<?php

namespace Sunnysideup\BuildDataObject\API\FormData;

use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use Sunnysideup\BuildDataObject\API\DBTypeConverter;


class KeyValueArrayFormData implements InterfaceForFormDataParts
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
