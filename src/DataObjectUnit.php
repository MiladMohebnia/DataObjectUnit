<?php

namespace miladm;

use miladm\dou\DataObject;
use miladm\prototype\ModelHandler;

abstract class DataObjectUnit extends DataObject
{
    protected $__updateData__ = [];
    private $__dataFixed__ = false;
    private ?array $__condition__ = null;

    protected function onBeforeSet($name, $value): mixed
    {
        return $value;
    }

    function __set($name, $value)
    {
        $value = $this->onBeforeSet($name, $value);
        if ($this->__get($name) !== $value) {
            if ($this->__dataFixed__) {
                $this->__updateData__[$name] = $value;
            } else {
                parent::__set($name, $value);
            }
        }
    }

    function __get($name)
    {
        return $this->__updateData__[$name] ?? parent::__get($name);
    }

    abstract function model(): ModelHandler;

    function load(array $condition)
    {
        if (!$condition) {
            return false;
        }
        $this->__condition__ = $condition;
        $data = $this->model()->get($this->__condition__);
        if (!$data || count($data) == 0) {
            return false;
        } else if (count($data) > 1) {
            // throw new Exception('multiple results for entered $condition');
            return false;
        }
        $this->injectData($data[0]);
        $this->__dataFixed__ = true;
        return $this;
    }

    function isStaged(): bool
    {
        return count($this->__updateData__) ? true : false;
    }

    function onBeforeSave(array $data): array
    {
        return $data;
    }

    function save(): bool
    {
        if (!$this->isStaged()) {
            return false;
        }
        if (!$this?->__condition__) {
            return false;
        }
        $updateDataList = (array) $this->__updateData__;
        $updateDataList = $this->onBeforeSave($updateDataList);
        return $this->model()->update($updateDataList, $this->__condition__) ? true : false;
    }

    function dataFixed(): bool
    {
        return $this->__dataFixed__;
    }
}
