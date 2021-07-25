# DataObjectUnit
here's a theory to have a better and cleaner code. the concept is that everything like model layer and DTO layer be supported in a data object unit.

# Use case

## get data from database

```php
$user = new User();
if (!$user->load(['id' => 12])) {
    return 'user not found!';
}
return $user->name;
```

## use as DTO
```php
$user = new User($_POST);
if(!$user->validate()) { // you must config validator 
    return 'bad request';
}
$user->load(); // must update load support DTO
```

```php
$username = $_POST['username'] ?? false;
if (!$username) {
    return 'bad request';
}
$user = new User();
if (!$user->load(['name'=> $username]){
    return 'user not exist';
    // or return 'username and password does not match';
}
$password = $_POST['password'] ?? '';
if(!$user->checkPassword($password) ) {
    return 'username and password does not match';
}
$user->login();
//or
$token = $user->createAccessToken();
```
## use to do actions based on DOU

```php
$user = new User();
if (!$user->load(12)) { // the same as ['id' => 12]
    throw new error();
}
array $postList = $user->getPosts(); // array in type of post DOU or []
```

## use DOU as ORM based model data object
```php
$user = new User();
$user->load(12);
$user->name = 'other name';
$user->save(); // this will update user data on database
```

# basic methods

| method       | params                       | return         | descriptions                                                                          |
| ------------ | ---------------------------- | -------------- | ------------------------------------------------------------------------------------- |
| model        |                              | ModelHandler   | to init Model handler using prototype                                                 |
| load         |                              | [called class] | loads data from repository or database                                                |
| isStaged     |                              | boolean        | retrun change state. if `true` then some data changed but not saved                   |
| save         |                              | boolean        | saves data and return if save process succeeded.                                      |
| dataFixed    |                              | boolean        | if `true` then any changes will be staged. usually it will be set true ofter `load()` |
| onBeforeSet  | `$name:string, $value:mixed` | mixed          | middleware function before setting a value in DOU                                     |
| onBeforeSave | `$data:array`                | array          | middleware function before save oon repository                                        |


# initiate and configure
first you need to init a prototype [documentation available at miladm/prototype](https://packagist.org/packages/miladm/prototype)

### create connection

```php
use miladm\table\Connection;

class MainConnection extends Connection
{
    public $host = "127.0.0.1";
    public $databaseName = "sample";
    public $user = 'root';
    public $password = 'root';
}
```

### create prototype
```php

use miladm\Prototype;
use miladm\prototype\Schema;

class UserP extends Prototype
{
    public function init(): Schema
    {
        return $this->schema('user')
            ->string('name')
            ->email('email')
            ->hash('password')->hashFunction(fn ($data) => md5($data))
            ->json('something');
    }

    public function connection(): Connection
    {
        return new MainConnection;
    }
}
```

### create your dou

**Note:** all variables of dou __must__ be `protected`.

```php
use miladm\DataObjectUnit;
use miladm\prototype\ModelHandler;

class User extends DataObjectUnit
{
    protected string $name;
    protected string $email;
    protected ?string $something;
    protected string $password;

    function model(): ModelHandler
    {
        return UserP::model();
    }
}
```

## middleware functions

### onBeforeSave
you can change or check value if necessary. for example hash password or check if something exists;
```php
use miladm\dou\OnBeforeSave;

class User extends DataObjectUnit implements OnBeforeSave 
{
    protected string $name;
    protected string $email;
    protected ?string $something;
    protected string $password;
    protected array $roles = ['updateData'];

    function model(): ModelHandler
    {
        return UserP::model();
    }

    function onBeforeSave($data): array
    {
        if (!$this->hasPermissionTo('updateData')) {
            return [];
        }
        return $data;
    }

    private function hasPermissionTo($permissionString): bool
    {
        return in_array($permissionString, $this->roles);
    }
}
```


### onBeforeSet
you can change or check value if necessary. for example hash password or check if something exists;
```php
use miladm\dou\OnBeforeSet;

class User extends DataObjectUnit implements OnBeforeSet
{
    protected string $name;
    protected string $email;
    protected ?string $something;
    protected string $password;
    protected array $roles = ['updateData'];

    function model(): ModelHandler
    {
        return UserP::model();
    }

    function onBeforeSet($name, $value): mixed
    {
        if ($name === 'password') {
            if(!$this->passwordValidation($value)) {
                return $this->password;
            }
        }
        return $value;
    }

    private function passwordValidation($value): bool
    {
        // check if 8 character and has special characters and ect.
    }
}
```