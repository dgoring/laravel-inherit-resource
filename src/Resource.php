<?php
namespace Dgoring\Laravel\InheritResource;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

trait Resource
{
  use GuessResource, ViewResponses, JsonResponses;

  use AuthorizesRequests, ValidatesRequests;

  protected $per = 15;

  protected $fillOnlyValidated = true;

  protected $saveTransaction = true;

  protected $distinctFix = true;

  public function index()
  {
    if($this->authorize)
    {
      $this->authorize('viewAny', $this->getClassName());
    }

    if(request()->wantsJson())
    {
      return $this->jsonIndex();
    }

    return $this->htmlIndex();
  }

  public function show()
  {
    if($this->authorize)
    {
      $this->authorize('view', $this->resource());
    }

    if(request()->wantsJson())
    {
      return $this->jsonShow();
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

    $saved = false;

    if($this->saveTransaction)
    {
      $saved = DB::transaction(function () use($attributes) {
        $this->resource()->fill($attributes);

        return $this->resource()->save();
      });
    }
    else
    {
      $this->resource()->fill($attributes);

      $saved = $this->resource()->save();
    }

    if($saved)
    {
      if(request()->wantsJson())
      {
        return $this->jsonStoreSuccess($attributes);
      }

      return $this->htmlStoreSuccess($attributes);
    }

    if(request()->wantsJson())
    {
      return $this->jsonStoreFailure($attributes);
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
    $instance = $this->resource();

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

    $saved = false;

    if($this->saveTransaction)
    {
      $saved = DB::transaction(function () use($attributes) {
        $this->resource()->fill($attributes);

        return $this->resource()->save();
      });
    }
    else
    {
      $this->resource()->fill($attributes);

      $saved = $this->resource()->save();
    }

    if($saved)
    {
      if(request()->wantsJson())
      {
        return $this->jsonUpdateSuccess($attributes);
      }

      return $this->htmlUpdateSuccess($attributes);
    }

    if(request()->wantsJson())
    {
      return $this->jsonUpdateFailure($attributes);
    }


    return $this->htmlUpateFailure($attributes);
  }

  public function destroy()
  {
    if($this->authorize)
    {
      $this->authorize('delete', $this->resource());
    }

    $deleted = false;

    if($this->saveTransaction)
    {
      $deleted = DB::transaction(function () {
        return $this->resource()->delete();
      });
    }
    else
    {
      $deleted = $this->resource()->delete();
    }

    if($deleted)
    {
      if(request()->wantsJson())
      {
        return $this->jsonDestroySuccess();
      }

      return $this->htmlDestroySuccess();
    }

    if(request()->wantsJson())
    {
      return $this->jsonDestroyFailure();
    }

    return $this->htmlDestroyFailure();
  }
}
