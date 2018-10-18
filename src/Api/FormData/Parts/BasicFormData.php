<?php

namespace Sunnysideup\BuildDataObject\Api\FormData\Parts;

use Sunnysideup\BuildDataObject\Api\FormData\Parts\InterfaceForFormDataParts;

class BasicFormData implements InterfaceForFormDataParts
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
