<?php

namespace Sunnysideup\BuildDataObject\Api\FormData\Parts;

use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use Sunnysideup\BuildDataObject\Api\DBTypeConverter;

class KeyValueArrayFormData implements InterfaceForFormDataParts
{
    /**
     * @var int
     */
    public const KEY = 0;

    /**
     * @var int
     */
    public const VALUE = 1;

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
                $useRawValue = false;
                if ($value === 'true' || $value === true) {
                    $useRawValue = true;
                }
                $valuePairArray = [
                    'Key' => $key,
                    'Value' => DBTypeConverter::fromDropdown($value)->toDataObject(),
                    'RawValue' => $value,
                    'UseRawValue' => $useRawValue,
                ];
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
