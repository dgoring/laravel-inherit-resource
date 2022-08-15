# Laravel Inherit Resource

Inspired by the ruby gem InheritResource

I bring you quick minimalist resource controllers in laravel

## Basic usage
Simply place resource trait in your controller
```php
namespace App\Http\Controllers;

use Dgoring\Laravel\InheritResource\Resource;

class UsersController extends Controller
{
  use Resource;
}
```
with the resource route in your routes file
```php
Route::resource('users', 'UsersController');
```

And all the resource functions are placed and run against the class as well as loading the views

all settings are assumed by the name of the controller so `UsersController` is assumed to use
- `App\User` for the model class
- `user` the route parameter
- these views are assumed for there respective resource functions
-- `users.index` with `$users` passed into it as a paginator collection
-- `users.show` with `$user` passed into it as the instance of the record
-- `users.create` with `$user` a fresh new instance of the model
-- `users.edit` with `$user` passed into it as the instance of the record

## the query
To override the query used for say filtering the index page
you can override the `collection` function

```php
class UsersController extends Controller
{
  use Resource { collection as query; }


  protected function collection()
  {
    $query = $this->query();

    if($search = request()->query('search'))
    {
      $query->where('name', 'like', "%{$search}%");
    }

    return $query;
  }
}
```

The `collection` function is also used for all resource functions to give the base query
So can be used to filter content across the controller

```php
  protected function collection()
  {
    $query = $this->query();

    $query->where('active', 1);

    return $query;
  }
```

## Authorized Resource

By default all the actions are put through the authorize function, so you can control access to this resource

- `index` ->authorize(`viewAny`, class)
- `create` ->authorize(`create`, new instance)
- `store` ->authorize(`create`, new instance)
- `edit` ->authorize(`update`, instance)
- `update` ->authorize(`update`, instance)
- `destroy` ->authorize(`delete`, instance)

## Validation rules

You can specify validation rules for the `store` and `update` functions but defining `validationRules` function

```php
class UsersController extends Controller
{
  use Resource;

  protected function validationRules()
  {
    if($this->resource()->exists)
    {
      return [
        'name' => 'string',
      ];
    }
    else
    {
      return [
        'name' => 'string|required',
      ];
    }
  }
}
```


## nested resource
Nested resources are just as easy

Simply setup your route

```php
Route::resource('teams.users', 'UsersController');
```

And return the relationship in the collection function
```php
use App\Team;

class UsersController extends Controller
{
  use Resource { collection as query; }


  protected function collection()
  {
    $team = Team::findOrFail(request()->route('team'));

    $query = $team->users();

    if($search = request()->query('search'))
    {
      $query->where('name', 'like', "%{$search}%");
    }

    return $query;
  }
}
```

as the collection function is used for the `create` and `store` functions
as long as your return the relationship from the parent model the relationship should be saved to the new record
and it should also already be in the model in the `create` view variable

## JSON

this resource is also ready to respond to json requests
note that the index will return a total count in the response headers as `Count`

also if defined in a appropriate place a JsonCollection or JsonResource formatter will be used for the model i.e.
```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{

}

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UsersCollection extends ResourceCollection
{

}
```


And you can also allow only JSON responses
```php
namespace App\Http\Controllers;

use Dgoring\Laravel\InheritResource\JsonResource;

class UsersController extends Controller
{
  use JsonResource;
}
```



or only HTML
```php
namespace App\Http\Controllers;

use Dgoring\Laravel\InheritResource\HtmlResource;

class UsersController extends Controller
{
  use HtmlResource;
}
```


## Overrides

### Namespaces
By default models are assumed to be under the `App` namespace but you can change that by adding the file `config/inherit_resource.php`

below shows the config file for `App\Models`

and also the namespaces for the json resources and collections

```php
<?php

return [
  'namespace' => 'App\\Models\\',

  'json_resources' => 'App\\Http\\Resources\\',
  'json_collections' => 'App\\Http\\Collections\\',
];

```

### Controller variables


```php
class UsersController extends Controller
{
  use Resource;

  public function __construct()
  {
    $this->class_name = CustomUser::class; // Model Class
    $this->instance_name = 'custom_user';  // Route parameter and name of variable passed to single record views
    $this->collection_name = 'customer_users'; // Name of variable passed to index view

    $this->authorize = false; // Switch to turn off authorize checks (default is on)

    $this->distinctFix = true; // if the query builder returns a distinct query, a fix will be applied to get the distinct count used for pagination
    $this->fillOnlyValidated = false; // IF enabled only the validated fields will be allowed to be mass assigned to the model (will default on in next major version)

    //Only for Resource and HtmlResource

    $this->view_ns = 'customer_users'; // dot notation path to views folder

    $this->views = [ // name of view used for each function
      'index'  => 'index',
      'create' => 'form',
      'show'   => 'show',
      'edit'   => 'form',
    ];


  }
}

```

### Responses
these response functions can be overridden to allow you to have completely custom responses to certain actions without having to override the entire route function


```php

class UsersController extends Controller
{
  use Resource;

  protected function htmlIndex();
  protected function htmlShow();
  protected function htmlCreate();
  protected function htmlStoreSuccess();
  protected function htmlStoreFailure();
  protected function htmlEdit();
  protected function htmlUpdateSuccess();
  protected function htmlUpdateFailure();
  protected function htmlDestroySuccess();
  protected function htmlDestroyFailure();

  protected function jsonIndex();
  protected function jsonShow();
  protected function jsonStoreSuccess();
  protected function jsonStoreFailure();
  protected function jsonUpdateSuccess();
  protected function jsonUpdateFailure();
  protected function jsonDestroySuccess();
  protected function jsonDestroyFailure();
}



