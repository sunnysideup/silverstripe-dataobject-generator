<?php

namespace Sunnysideup\BuildDataObject\Api\FormData;

use Sunnysideup\BuildDataObject\Api\FormData\Parts\KeyValueArrayFormData;
use Sunnysideup\BuildDataObject\Api\FormData\Parts\KeyArrayFormData;
use Sunnysideup\BuildDataObject\Api\FormData\Parts\CanFormData;
use Sunnysideup\BuildDataObject\Api\FormData\Parts\BasicFormData;
use Sunnysideup\BuildDataObject\Api\FormData\Parts\InterfaceForFormDataParts;

use Sunnysideup\BuildDataObject\View\NewDataObject;

class FormDataDecomposer
{
    const EXP_CHAR = '____';

    const LIST_IDENTIFIER = 'LIST';

    const KEY_IDENTIFIER = 'KEY';

    const VALUE_IDENTIFIER = 'VALUE';

    private $array;

    public function __construct(array $formData)
    {
        $array = [];
        foreach ($formData as $key => $value) {
            if ($key && $value) {
                $parts = explode(self::EXP_CHAR, $key);
                if (count($parts) === 3) {
                    $element = $parts[0];
                    $type = $parts[1];
                    $index = $parts[2];

                    $isKey = $type === self::KEY_IDENTIFIER ? true : false;
                    $isValue = $type === self::VALUE_IDENTIFIER ? true : false;
                    $isList = $type === self::LIST_IDENTIFIER ? true : false;
                    if ($isKey || $isValue) {
                        if (!isset($array[$element])) {
                            $array[$element] = new KeyValueArrayFormData();
                        }
                        $field = $array[$element];
                        if ($isKey) {
                            $field->setKeyAt($index, $value);
                        } else {
                            $field->setValueAt($index, $value);
                        }
                    } elseif ($isList) {
                        $parts = explode(self::EXP_CHAR, $key);
                        $element = $parts[0];
                        $index = $parts[2];
                        if (!isset($array[$element])) {
                            $array[$element] = new KeyArrayFormData();
                        }
                        $field = $array[$element];
                        $field->setValueAt($index, $value);
                    } else {
                        user_error('Can not decode data --- '.$key.' --- '.$value.' ---');
                    }
                } elseif (substr($key, 0, 3) === 'can') {
                    //todo: make more solid
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
