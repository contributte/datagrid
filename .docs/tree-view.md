Table of contents

- [Tree view](#tree-view)
	- [Api](#api)
	- [Example table structure](#example-table-structure)
	- [Redrawing one row \(ajax\)](#redrawing-one-row-ajax)

# Tree view

## Api

There is a possibility to render the data as a tree:

```php
/**
 * First parameter is a callback that will return particular children rows for given parent
 * Second is a column name (or callback), that indicates whether the row has some children or not
 */
$grid->setTreeView(callable $getChildrenCallback, string|callable $has_children_column);
```

## Example table structure

Click the link "Show me the code" above. This will show you the code of this datagrid example. And here is a database table structure:

| Id | parent_category_id | name       | status |
| -- | ------------------ | ---------- | ------ |
| 1  | null               | 6r0xliyalt | 0      |
| 2  | null               | ozl4gy9udu | 0      |
| 3  | null               | 9i20xsrese | 1      |
| 4  | null               | yk95ak88ra | 2      |
| 5  | null               | 2loxs05egs | 0      |
| 6  | 1                  | wzrdlu2o0v | 1      |
| 7  | 1                  | 401nd5xv80 | 1      |
| 8  | 1                  | qeba0t2ovv | 0      |
| 9  | 1                  | el25id2emd | 0      |
| 10 | 1                  | vmkppicf1z | 1      |
| 11 | 2                  | yj6icacjk0 | 0      |
| 12 | 3                  | rjqpytdq63 | 0      |
| 13 | 3                  | wkossm2fud | 0      |
| 14 | 3                  | 4gbik9rxbp | 0      |
| 15 | 2                  | jl9ke3q9s7 | 2      |


## Redrawing one row (ajax)

Different situation occurs when you need to redraw just one row. Datagrid does not know about all the items (it has originally only top level rows). So before you call `Datagrid::redrawItem()`, you have to set datagrid datasource where the item will be visible:

```php
public function handleSetCategoryStatus($id, $status): void
{
	$this->categoryRepository->changeStatus($id, $status);

	$this->flashMessage("Status of category [$id] was updated to [$status].", 'success');

	$join = $this->db->select('COUNT(id) AS count, parent_category_id')
		->from('category')
		->groupBy('parent_category_id');

	$fluent = $this->db
		->select('c.*, c_b.count as has_children')
		->from('category', 'c')
		->leftJoin($join, 'c_b')
			->on('c_b.parent_category_id = c.id');

	if ($this->isAjax()) {
		$this->redrawControl('flashes');

		$this['categoriesGrid']->setDataSource($fluent);
		$this['categoriesGrid']->redrawItem($id, 'c.id');
	} else {
		$this->redirect('this');
	}
}
```
