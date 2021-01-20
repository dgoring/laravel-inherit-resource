<?php
namespace Dgoring\Laravel\InheritResource;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

trait HtmlResource
{
  use GuessResource, GuessView;

  use AuthorizesRequests, ValidatesRequests;

  protected $per = 15;

  protected $distinctFix = true;

  public function index()
  {
    if($this->authorize)
    {
      $this->authorize('viewAny', $this->getClassName());
    }

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

    $results = ($total = $base->getCountForPagination($columns))
                                ? $query->forPage($page, $this->per)->get(['*'])
                                : new Collection([]);

    $paginator = new LengthAwarePaginator($results, $total, $this->per, $page, [
      'path' => Paginator::resolveCurrentPath(),
      'pageName' => $pageName,
    ]);

    return view($this->getViewNS() . $this->views['index'], [
      $this->getCollectionName() => $paginator->appends(request()->query())
    ]);
  }

  public function show()
  {
    if($this->authorize)
    {
      $this->authorize('view', $this->resource());
    }

    return view($this->getViewNS() . $this->views['show'], [
      $this->getInstanceName() => $this->resource()
    ]);
  }

  public function create()
  {
    if($this->authorize)
    {
      $this->authorize('create', $this->resource());
    }

    return view($this->getViewNS() . $this->views['create'], [
      $this->getInstanceName() => $this->resource()
    ]);
  }

  public function store()
  {
    if($this->authorize)
    {
      $this->authorize('create', $this->resource());
    }

    if(method_exists($this, 'validationRules'))
    {
      $this->validateWith($this->validationRules());
    }

    $this->resource()->fill(request()->all());

    if($this->resource()->save())
    {
      return redirect()->route(
          $this->getResourceRoute() . '.show',
          array_merge(request()->route()->parameters, [$this->resource()->id])
        )
        ->with('alerts.success', class_basename($this->getClassName()) . ' Successfully created');
    }

    if(request()->wantsJson())
    {
      return response()->json(['error' => 'Error encountered creating ' . class_basename($this->getClassName())])->status(500);
    }

    return redirect()->back()
      ->with('alerts.danger', 'Error encountered creating ' . class_basename($this->getClassName()))
      ->withInputs(request()->input());
  }

  public function edit()
  {
    if($this->authorize)
    {
      $this->authorize('update', $this->resource());
    }

    return view($this->getViewNS() . $this->views['edit'], [
      $this->getInstanceName() => $this->resource()
    ]);
  }

  public function update()
  {
    if($this->authorize)
    {
      $this->authorize('update', $this->resource());
    }

    if(method_exists($this, 'validationRules'))
    {
      $this->validateWith($this->validationRules());
    }

    $this->resource()->fill(request()->all());

    if($this->resource()->save())
    {
      return redirect()->route(
          $this->getResourceRoute() . '.show',
          array_merge(request()->route()->parameters)
        )
        ->with('alerts.success', class_basename($this->getClassName()) . ' Successfully updated');
    }

    return redirect()->back()
      ->with('alerts.danger', 'Error encountered updating ' . class_basename($this->getClassName()))
      ->withInputs(request()->input());
  }

  public function destroy()
  {
    if($this->authorize)
    {
      $this->authorize('delete', $this->resource());
    }

    if($this->resource()->delete())
    {
      $parameters = request()->route()->parameters;
      array_pop($parameters);

      return redirect()->route($this->getResourceRoute() . '.index', $parameters)
        ->with('alerts.success', class_basename($this->getClassName()) . ' Successfully deleted');
    }

    return redirect()->back()
      ->with('alerts.danger', 'Error encountered deleting ' . class_basename($this->getClassName()))
      ->withInputs(request()->input());
  }
}
