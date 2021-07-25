<?php

namespace miladm\dou;

interface OnBeforeSave
{
    function onBeforeSave(array $data): array;
}
