<?php

namespace Sunnysideup\BuildDataObject\Api\FormData\Parts;

use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use Sunnysideup\BuildDataObject\Api\DBTypeConverter;

class KeyArrayFormData implements InterfaceForFormDataParts
{
    private $array = [];

    public function setKeyAt($index, $key)
    {
        if (! isset($this->array[$index])) {
            $this->array[$index] = null;
        }
    }

    public function setValueAt($index, $value)
    {
        $this->setKeyAt($index, null);
        $this->array[$index] = $value;
    }

    public function getArray()
    {
        return $this->array;
    }

    public function toLiveObject()
    {
        $arrayList = ArrayList::create();
        foreach ($this->array as $value) {
            if ($value) {
                $useRawValue = false;
                if ('true' === $value || true === $value) {
                    $useRawValue = true;
                }

                $valueArray = [
                    'Value' => DBTypeConverter::fromDropdown($value)->toDataObject(),
                    'RawValue' => $value,
                    'UseRawValue' => $useRawValue,
                ];
                $arrayList->push(ArrayData::create($valueArray));
            }
        }

        return $arrayList;
    }
}
