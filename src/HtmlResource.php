<?php
namespace Dgoring\Laravel\InheritResource;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

trait HtmlResource
{
  use GuessResource, ViewResponses;

  use AuthorizesRequests, ValidatesRequests;

  protected $per = 15;

  protected $fillOnlyValidated = false;

  protected $saveTransaction = false;

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

    $saved = false;

    if($this->saveTransaction)
    {
      $saved = DB::transaction(function () {
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

    $saved = false;

    if($this->saveTransaction)
    {
      $saved = DB::transaction(function () {
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
      return $this->htmlDestroySuccess();
    }

    return $this->htmlDestroyFailure();
  }
}
