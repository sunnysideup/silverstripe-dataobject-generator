<?php

namespace Sunnysideup\BuildDataObject\API\FormBuilder;

interface IFormBuilderOwner
{
    public function getBaseClass() : string;
    public function getShortBaseClass() : string;
    public function getAdditionalPrimaryFields() : array;
    public function getPossibleRelationsWithBaseClass();
    public function callAPIMethod($method, $defaultValueIfMethodIsNull);
}
