<?php declare(strict_types = 1);

use Latte\Engine;

$rootDir = dirname(__DIR__, 2);

require $rootDir . '/vendor/autoload.php';
require __DIR__ . '/datagrid.php';

[$outputPath, $showHelp] = visualParseArguments($argv);

if ($showHelp || $outputPath === null) {
	fwrite(STDERR, "Usage: php tests/visual/test.php --output <path>\n");
	exit($showHelp ? 0 : 1);
}

$outputDirectory = dirname($outputPath);

if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0777, true) && !is_dir($outputDirectory)) {
	fwrite(STDERR, sprintf("Could not create output directory: %s\n", $outputDirectory));
	exit(1);
}

$datagridCssPath = $rootDir . '/assets/css/datagrid.css';
$datagridCss = file_get_contents($datagridCssPath);

if ($datagridCss === false) {
	fwrite(STDERR, sprintf("Could not read stylesheet: %s\n", $datagridCssPath));
	exit(1);
}

if (!class_exists(Engine::class)) {
	fwrite(STDERR, "Latte is not installed. Run composer install/update to include latte/latte.\n");
	exit(1);
}

$grid = visualCreateDatagrid();
$gridHtml = visualRenderGridHtml($grid);

$latte = new Engine();
$templatePath = __DIR__ . '/datagrid.latte';

$html = $latte->renderToString($templatePath, [
	'baselineCss' => visualBuildBaselineCss(),
	'datagridCss' => $datagridCss,
	'gridHtml' => $gridHtml,
]);

if (file_put_contents($outputPath, $html) === false) {
	fwrite(STDERR, sprintf("Could not write output file: %s\n", $outputPath));
	exit(1);
}

echo sprintf("Rendered datagrid HTML to %s\n", $outputPath);

/**
 * @return array{0: string|null, 1: bool}
 */
function visualParseArguments(array $arguments): array
{
	$outputPath = null;
	$showHelp = false;

	for ($index = 1, $count = count($arguments); $index < $count; $index++) {
		$argument = $arguments[$index];

		if ($argument === '--help' || $argument === '-h') {
			$showHelp = true;
			continue;
		}

		if ($argument === '--output') {
			$index++;
			$outputPath = $arguments[$index] ?? null;
			continue;
		}

		if (str_starts_with($argument, '--output=')) {
			$outputPath = substr($argument, 9);
		}
	}

	return [$outputPath, $showHelp];
}
