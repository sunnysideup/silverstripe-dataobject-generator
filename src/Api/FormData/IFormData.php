<?php

namespace Sunnysideup\BuildDataObject\API\FormData;


interface IFormData
{
    public function setKeyAt($index, $key);
    public function setValueAt($index, $value);
    public function toLiveObject();
}
