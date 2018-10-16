<?php

namespace Sunnysideup\BuildDataObject\API\FormData;

use Sunnysideup\BuildDataObject\View\NewDataObject;

class FormDataDecomposer
{
    const KEY_PART = '__KEY__';
    const VALUE_PART = '__VALUE__';

    private $array;

    public function __construct(array $formData)
    {
        $array = [];
        foreach ($formData as $key => $value) {
            if ($key && $value) {
                $isKey = strpos($key, '__KEY__');
                if ($isKey || strpos($key, '__VALUE__')) {
                    $parts = explode('__', $key);
                    $element = $parts[0];
                    $key_or_value = $parts[1];
                    $index = $parts[2];
                    if (!isset($array[$element])) {
                        $array[$element] = new KeyValueArrayFormData();
                    }
                    $field = $array[$element];
                    if ($isKey) {
                        $field->setKeyAt($index, $value);
                    } else {
                        $field->setValueAt($index, $value);
                    }
                } elseif (substr($key, 0, 3) === 'can') {
                    $array[$key] = new CanFormData($value);
                } else {
                    $array[$key] = new BasicFormData($value);
                }
            }
        }
        $this->array = $array;
    }

    public function getResult(&$array)
    {
        $array = $this->array;
    }

    public function toArrayData() : NewDataObject
    {
        $newArray = [];
        foreach ($this->array as $field => $value) {
            $newArray[$field] = $value->toLiveObject();
        }
        return NewDataObject::create($newArray);
    }
}
