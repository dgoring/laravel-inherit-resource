<?php
namespace Dgoring\Laravel\InheritResource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

trait JsonResponses
{
  protected $json_resource_class_name = null;
  protected $json_collection_class_name = null;

  protected function getJsonCollectionClassName()
  {
    if($this->json_collection_class_name)
    {
      return $this->json_collection_class_name;
    }

    $class = config('inherit_resource.json_collections', 'App\\Http\\Resources\\') . $this->getControllerName() . 'Collection';

    if(class_exists($class))
    {
      return $this->json_collection_class_name = $class;
    }

    $class = config('inherit_resource.json_collections', 'App\\Http\\Resources\\') . $this->getControllerName();

    if(class_exists($class))
    {
      return $this->json_collection_class_name = $class;
    }

    return false;
  }

  protected function getJsonResourceClassName()
  {
    if($this->json_resource_class_name)
    {
      return $this->json_resource_class_name;
    }

    $class = config('inherit_resource.json_resources', 'App\\Http\\Resources\\') . str_singular($this->getControllerName()) . 'Resource';

    if(class_exists($class))
    {
      return $this->json_resource_class_name = $class;
    }

    $class = config('inherit_resource.json_resources', 'App\\Http\\Resources\\') . str_singular($this->getControllerName());

    if(class_exists($class))
    {
      return $this->json_resource_class_name = $class;
    }

    return false;
  }


  protected function jsonIndex()
  {
    $query = $this->collection();
    $count = $query->count();

    if(request()->has('take'))
    {
      if($take = request()->query('take'))
      {
        $query->take($take);
      }
    }
    else
    if($this->per > 0)
    {
      $query->take($this->per);
    }

    if($class = $this->getJsonCollectionClassName())
    {
      return $class::collection($query->get())->with(['Count' => $count]);
    }

    if($class = $this->getJsonResourceClassName())
    {
      return $class::collection($query->get())->with(['Count' => $count]);
    }

    return response()->json($query->get())->withHeaders(['Count' => $count]);
  }

  protected function jsonShow()
  {
    if($class = $this->getJsonResourceClassName())
    {
      return $class::make($this->resource());
    }

    return response()->json($this->resource());
  }

  protected function jsonStoreSuccess()
  {
    if($class = $this->getJsonResourceClassName())
    {
      return $class::make($this->resource());
    }

    return response()->json($this->resource());
  }

  protected function jsonStoreFailure()
  {
    return response()->json(['error' => 'Error encountered creating ' . class_basename($this->getClassName())])->status(500);
  }

  protected function jsonUpdateSuccess()
  {
    if($class = $this->getJsonResourceClassName())
    {
      return $class::make($this->resource());
    }

    return response()->json($this->resource());
  }

  protected function jsonUpdateFailure()
  {
    return response()->json(['error' => 'Error encountered updating ' . class_basename($this->getClassName())], 500);
  }

  protected function jsonDestroySuccess()
  {
    return response()->json(true);
  }

  protected function jsonDestroyFailure()
  {
    return response()->json(['error' => 'Error encountered deleting ' . class_basename($this->getClassName())], 500);
  }


}
