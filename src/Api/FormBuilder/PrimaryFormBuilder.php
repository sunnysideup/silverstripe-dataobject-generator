<?php

namespace Sunnysideup\BuildDataObject\Api\FormBuilder;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

/**
 * What is in the first of two forms for the builder?
 */
class PrimaryFormBuilder extends BaseFormBuilder
{
    protected function onGetHeaderFields(InterfaceForFormController $owner, array &$headerFields)
    {
        $possibleExtensions = $owner->getPossibleRelationsWithBaseClass();
        // already sorted?
        // asort($possibleExtensions);

        $possibleBasedOn = $possibleExtensions;
        unset($possibleBasedOn[DataObject::class]);

        $headerFields[] = LiteralField::create(
            'Instructions',
            '<p>
                Please complete fields as you see fit.
                For more information on the Silverstripe Model
                visit the <a href="https://docs.silverstripe.org/en/4/developer_guides/model/data_model_and_orm/">docs</a>
                and the <a href="https://api.silverstripe.org/4/SilverStripe/ORM/DataObject.html">api</a>.
            </p>'
        );
        $headerFields[] = HeaderField::create('BasedOnHeader', 'Based On ...');
        $headerFields[] = DropdownField::create('Template', '', (string) $possibleBasedOn)->setEmptyString('--- PRELOAD VALUES FROM ---');
        $headerFields[] = HeaderField::create('NameSpaceHeader', 'Namespace your ' . $owner->getShortBaseClass());
        $headerFields[] = TextField::create('NameSpace', '')->setAttribute('placeholder', 'e.g. MyCompany\\MyProject\\Model');
        $headerFields[] = HeaderField::create('NameHeader', 'Name your ' . $owner->getShortBaseClass())->setDescription('e.g. MyPage or MyDataObject (do not include namespace)');
        $headerFields[] = TextField::create('Name', '');
        $headerFields[] = HeaderField::create('ExtendsHeader', 'Extends');
        $headerFields[] = DropdownField::create('Extends', '', (string) $possibleExtensions)->setValue($owner->getBaseClass());

        $additionalFields = $owner->getAdditionalPrimaryFields();
        foreach ($additionalFields as $additionalField) {
            $headerFields[] = $additionalField;
        }
    }
}
