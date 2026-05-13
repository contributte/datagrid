import {test, expect} from '@playwright/test';
import {fileURLToPath} from 'node:url';

const bundlePath = fileURLToPath(new URL('../temp/browser/autosubmit.js', import.meta.url));
const DELAY = 300;

// Mirrors the real filter form: an ajax form with two filter inputs and a hidden but enabled
// submit button (like perPage_submit), which makes the browser submit the form natively on Enter.
const html = `
<div id="grid">
	<form class="ajax" action="/filter" method="post">
		<input type="text" name="filter[name]" data-autosubmit>
		<input type="text" name="filter[email]" data-autosubmit>
		<input type="text" name="filter[date]" data-autosubmit data-autosubmit-change>
		<input type="submit" name="perPage_submit" value="Go" style="visibility:hidden">
	</form>
</div>`;

async function mount(page) {
	const requests = [];
	await page.route('http://datagrid.test/', route => route.fulfill({contentType: 'text/html', body: html}));
	await page.route('http://datagrid.test/filter', async route => {
		// Naja posts FormData as multipart/form-data
		const body = route.request().postData() ?? '';
		requests.push(body.match(/name="filter\[name\]"\r\n\r\n(.*?)\r\n/)?.[1] ?? null);
		try {
			await route.fulfill({contentType: 'application/json', body: '{}'});
		} catch { /* The browser may already have aborted the request. */ }
	});
	await page.goto('http://datagrid.test/');
	await page.addScriptTag({path: bundlePath});
	const input = page.locator('input[name="filter[name]"]');
	await input.focus();
	return {input, requests};
}

const settle = page => page.waitForTimeout(DELAY * 2);

test('typing submits once after the debounce delay', async ({page}) => {
	const {requests} = await mount(page);
	await page.keyboard.type('abc');
	await settle(page);
	expect(requests).toEqual(['abc']);
});

test.describe(() => {
	test.use({permissions: ['clipboard-read', 'clipboard-write']});

	test('a modifier keyup after the last character keyup does not swallow the submit', async ({page}) => {
		const {requests} = await mount(page);
		const shortcut = async key => {
			// Ctrl released after the key: the last keyup is "Control"
			await page.keyboard.down('Control');
			await page.keyboard.press(key);
			await page.keyboard.up('Control');
		};
		await page.keyboard.type('abc');
		await settle(page);

		await shortcut('a');
		await shortcut('x');
		await settle(page);
		expect(requests).toEqual(['abc', '']);

		await shortcut('v');
		await settle(page);
		expect(requests).toEqual(['abc', '', 'abc']);
	});
});

test('navigation keyups after a character keyup do not swallow the submit', async ({page}) => {
	const {requests} = await mount(page);
	await page.keyboard.type('ab');
	await page.keyboard.press('ArrowLeft');
	await page.keyboard.press('Shift');
	await settle(page);
	expect(requests).toEqual(['ab']);
});

test('modifier and navigation keys alone do not submit', async ({page}) => {
	const {requests} = await mount(page);
	for (const key of ['Shift', 'Control', 'ArrowLeft', 'Home', 'F2']) {
		await page.keyboard.press(key);
	}
	await settle(page);
	expect(requests).toEqual([]);
});

test('Enter right after typing submits exactly once, without waiting', async ({page}) => {
	const {requests} = await mount(page);
	await page.keyboard.type('abc');
	await page.keyboard.press('Enter');
	await expect.poll(() => requests.length, {timeout: DELAY / 2}).toBe(1);
	await settle(page);
	expect(requests).toEqual(['abc']);
});

test('Enter on an unchanged value still submits exactly once', async ({page}) => {
	const {requests} = await mount(page);
	await page.keyboard.press('Enter');
	await settle(page);
	expect(requests).toEqual(['']);
});

test('Enter after typing into several inputs of the form submits exactly once', async ({page}) => {
	const {requests} = await mount(page);
	await page.keyboard.type('a');
	await page.keyboard.press('Tab');
	await page.keyboard.type('b');
	await page.keyboard.press('Enter');
	await settle(page);
	expect(requests).toEqual(['a']);
});

test('typing and leaving an input with data-autosubmit-change submits once', async ({page}) => {
	const {requests} = await mount(page);
	await page.locator('input[name="filter[date]"]').focus();
	await page.keyboard.type('2026-11-11');
	await page.locator('input[name="filter[name]"]').focus();
	await settle(page);
	expect(requests).toEqual(['']);
});

test('virtual keyboard keyup ("Unidentified") triggers the submit', async ({page}) => {
	const {input, requests} = await mount(page);
	await input.evaluate(el => {
		el.value = 'mobile';
		el.dispatchEvent(new KeyboardEvent('keyup', {key: 'Unidentified', keyCode: 229, bubbles: true}));
	});
	await settle(page);
	expect(requests).toEqual(['mobile']);
});
