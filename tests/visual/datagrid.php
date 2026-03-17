<?php declare(strict_types = 1);

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;

function visualCreateDatagrid(): Datagrid
{
	$factory = new TestingDatagridFactory();
	$grid = $factory->createTestingDatagrid();

	$grid->setRememberState(false);
	$grid->setRefreshUrl(false);
	$grid->setPagination(false);
	$grid->setDataSource(visualBuildDeterministicData());
	$grid->setDefaultSort(['name' => 'ASC']);

	$grid->addColumnText('id', 'ID')
		->setSortable();
	$grid->addColumnText('name', 'Name')
		->setSortable();
	$grid->addColumnText('status', 'Status');
	$grid->addColumnText('role', 'Role');

	$grid->addFilterText('name', 'Name');
	$grid->addFilterSelect('status', 'Status', [
		'active' => 'Active',
		'paused' => 'Paused',
		'archived' => 'Archived',
	]);

	return $grid;
}

function visualRenderGridHtml(Datagrid $grid): string
{
	ob_start();
	$grid->render();

	$output = ob_get_clean();

	if (!is_string($output) || $output === '') {
		throw new RuntimeException('Rendered grid output is empty.');
	}

	return $output;
}

/**
 * @return array<int, array{id: int, name: string, status: string, role: string}>
 */
function visualBuildDeterministicData(): array
{
	return [
		['id' => 1001, 'name' => 'Alice Johnson', 'status' => 'active', 'role' => 'Admin'],
		['id' => 1002, 'name' => 'Bob Smith', 'status' => 'active', 'role' => 'Editor'],
		['id' => 1003, 'name' => 'Carol White', 'status' => 'paused', 'role' => 'Editor'],
		['id' => 1004, 'name' => 'Daniel Green', 'status' => 'archived', 'role' => 'Viewer'],
		['id' => 1005, 'name' => 'Eva Brown', 'status' => 'active', 'role' => 'Viewer'],
		['id' => 1006, 'name' => 'Frank Lee', 'status' => 'paused', 'role' => 'Admin'],
		['id' => 1007, 'name' => 'Gina Hall', 'status' => 'active', 'role' => 'Editor'],
		['id' => 1008, 'name' => 'Henry King', 'status' => 'archived', 'role' => 'Viewer'],
	];
}

function visualBuildBaselineCss(): string
{
	return <<<'CSS'
* {
	box-sizing: border-box;
}

body {
	margin: 0;
	padding: 24px;
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
	background: #f7f7f8;
	color: #1f2328;
}

.preview-shell {
	max-width: 1180px;
	margin: 0 auto;
	background: #ffffff;
	border: 1px solid #d0d7de;
	border-radius: 8px;
	padding: 18px;
}

.preview-title {
	margin: 0 0 12px;
	font-size: 18px;
	font-weight: 600;
}

table {
	width: 100%;
	border-collapse: collapse;
}

th,
td {
	border: 1px solid #d0d7de;
	padding: 8px;
	font-size: 13px;
}

thead th {
	background: #f6f8fa;
}

a {
	color: #0969da;
	text-decoration: none;
}

.form-control,
.form-select {
	width: 100%;
	height: 30px;
	padding: 4px 8px;
	font-size: 13px;
	border: 1px solid #d0d7de;
	border-radius: 4px;
	background: #fff;
}

.btn {
	display: inline-block;
	padding: 4px 10px;
	font-size: 12px;
	border: 1px solid #d0d7de;
	border-radius: 4px;
	background: #fff;
	color: #1f2328;
}

.btn-primary {
	background: #0969da;
	border-color: #0969da;
	color: #fff;
}
CSS;
}
