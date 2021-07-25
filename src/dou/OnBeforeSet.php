<?php

namespace miladm\dou;

interface OnBeforeSet
{
    function onBeforeSet($name, $value): mixed;
}
