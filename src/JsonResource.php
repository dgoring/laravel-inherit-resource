<?php
namespace Dgoring\Laravel\InheritResource;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

trait JsonResource
{
  use GuessResource;
  use AuthorizesRequests, ValidatesRequests;

  protected $per = 15;

  public function index()
  {
    if($this->authorize)
    {
      $this->authorize('viewAny', $this->getClassName());
    }

    $query = $this->collection();
    $count = $query->count();

    if($skip = request()->query('skip'))
    {
      $query->skip($skip);
    }

    if($take = request()->query('take'))
    {
      $query->take($take);
    }
    else
    {
      $query->take($this->per);
    }

    return response()->json($query->get())->withHeaders(['Count' => $count]);
  }

  public function show()
  {
    if($this->authorize)
    {
      $this->authorize('view', $this->resource());
    }

    return response()->json($this->resource());
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
      return response()->json($this->resource());
    }

    return response()->json(['error' => 'Error encountered creating ' . class_basename($this->getClassName())])->status(500);
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
      return response()->json($this->resource());
    }

    return response()->json(['error' => 'Error encountered updating ' . class_basename($this->getClassName())], 500);
  }

  public function destroy()
  {
    if($this->authorize)
    {
      $this->authorize('delete', $this->resource());
    }

    if($this->resource()->delete())
    {
      return response()->json([]);
    }

    return response()->json(['error' => 'Error encountered deleting ' . class_basename($this->getClassName())], 500);
  }
}
