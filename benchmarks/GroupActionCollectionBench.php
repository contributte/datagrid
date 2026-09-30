<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Benchmarks;

use Contributte\Datagrid\GroupAction\GroupButtonAction;
use Contributte\Datagrid\GroupAction\GroupSelectAction;
use Contributte\Datagrid\GroupAction\GroupTextAction;
use Contributte\Datagrid\GroupAction\GroupTextareaAction;
use PhpBench\Attributes as Bench;

/**
 * Benchmarks for GroupActionCollection optimizations:
 * - ID generation: count() vs auto-increment counter
 * - Loop merging: 3 separate loops vs 1 merged loop
 */
class GroupActionCollectionBench
{

	/**
	 * Simulate the original ID generation using count()
	 *
	 * @param array{action_count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideActionCounts')]
	public function benchIdGenerationWithCount(array $params): void
	{
		$groupActions = [];

		for ($i = 0; $i < $params['action_count']; $i++) {
			$id = count($groupActions) > 0 ? count($groupActions) + 1 : 1;
			$groupActions[$id] = new GroupTextAction('Action ' . $i);
		}
	}

	/**
	 * Optimized ID generation using auto-increment counter
	 *
	 * @param array{action_count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideActionCounts')]
	public function benchIdGenerationWithCounter(array $params): void
	{
		$groupActions = [];
		$nextId = 1;

		for ($i = 0; $i < $params['action_count']; $i++) {
			$id = $nextId++;
			$groupActions[$id] = new GroupTextAction('Action ' . $i);
		}
	}

	/**
	 * Simulate the original 3-loop pattern for processing group actions
	 *
	 * @param array{action_count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideActionCounts')]
	public function benchThreeLoopProcessing(array $params): void
	{
		$actions = $this->createMixedActions($params['action_count']);
		$buttonResults = [];
		$mainOptions = [];
		$subActionResults = [];

		// Loop 1: button actions
		foreach ($actions as $id => $action) {
			if ($action instanceof GroupButtonAction) {
				$buttonResults[$id] = $action->getTitle();
			}
		}

		// Loop 2: main options
		foreach ($actions as $id => $action) {
			if (! $action instanceof GroupButtonAction) {
				$mainOptions[$id] = $action->getTitle();
			}
		}

		// Loop 3: sub-action controls
		foreach ($actions as $id => $action) {
			if ($action instanceof GroupSelectAction) {
				$subActionResults[$id] = $action->getOptions();
			} elseif ($action instanceof GroupTextAction) {
				$subActionResults[$id] = 'text';
			} elseif ($action instanceof GroupTextareaAction) {
				$subActionResults[$id] = 'textarea';
			}
		}
	}

	/**
	 * Optimized single-loop pattern
	 *
	 * @param array{action_count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideActionCounts')]
	public function benchSingleLoopProcessing(array $params): void
	{
		$actions = $this->createMixedActions($params['action_count']);
		$buttonResults = [];
		$mainOptions = [];
		$subActionResults = [];

		foreach ($actions as $id => $action) {
			if ($action instanceof GroupButtonAction) {
				$buttonResults[$id] = $action->getTitle();

				continue;
			}

			$mainOptions[$id] = $action->getTitle();

			if ($action instanceof GroupSelectAction) {
				$subActionResults[$id] = $action->getOptions();
			} elseif ($action instanceof GroupTextAction) {
				$subActionResults[$id] = 'text';
			} elseif ($action instanceof GroupTextareaAction) {
				$subActionResults[$id] = 'textarea';
			}
		}
	}

	/**
	 * @return array<string, array{action_count: int}>
	 */
	public function provideActionCounts(): array
	{
		return [
			'5 actions' => ['action_count' => 5],
			'20 actions' => ['action_count' => 20],
			'50 actions' => ['action_count' => 50],
		];
	}

	/**
	 * @return array<int, GroupButtonAction|GroupSelectAction|GroupTextAction|GroupTextareaAction>
	 */
	private function createMixedActions(int $count): array
	{
		$actions = [];

		for ($i = 1; $i <= $count; $i++) {
			$actions[$i] = match ($i % 4) {
				0 => new GroupButtonAction('Button ' . $i),
				1 => new GroupSelectAction('Select ' . $i, ['a' => 'A', 'b' => 'B']),
				2 => new GroupTextAction('Text ' . $i),
				3 => new GroupTextareaAction('Textarea ' . $i),
			};
		}

		return $actions;
	}

}
