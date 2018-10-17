<?php

namespace Sunnysideup\BuildDataObject\API\FormData;


interface InterfaceForFormDataParts
{
    public function setKeyAt($index, $key);
    public function setValueAt($index, $value);
    public function toLiveObject();
}
