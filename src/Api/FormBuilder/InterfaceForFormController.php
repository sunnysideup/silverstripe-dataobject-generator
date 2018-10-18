<?php

namespace Sunnysideup\BuildDataObject\Api\FormBuilder;

/**
 * What do we need in the form controller?
 */

interface InterfaceForFormController
{

    public function getBaseClass() : string;

    public function getShortBaseClass() : string;

    public function getAdditionalPrimaryFields() : array;

    public function getPossibleRelationsWithBaseClass();

    public function callAPIMethod($method, $defaultValueIfMethodIsNull);

}
