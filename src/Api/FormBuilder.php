<?php


namespace Sunnysideup\BuildDataObject\API;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\CompositeField;


interface IFormBuilderOwner
{
    public function getBaseClass() : string;
    public function getShortBaseClass() : string;
    public function getAdditionalPrimaryFields() : array;
    public function getPossibleRelationsWithBaseClass();
    public function callAPIMethod($method, $defaultValueIfMethodIsNull);
}

class SubField
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

abstract class FormPart
{
    private $owner;
    private $maxChildren;
    private $subFields;
    private $formFieldsWithMultiple;
    protected $name;
    protected $source1;
    protected $source2;
    protected $isMultiple;

    public function __construct($owner, $name, $isMultiple, $sourceMethod1, $sourceMethod2)
    {
        $this->owner = $owner;
        $this->name = $name;
        $this->isMultiple = $isMultiple;

        if ($isMultiple) {
            $this->maxChildren = 12;
        } else {
            $this->maxChildren = 1;
        }

        //work out sources
        $this->source1 = $owner->callAPIMethod($sourceMethod1, null);
        $this->source2 = $owner->callAPIMethod($sourceMethod2, 'ignore');

        $this->subFields = array();
        $this->formFieldsWithMultiple = array();

        //work out field names
        $this->workOutFields();
    }

    private function workOutFields()
    {
        for ($i = 1; $i <= $this->maxChildren; $i++) {
            $subField = $this->onGetSubField($i);
            array_push($this->subFields, $subField);
            if ($this->isMultiple) {
                $name = $subField->getNameKey();
                $this->formFieldsWithMultiple[$name] = true;
            }
        }
    }

    private function createHeaderField() : HeaderField
    {
        $title = str_replace('_', ' ', $this->name);
        return HeaderField::create($this->name.'_HEADER', $title);
    }

    private function createAddRemoveField($position) : LiteralField
    {
        return LiteralField::create($this->name.'_ADD_'.$position,
        '
            <div class="CompositeField add-and-remove">
                <a href="#" class="add first-add"><i class="material-icons">add_circle_outline</i></a>
                <a href="#" class="remove"><i class="material-icons">remove_circle_outline</i></a>
            </div>
        ');
    }

    private function isMultiple(SubField $subField)
    {
        return isset($this->formFieldsWithMultiple[$subField->getNameKey()]);
    }

    public function toCompositeField(int $outerPosition) : CompositeField
    {
        $compositeField = CompositeField::create();
        $compositeField->addExtraClass('OuterComposite pos'.$outerPosition);

        $headerField = $this->createHeaderField();
        $compositeField->push($headerField);

        $innerCount = 0;
        foreach ($this->subFields as $innerCount => $subField) {
            $innerCount++;
            $isMultiple = $this->isMultiple($subField);
            $innerField = $subField->toInnerFormField($innerCount, $isMultiple);
            $compositeField->push($innerField);
        }

        if ($this->isMultiple) {
            // TODO: add buttons dynamically in the front-end side rather than doing here
            // e.g. jQuery(() => $('.OuterComposite.multiple').append(buttons));
            //$addRemoveField = $this->createAddRemoveField($innerCount);
            //$compositeField->push($addRemoveField);
            $compositeField->addExtraClass('multiple');
        }
        return $compositeField;
    }

    protected abstract function onGetSubField(int $index);
}

class PlainFormPart extends FormPart
{
    protected function onGetSubField(int $index)
    {
        if ($this->isMultiple) {
            $nameKey = $this->name.$index;
        } else {
            $nameKey = $this->name;
        }

        return new SubField($nameKey, $this->source1, null, null);
    }
}

class KeyValueFormPart extends FormPart
{
    protected function onGetSubField(int $index)
    {
        if ($this->isMultiple) {
            $nameKey = $this->name.'__KEY__'.$index;
            $nameValue = $this->name.'__VALUE__'.$index;
        } else {
            $nameKey = $this->name.'__KEY__';
            $nameValue = $this->name.'__VALUE__';
        }
        return new SubField($nameKey, $this->source1, $nameValue, $this->source2);
    }
}

abstract class FormBuilder
{
    private $owner;
    private $toBuild;
    private $headerFields;

    public function __construct(IFormBuilderOwner $owner, array $toBuild)
    {
        $this->owner = $owner;
        $this->toBuild = $toBuild;
        $this->headerFields = array();
        $this->onGetHeaderFields($owner, $this->headerFields);
    }

    private function createFormPartOnItem($item) : FormPart
    {
        $name = $item['Name'];
        $isMultiple = $item['IsMultiple'];
        $sourceMethod1 = $item['SourceMethod1'];
        $sourceMethod2 = $item['SourceMethod2'];

        //work out style
        $hasKeyAndValue = $sourceMethod1 && $sourceMethod2;
        if ($hasKeyAndValue) {
            return new KeyValueFormPart($this->owner, $name, $isMultiple, $sourceMethod1, $sourceMethod2);
        } else {
            return new PlainFormPart($this->owner, $name, $isMultiple, $sourceMethod1, $sourceMethod2);
        }
    }

    public function build() : FieldList
    {
        $finalFields = new FieldList($this->headerFields);

        $outerCount = 0;
        foreach ($this->toBuild as $item) {
            $outerCount++;

            $part = $this->createFormPartOnItem($item);
            $compositeField = $part->toCompositeField($outerCount);
            $finalFields->push($compositeField);
        }
        return $finalFields;
    }

    protected abstract function onGetHeaderFields(IFormBuilderOwner $owner, array &$headerFields);
}


class PrimaryFormBuilder extends FormBuilder
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

class SecondaryFormBuilder extends FormBuilder
{
    protected function onGetHeaderFields(IFormBuilderOwner $owner, array &$headerFields)
    {
        // nothing
    }
}
