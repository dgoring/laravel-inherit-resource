<?php
namespace Dgoring\Laravel\InheritResource;

use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

trait ViewResponses
{
  use GuessView;

  protected function htmlIndex()
  {
    $query = $this->collection();

    $columns = ['*'];

    if($this->distinctFix && $query instanceof Builder && $query->toBase()->distinct && ($model = $query->getModel()))
    {
      $columns = [$model->getTable() . '.' . $model->getKeyName()];
    }

    $base = $query;

    if($base instanceof Builder)
    {
      $base = $base->toBase();
    }
    else
    if($base instanceof Relation)
    {
      $base = $base->getBaseQuery();
    }

    $pageName = 'page';
    $page = Paginator::resolveCurrentPage($pageName);

    $results = null;

    if($total = $base->getCountForPagination($columns))
    {
      $results = $this->per > 0 ? $query->forPage($page, $this->per)->get(['*']) : $query->get(['*']);
    }
    else
    {
      $results = new Collection([]);
    }

    $paginator = new LengthAwarePaginator($results, $total, $this->per, $page, [
      'path' => Paginator::resolveCurrentPath(),
      'pageName' => $pageName,
    ]);

    return view($this->getViewNS() . $this->views['index'], [
      $this->getCollectionName() => $paginator->appends(request()->query())
    ]);
  }

  protected function htmlShow()
  {
    return view($this->getViewNS() . $this->views['show'], [
      $this->getInstanceName() => $this->resource()
    ]);
  }

  protected function htmlCreate()
  {
    return view($this->getViewNS() . $this->views['create'], [
      $this->getInstanceName() => $this->resource()
    ]);
  }

  protected function htmlStoreSuccess()
  {
    return redirect()->route(
          $this->getResourceRoute() . '.show',
          array_merge(request()->route()->parameters, [$this->resource()->getKey()])
        )
        ->with('alerts.success', class_basename($this->getClassName()) . ' Successfully created');

  }

  protected function htmlStoreFailure()
  {
    return redirect()->back()
      ->with('alerts.danger', 'Error encountered creating ' . class_basename($this->getClassName()))
      ->withInputs(request()->input());
  }

  protected function htmlEdit()
  {
    return view($this->getViewNS() . $this->views['edit'], [
      $this->getInstanceName() => $this->resource()
    ]);
  }

  protected function htmlUpdateSuccess()
  {
    return redirect()->route(
          $this->getResourceRoute() . '.show',
          array_merge(request()->route()->parameters)
        )
        ->with('alerts.success', class_basename($this->getClassName()) . ' Successfully updated');

  }

  protected function htmlUpdateFailure()
  {
    return redirect()->back()
      ->with('alerts.danger', 'Error encountered updating' . class_basename($this->getClassName()))
      ->withInputs(request()->input());
  }

  protected function htmlDestroySuccess()
  {
    $parameters = request()->route()->parameters;
    array_pop($parameters);

    return redirect()->route($this->getResourceRoute() . '.index', $parameters)
          ->with('alerts.success', class_basename($this->getClassName()) . ' Successfully deleted');

  }

  protected function htmlDestroyFailure()
  {
    return redirect()->back()
      ->with('alerts.danger', 'Error encountered deleting' . class_basename($this->getClassName()))
      ->withInputs(request()->input());
  }
}

