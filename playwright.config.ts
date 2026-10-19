import {defineConfig, devices} from '@playwright/test';

export default defineConfig({
	testDir: './tests/browser',
	globalSetup: './tests/browser/setup.mjs',
	outputDir: './tests/temp/browser/results',
	reporter: 'list',
	workers: 1,
	use: {browserName: 'chromium', headless: true},
	projects: [
		{name: 'desktop', use: {viewport: {width: 1280, height: 900}}},
		{name: 'mobile', use: {...devices['Pixel 7'], defaultBrowserType: 'chromium'}},
	],
});
