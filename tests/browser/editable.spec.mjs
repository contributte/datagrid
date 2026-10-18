import {test, expect} from '@playwright/test';
import {fileURLToPath} from 'node:url';

const bundlePath = fileURLToPath(new URL('../temp/browser/editable.js', import.meta.url));

async function mount(page, options = {}) {
	const requests = [];
	const pending = [];
	await page.route('http://datagrid.test/', route => route.fulfill({
		contentType: 'text/html',
		body: '<style>td{line-height:24px;padding:8px;min-width:180px}textarea,input,select{width:100%;box-sizing:border-box}</style><div id="grid"><table><tr><td>Original</td></tr></table></div><button id="outside">Outside</button>',
	}));
	await page.route('http://datagrid.test/edit', async route => {
		requests.push(route.request().postData());
		if (options.pending) await new Promise(resolve => pending.push(resolve));
		try {
			await route.fulfill({status: options.fail ? 500 : 200, contentType: 'application/json', body: JSON.stringify(options.response ?? {})});
		} catch { /* The browser may already have aborted the request. */ }
	});
	await page.goto('http://datagrid.test/');
	await page.locator('td').evaluate((cell, options) => {
		cell.innerHTML = options.html ?? 'Original';
		cell.setAttribute('data-datagrid-editable-url', '/edit');
		cell.setAttribute('data-datagrid-editable-type', options.type ?? 'textarea');
		cell.setAttribute('data-datagrid-editable-value', options.value ?? 'Original');
		if (options.select) cell.setAttribute('data-datagrid-editable-element', options.select);
	}, options);
	await page.addScriptTag({path: bundlePath});
	return {cell: page.locator('td').first(), requests, release: () => pending.splice(0).forEach(resolve => resolve())};
}

test('Enter followed by blur sends one POST; an empty successful response is valid', async ({page}) => {
	const fixture = await mount(page, {pending: true});
	try {
		await fixture.cell.click();
		const editor = fixture.cell.locator('textarea');
		await editor.fill('Changed');
		await editor.press('Enter');
		await expect.poll(() => fixture.requests.length).toBe(1);
		await expect(editor).toHaveJSProperty('readOnly', true);
		await expect(fixture.cell).toHaveAttribute('aria-busy', 'true');
		await editor.press('Enter');
		await page.locator('#outside').click();
		fixture.release();
		await expect(fixture.cell).toHaveClass(/edited/);
		await expect(fixture.cell).not.toHaveClass(/edited-error/);
		await expect(fixture.cell).toHaveText('Changed');
		await expect(fixture.cell).not.toHaveAttribute('aria-busy');
		expect(fixture.requests).toEqual(['value=Changed']);
	} finally { fixture.release(); }
});

test('Escape restores exact markup and does not submit on removal blur', async ({page}) => {
	const html = '\n\t<strong>Original</strong>\n';
	const fixture = await mount(page, {html});
	await fixture.cell.click();
	await fixture.cell.locator('textarea').fill('Changed');
	await fixture.cell.locator('textarea').press('Escape');
	expect(await fixture.cell.innerHTML()).toBe(html);
	await expect(fixture.cell).not.toHaveClass(/editing/);
	expect(fixture.requests).toEqual([]);
});

test('Shift+Enter inserts a newline; Enter submits it once', async ({page}) => {
	const fixture = await mount(page);
	await fixture.cell.click();
	const editor = fixture.cell.locator('textarea');
	await editor.fill('First');
	await editor.press('End');
	await editor.press('Shift+Enter');
	await page.keyboard.insertText('Second');
	await expect(editor).toHaveValue('First\nSecond');
	expect(fixture.requests).toEqual([]);
	await editor.press('Enter');
	await expect(fixture.cell).toHaveClass(/edited/);
	expect(fixture.requests).toEqual(['value=First%0ASecond']);
});

