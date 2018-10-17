<?php

namespace Sunnysideup\BuildDataObject\API\FormBuilder\Parts;

use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\TextField;


class InnerComposite
{
    private $keyField, $valueField;

    public function __construct($nameKey, $source1, $valueKey, $source2)
    {
        //key field
        $this->keyField = self::createFormField($nameKey, $source1, 'mykey');

        if ($valueKey) {
            //value field
            $this->valueField = self::createFormField($valueKey, $source2, 'myvalue');
        }
    }

    private static function createFormField($key, &$source, $additionalClass) : FormField
    {
        if ($source) {
            asort($source);
            $field = DropdownField::create($key, '', $source)->setEmptyString('--- Please Select ---');
        } else {
            $field = TextField::create($key, '');
        }
        return $field->addExtraClass($additionalClass);
    }

    public function getNameKey() : string
    {
        return $this->keyField->getName();
    }

    public function toInnerFormField($position, $isMultiple) : FormField
    {
        if ($this->valueField || $isMultiple) {
            $subCompositeField = CompositeField::create();
            $subCompositeField->addExtraClass('InnerComposite pos'.$position);

            $subCompositeField->push($this->keyField);
            if ($this->valueField) {
                $subCompositeField->push($this->valueField);
            }
            return $subCompositeField;
        } else {
            return $this->keyField;
        }
    }
}
