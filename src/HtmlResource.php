<?php
namespace Dgoring\Laravel\InheritResource;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

trait HtmlResource
{
  use GuessResource, ViewResponses;

  use AuthorizesRequests, ValidatesRequests;

  protected $per = 15;

  protected $fillOnlyValidated = true;

  protected $distinctFix = true;

  public function index()
  {
    if($this->authorize)
    {
      $this->authorize('viewAny', $this->getClassName());
    }

    return $this->htmlIndex();
  }

  public function show()
  {
    if($this->authorize)
    {
      $this->authorize('view', $this->resource());
    }

    return $this->htmlShow();
  }

  public function create()
  {
    if($this->authorize)
    {
      $this->authorize('create', $this->resource());
    }

    return $this->htmlCreate();
  }

  public function store()
  {
    if($this->authorize)
    {
      $this->authorize('create', $this->resource());
    }

    $attributes = request()->all();

    if(method_exists($this, 'validationRules'))
    {
      $validated = $this->validateWith($this->validationRules());

      if($this->fillOnlyValidated)
      {
        $attributes = $validated;
      }
    }

    $this->resource()->fill($attributes);

    if($this->resource()->save())
    {
      return $this->htmlStoreSuccess($attributes);
    }

    return $this->htmlStoreFailure($attributes);
  }

  public function edit()
  {
    if($this->authorize)
    {
      $this->authorize('update', $this->resource());
    }

    return $this->htmlEdit();
  }

  public function update()
  {
    if($this->authorize)
    {
      $this->authorize('update', $this->resource());
    }

    $attributes = request()->all();

    if(method_exists($this, 'validationRules'))
    {
      $validated = $this->validateWith($this->validationRules());

      if($this->fillOnlyValidated)
      {
        $attributes = $validated;
      }
    }

    $this->resource()->fill($attributes);

    if($this->resource()->save())
    {
      return $this->htmlUpdateSuccess($attributes);
    }

    return $this->htmlUpdateFailure($attributes);
  }

  public function destroy()
  {
    if($this->authorize)
    {
      $this->authorize('delete', $this->resource());
    }

    if($this->resource()->delete())
    {

      return $this->htmlDestroySuccess();
    }

    return $this->htmlDestroyFailure();
  }
}
