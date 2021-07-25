<?php

use miladm\DataObjectUnit;
use miladm\dou\OnBeforeSet;
use miladm\prototype\ModelHandler;

include "../index.php";


class User extends DataObjectUnit implements OnBeforeSet
{
    protected string $name;
    protected string $email;
    protected ?string $something;
    protected string $password;

    function model(): ModelHandler
    {
        return UserP::model();
    }

    function checkPassword(string $password)
    {
        return $this->password == Security::hash($password);
    }

    function onBeforeSet($name, $value): mixed
    {
        if ( // if updating pass word and it's the same as it was
            $this->dataFixed() &&
            $name == 'password' &&
            isset($this->password)  &&
            $this->checkPassword($value)
        ) {
            return Security::hash($value); //skips input
        }
        return $value;
    }
}


$user = new User();
$user->load(['id' => 1]);
$user->password = 'milado';
die(var_dump(
    $user,
    $user->isStaged(),
    $user->save()
));
