<?php
namespace Dgoring\Laravel\InheritResource;

use ReflectionClass;

trait GuessView
{
  protected $view_ns = null;

  protected $pageQueryVar = 'page';

  protected $views = [
    'index'  => 'index',
    'create' => 'create',
    'show'   => 'show',
    'edit'   => 'edit',
  ];

  protected function getResourceRoute()
  {
    $name = request()->route()->getName();

    return preg_replace('/\A(.+)\.[^.]+\z/', '$1', $name);
  }


  protected final function getControllerPath()
  {
    $reflection = new ReflectionClass(\App\Providers\RouteServiceProvider::class);
    $namespace = array_get($reflection->getDefaultProperties(), 'namespace');

    $class = get_called_class();

    return preg_replace('/\A' . preg_quote($namespace, '/') . '\\\\/', '', $class);
  }

  protected function getViewNS()
  {
    if($this->view_ns)
    {
      return $this->view_ns;
    }

    return $this->view_ns = str_replace('\\_', '.', snake_case(
      preg_replace('/\A([\w\\\]+)Controller\z/', '$1', $this->getControllerPath())
    )) . '.';
  }
}

