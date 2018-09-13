<?php

namespace Sunnysideup\BuildDataObject\API\FormData;

use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use Sunnysideup\BuildDataObject\API\DBTypeConverter;


interface IFormData
{
    public function setKeyAt($index, $key);
    public function setValueAt($index, $value);
    public function toLiveObject();
}

class KeyValueArrayFormData implements IFormData
{
    const KEY = 0;
    const VALUE = 1;

    private $array = array();

    private function initElementAt($index)
    {
        if (!isset($this->array[$index])) {
            $this->array[$index] = [];
        }
    }

    public function setKeyAt($index, $key)
    {
        $this->initElementAt($index);
        $this->array[$index][self::KEY] = $key;
    }

    public function setValueAt($index, $value)
    {
        $this->initElementAt($index);
        $this->array[$index][self::VALUE] = $value;
    }

    public function getArray(&$array)
    {
        $array = $this->array;
    }

    public function toLiveObject()
    {
        $arrayList = ArrayList::create();
        foreach ($this->array as $e => $pair) {
            if (isset($pair[self::KEY]) && isset($pair[self::VALUE])) {
                $key = $pair[self::KEY];
                $value = $pair[self::VALUE];
                $valurPairArray = null;
                if ($value == 'true') {
                    $valuePairArray = [
                        'Key' => $key,
                        'UnquotedValue' => $value,
                    ];
                } else {
                    $valuePairArray = [
                        'Key' => $key,
                        'Value' => DBTypeConverter::fromDropdown($value)->toDataObject(),
                    ];
                }
                $arrayList->push(ArrayData::create($valuePairArray));
            }
        }
        return $arrayList;
    }
}

class CanFormData implements IFormData
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function setKeyAt($index, $key)
    {
    }

    public function setValueAt($index, $value)
    {
    }

    public function toLiveObject()
    {
        $value = $this->value;
        if ($value === 'parent') {
            return null;
        }

        $str = '';
        if ($value === 'one') {
            $str = 'DataObject::get_one($this->class) ? false : true;';
        } elseif ($value === 'true') {
            $str = 'true;';
        } elseif ($value === 'false') {
            $str = 'false;';
        } else {
            $str = 'Permission::check(\''.$value.'\', \'any\', $member);';
        }

        return DBField::create_field('Varchar', $str);
    }
}

class PlainFormData implements IFormData
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function setKeyAt($index, $key)
    {
    }

    public function setValueAt($index, $value)
    {
    }

    public function toLiveObject()
    {
        return $this->value;
    }
}
