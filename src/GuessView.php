<?php
namespace Dgoring\Laravel\InheritResource;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Providers\RouteServiceProvider;

trait GuessView
{
  protected $view_ns = null;

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
    $reflection = new \ReflectionClass(RouteServiceProvider::class);
    $namespace = Arr::get($reflection->getDefaultProperties(), 'namespace');

    $class = get_called_class();

    return preg_replace('/\A' . preg_quote($namespace, '/') . '\\\\/', '', $class);
  }

  protected function getViewNS()
  {
    if($this->view_ns)
    {
      return $this->view_ns;
    }

    return $this->view_ns = str_replace('\\_', '.', Str::snake(
      preg_replace('/\A([\w\\\]+)Controller\z/', '$1', $this->getControllerPath())
    )) . '.';
  }

}