test('Escape while focused on a pending editor does not close or resubmit it', async ({page}) => {
	const fixture = await mount(page, {pending: true});
	try {
		await fixture.cell.click();
		const editor = fixture.cell.locator('textarea');
		await editor.fill('Changed');
		await editor.press('Enter');
		await expect.poll(() => fixture.requests.length).toBe(1);
		await expect(editor).toBeFocused();
		await page.keyboard.press('Escape');
		await expect(fixture.cell).toHaveClass(/editing/);
		fixture.release();
		await expect(fixture.cell).toHaveClass(/edited/);
		await expect(fixture.cell).not.toHaveClass(/edited-error/);
		expect(fixture.requests).toHaveLength(1);
	} finally { fixture.release(); }
});

test('global Escape outside the editor does not abort a pending save', async ({page}) => {
	const fixture = await mount(page, {pending: true});
	try {
		await fixture.cell.click();
		await fixture.cell.locator('textarea').fill('Changed');
		await fixture.cell.locator('textarea').press('Enter');
		await expect.poll(() => fixture.requests.length).toBe(1);
		await page.locator('#outside').click();
		await page.keyboard.press('Escape');
		fixture.release();
		await expect(fixture.cell).toHaveClass(/(?:^| )edited(?: |$)/);
		await expect(fixture.cell).toHaveText('Changed');
		await expect(fixture.cell).not.toHaveClass(/edited-error/);
		await expect(fixture.cell).not.toHaveAttribute('aria-busy');
		expect(fixture.requests).toHaveLength(1);
	} finally { fixture.release(); }
});

test('two cells save concurrently without aborting each other', async ({page}) => {
	const fixture = await mount(page, {pending: true});
	await fixture.cell.evaluate(cell => {
		const second = cell.cloneNode(true);
		second.textContent = 'Second';
		second.setAttribute('data-datagrid-editable-value', 'Second');
		cell.parentElement.append(second);
		window.ajax.dispatchEvent(new CustomEvent('success'));
	});
	const secondCell = page.locator('td').nth(1);
	try {
		await fixture.cell.click();
		await fixture.cell.locator('textarea').fill('A2');
		await fixture.cell.locator('textarea').press('Enter');
		await expect.poll(() => fixture.requests.length).toBe(1);
		await secondCell.click();
		await secondCell.locator('textarea').fill('B2');
		await secondCell.locator('textarea').press('Enter');
		await expect.poll(() => fixture.requests.length).toBe(2);
		fixture.release();
		for (const [cell, value] of [[fixture.cell, 'A2'], [secondCell, 'B2']]) {
			await expect(cell).toHaveClass(/(?:^| )edited(?: |$)/);
			await expect(cell).toHaveText(value);
			await expect(cell).not.toHaveClass(/edited-error/);
			await expect(cell).not.toHaveAttribute('aria-busy');
		}
		expect(fixture.requests).toEqual(['value=A2', 'value=B2']);
	} finally { fixture.release(); }
});

test('an unrelated Naja request does not abort a pending cell save', async ({page}) => {
	const fixture = await mount(page, {pending: true});
	await page.route('http://datagrid.test/filter', route => route.fulfill({contentType: 'application/json', body: '{}'}));
	try {
		await fixture.cell.click();
		await fixture.cell.locator('textarea').fill('Changed');
		await fixture.cell.locator('textarea').press('Enter');
		await expect.poll(() => fixture.requests.length).toBe(1);
		await page.evaluate(() => window.naja.makeRequest('GET', '/filter', null, {history: false}));
		fixture.release();
		await expect(fixture.cell).toHaveClass(/(?:^| )edited(?: |$)/);
		await expect(fixture.cell).toHaveText('Changed');
		await expect(fixture.cell).not.toHaveClass(/edited-error/);
		expect(fixture.requests).toHaveLength(1);
	} finally { fixture.release(); }
});

test('the Naja adapter still rejects an explicitly abortable request when opted in', async ({page}) => {
	const fixture = await mount(page, {pending: true});
	try {
		await page.evaluate(() => {
			window.requestResult = window.ajax.request({url: '/edit', method: 'POST', data: {value: 'Changed'}, rejectOnCancel: true})
				.then(() => 'success', error => error.name);
		});
		await expect.poll(() => fixture.requests.length).toBe(1);
		await page.keyboard.press('Escape');
		expect(await page.evaluate(() => window.requestResult)).toBe('AbortError');
	} finally { fixture.release(); }
});

