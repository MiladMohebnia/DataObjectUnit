<?php

namespace miladm\dou;

class DataObject
{
    protected $__data__ = [];

    function __set($name, $value)
    {
        if (!property_exists($this, $name)) {
            $this->__data__[$name] = $value;
        } else {
            if (isset($this->$name) && is_string($this->$name) && class_exists($this->$name)) {
                $this->$name = new $this->$name($value);
            } elseif (isset($this->$name) && is_object($this->$name)) {
                if ($this->$name instanceof DataObject) {
                    $this->$name = new $this->$name($value);
                } else {

                    // bad implementation
                    /**
                     * Todo: update this part
                     */
                    foreach ((array) $this->$name as $key => $val) {
                        $this->$name->$key = new $val($value->$key);
                    }
                }
            } else {
                $this->$name = $value;
            }
        }
        return $this;
    }

    function __get($name)
    {
        return $this->$name ?? $this->__data__[$name] ?? null;
    }

    function __construct($data = false)
    {
        $this->init();
        if ($data) {
            $this->injectData($data);
        }
    }

    public function init()
    {
    }

    public function injectData($data)
    {
        $data = (array) $data;
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
        return $this;
    }
}
