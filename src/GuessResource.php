<?php
namespace Dgoring\Laravel\InheritResource;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

trait GuessResource
{
  protected $resource_key  = null;
  protected $class_name    = null;
  protected $collection_name = null;
  protected $controller_name = null;
  protected $instance_name = null;

  protected $authorize = true;

  protected final function getControllerName()
  {
    if($this->controller_name)
    {
      return $this->controller_name;
    }

    $class = class_basename(get_called_class());

    return $this->controller_name = preg_replace('/\A(\w+)Controller\z/', '$1', $class);
  }

  protected function getClassName()
  {
    if($this->class_name)
    {
      return $this->class_name;
    }

    $class = config('inherit_resource.namespace', 'App\\') . Str::singular($this->getControllerName());

    if(class_exists($class))
    {
      return $this->class_name = $class;
    }
    else
    {
      throw new \Exception('Can\'t find class');
    }
  }

  protected function getInstanceName()
  {
    if($this->instance_name)
    {
      return $this->instance_name;
    }

    return $this->instance_name = Str::singular(Str::snake($this->getControllerName()));
  }

  protected function getCollectionName()
  {
    if($this->collection_name)
    {
      return $this->collection_name;
    }

    return $this->collection_name = Str::snake($this->getControllerName());
  }

  protected function collection()
  {
    $class = $this->getClassName();

    $query = $class::query();

    return $query;
  }

  protected $resource = null;

  protected function resource()
  {
    if($this->resource)
    {
      return $this->resource;
    }

    $value = request()->route($this->getInstanceName());

    if($this->resource_key && $this->collection() instanceof Builder)
    {
      if($value)
      {
        $instance = $this->collection()->getModel();

        return $this->resource = $this->collection()->where($instance->getTable() . '.' . $this->resource_key, $value)->firstOrFail();;
      }
    }
    else
    if($value)
    {
      return $this->resource = $this->collection()->findOrFail($value);
    }

    return $this->resource = $this->collection()->findOrNew(-1);
  }
}