test('a before-event cancellation is not reported as a saved value', async ({page}) => {
	const fixture = await mount(page);
	await page.evaluate(() => window.naja.addEventListener('before', event => event.preventDefault()));
	await fixture.cell.click();
	await fixture.cell.locator('textarea').fill('Changed');
	await fixture.cell.locator('textarea').press('Enter');
	await expect(fixture.cell).toHaveClass(/edited-error/);
	await expect(fixture.cell).toHaveText('Original');
	expect(fixture.requests).toEqual([]);
});

test('an HTTP error restores the exact display', async ({page}) => {
	const fixture = await mount(page, {html: '<strong>Original</strong>', fail: true});
	await fixture.cell.click();
	await fixture.cell.locator('textarea').fill('Changed');
	await fixture.cell.locator('textarea').press('Enter');
	await expect(fixture.cell).toHaveClass(/edited-error/);
	expect(await fixture.cell.innerHTML()).toBe('<strong>Original</strong>');
});

test('select change and removal blur submit once', async ({page}) => {
	const fixture = await mount(page, {type: 'select', value: '1', select: '<select><option value="1">First</option><option value="2">Second</option></select>'});
	await fixture.cell.click();
	await fixture.cell.locator('select').selectOption('2');
	await expect(fixture.cell).toHaveText('Second');
	expect(fixture.requests).toEqual(['value=2']);
});

test('repeated AJAX initialization binds old and new cells once', async ({page}) => {
	const fixture = await mount(page);
	const counts = await page.evaluate(() => {
		const oldCell = document.querySelector('td');
		const newCell = oldCell.cloneNode(true);
		oldCell.parentElement.append(newCell);
		const counts = {old: 0, added: 0};
		for (const [cell, key] of [[oldCell, 'old'], [newCell, 'added']]) {
			const add = cell.addEventListener;
			cell.addEventListener = function (type, ...args) {
				if (type === 'click') counts[key]++;
				return add.call(this, type, ...args);
			};
		}
		for (let i = 0; i < 3; i++) window.ajax.dispatchEvent(new CustomEvent('success'));
		return counts;
	});
	expect(counts).toEqual({old: 0, added: 1});
	await page.locator('td').last().click();
	await page.locator('td').last().locator('textarea').fill('Changed');
	await page.locator('td').last().locator('textarea').press('Enter');
	await expect(page.locator('td').last()).toHaveText('Changed');
	expect(fixture.requests).toHaveLength(1);
});

test('a pending response does not overwrite a newly redrawn cell', async ({page}) => {
	const fixture = await mount(page, {pending: true});
	try {
		await fixture.cell.click();
		await fixture.cell.locator('textarea').fill('Changed');
		await fixture.cell.locator('textarea').press('Enter');
		await expect.poll(() => fixture.requests.length).toBe(1);
		await fixture.cell.evaluate(cell => {
			const replacement = document.createElement('td');
			replacement.textContent = 'Server redraw';
			cell.replaceWith(replacement);
		});
		fixture.release();
		await expect(fixture.cell).toHaveText('Server redraw');
		await expect(fixture.cell).not.toHaveClass(/edited/);
	} finally { fixture.release(); }
});

for (const replacement of [false, 0, '<strong>Rendered</strong>']) {
	test(`retains existing server-renderer behavior for ${JSON.stringify(replacement)}`, async ({page}) => {
		const fixture = await mount(page, {response: {_datagrid_editable_new_value: replacement}});
		await fixture.cell.click();
		await fixture.cell.locator('textarea').fill('Changed');
		await fixture.cell.locator('textarea').press('Enter');
		await expect(fixture.cell).toHaveClass(/edited/);
		expect(await fixture.cell.innerHTML()).toBe(typeof replacement === 'string' ? replacement : 'Changed');
	});
}
