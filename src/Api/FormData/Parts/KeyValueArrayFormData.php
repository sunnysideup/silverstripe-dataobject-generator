<?php

namespace Sunnysideup\BuildDataObject\Api\FormData\Parts;

use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use Sunnysideup\BuildDataObject\Api\DBTypeConverter;

class KeyValueArrayFormData implements InterfaceForFormDataParts
{
    const KEY = 0;

    const VALUE = 1;

    private $array = [];

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

    public function getArray()
    {
        return $this->array;
    }

    public function toLiveObject()
    {
        $arrayList = ArrayList::create();
        foreach ($this->array as $pair) {
            if (isset($pair[self::KEY]) && isset($pair[self::VALUE])) {
                $key = $pair[self::KEY];
                $value = $pair[self::VALUE];
                $valuePairArray = [];
                if ($value === 'true') {
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

    private function initElementAt($index)
    {
        if (! isset($this->array[$index])) {
            $this->array[$index] = [];
        }
    }
}
