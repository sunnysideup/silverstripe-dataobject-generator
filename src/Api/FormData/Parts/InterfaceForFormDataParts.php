<?php

namespace Sunnysideup\BuildDataObject\Api\FormData\Parts;

interface InterfaceForFormDataParts
{
    public function setKeyAt($index, $key);

    public function setValueAt($index, $value);

    public function toLiveObject();
}
