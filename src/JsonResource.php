<?php
namespace Dgoring\Laravel\InheritResource;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

trait JsonResource
{
  use GuessResource, JsonResponses;

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

    return $this->jsonIndex();
  }

  public function show()
  {
    if($this->authorize)
    {
      $this->authorize('view', $this->resource());
    }

    return $this->jsonShow();
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
      return $this->jsonStoreSuccess($attributes);
    }

    return $this->jsonStoreFailure($attributes);
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
      return $this->jsonUpdateSuccess($attributes);
    }

    return $this->jsonUpdateFailure($attributes);
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
      return $this->jsonDestroySuccess();
    }

    return $this->jsonDestroyFailure();
  }
}
