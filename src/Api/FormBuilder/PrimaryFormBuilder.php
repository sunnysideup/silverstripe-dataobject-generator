<?php

namespace Sunnysideup\BuildDataObject\API\FormBuilder;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;


class PrimaryFormBuilder extends BaseFormBuilder
{
    protected function onGetHeaderFields(IFormBuilderOwner $owner, array &$headerFields)
    {
        $possibleExtensions = $owner->getPossibleRelationsWithBaseClass();
        // already sorted?
        // asort($possibleExtensions);

        $possibleBasedOn = $possibleExtensions;
        unset($possibleBasedOn[DataObject::class]);

        array_push($headerFields, HeaderField::create('BasedOnHeader', 'Based On (OPTIONAL) ...'));
        array_push($headerFields, DropdownField::create('Template', '', $possibleBasedOn)->setEmptyString('--- PRELOAD VALUES FROM ---'));
        array_push($headerFields, HeaderField::create('NameSpaceHeader', 'Namespace your '.$owner->getShortBaseClass()));
        array_push($headerFields, TextField::create('NameSpace', '')->setAttribute('placeholder', 'MyProject\\Model'));
        array_push($headerFields, HeaderField::create('NameHeader', 'Name your '.$owner->getShortBaseClass()));
        array_push($headerFields, TextField::create('Name', ''));
        array_push($headerFields, HeaderField::create('ExtendsHeader', 'Extends'));
        array_push($headerFields, DropdownField::create('Extends', '', $possibleExtensions)->setValue($owner->getBaseClass()));

        $additionalFields = $owner->getAdditionalPrimaryFields();
        foreach ($additionalFields as $additionalField) {
            array_push($headerFields, $additionalField);
        }
    }
}
