<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Benchmarks;

use DateTime;
use PhpBench\Attributes as Bench;

/**
 * Benchmarks for DateTimeHelper::fromString() optimization:
 * - Original: array_merge() recreates default formats array on every call
 * - Optimized: class constant for default formats, conditional merge
 */
class DateTimeHelperBench
{

	private const DEFAULT_FORMATS = [
		'Y-m-d H:i:s.u',
		'Y-m-d H:i:s',
		'Y-m-d',
		'j. n. Y G:i:s',
		'j. n. Y G:i',
		'j. n. Y',
		'U',
	];

	/**
	 * Original: array_merge on every call
	 *
	 * @param array{value: string, custom_formats: array<string>} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideDateValues')]
	public function benchFromStringOriginal(array $params): void
	{
		$this->fromStringOriginal($params['value'], $params['custom_formats']);
	}

	/**
	 * Optimized: constant default formats with conditional merge
	 *
	 * @param array{value: string, custom_formats: array<string>} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideDateValues')]
	public function benchFromStringOptimized(array $params): void
	{
		$this->fromStringOptimized($params['value'], $params['custom_formats']);
	}

	/**
	 * Benchmark DateTime instance passthrough (both should be same)
	 */
	#[Bench\Revs(5000)]
	#[Bench\Iterations(10)]
	public function benchDateTimePassthrough(): void
	{
		$dt = new DateTime();
		$this->fromStringOriginal($dt, []);
	}

	/**
	 * @return array<string, array{value: string, custom_formats: array<string>}>
	 */
	public function provideDateValues(): array
	{
		return [
			'Y-m-d H:i:s (no custom)' => [
				'value' => '2024-06-15 14:30:00',
				'custom_formats' => [],
			],
			'Y-m-d (no custom)' => [
				'value' => '2024-06-15',
				'custom_formats' => [],
			],
			'Czech format (no custom)' => [
				'value' => '15. 6. 2024',
				'custom_formats' => [],
			],
			'Y-m-d H:i:s (with custom)' => [
				'value' => '2024-06-15 14:30:00',
				'custom_formats' => ['d/m/Y', 'm-d-Y'],
			],
			'last format U (no custom)' => [
				'value' => '1718454600',
				'custom_formats' => [],
			],
		];
	}

	/**
	 * Original implementation: array_merge on every call
	 */
	private function fromStringOriginal(mixed $value, array $formats = []): DateTime
	{
		$formats = array_merge($formats, [
			'Y-m-d H:i:s.u',
			'Y-m-d H:i:s',
			'Y-m-d',
			'j. n. Y G:i:s',
			'j. n. Y G:i',
			'j. n. Y',
			'U',
		]);

		if ($value instanceof DateTime) {
			return $value;
		}

		foreach ($formats as $format) {
			$date = DateTime::createFromFormat($format, (string) $value);

			if ($date === false) {
				continue;
			}

			return $date;
		}

		return new DateTime();
	}

	/**
	 * Optimized implementation: constant default formats
	 */
	private function fromStringOptimized(mixed $value, array $formats = []): DateTime
	{
		$allFormats = $formats !== [] ? array_merge($formats, self::DEFAULT_FORMATS) : self::DEFAULT_FORMATS;

		if ($value instanceof DateTime) {
			return $value;
		}

		foreach ($allFormats as $format) {
			$date = DateTime::createFromFormat($format, (string) $value);

			if ($date === false) {
				continue;
			}

			return $date;
		}

		return new DateTime();
	}

}
