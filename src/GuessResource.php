<?php
namespace Dgoring\Laravel\InheritResource;

trait GuessResource
{
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

    $class = config('inherit_resource.namespace', 'App\\') . str_singular($this->getControllerName());

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

    return $this->instance_name = str_singular(snake_case($this->getControllerName()));
  }

  protected function getCollectionName()
  {
    if($this->collection_name)
    {
      return $this->collection_name;
    }

    return $this->collection_name = snake_case($this->getControllerName());
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

    $class = $this->getClassName();

    if($id = request()->{$this->getInstanceName()})
    {
      return $this->resource = $this->collection()->findOrFail($id);
    }

    return $this->resource = $this->collection()->findOrNew(-1);
  }
}
