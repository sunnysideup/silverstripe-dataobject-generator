<?php

namespace Sunnysideup\BuildDataObject\API\FormBuilder;

use SilverStripe\Forms\FieldList;
use Sunnysideup\BuildDataObject\API\FormBuilder\Parts\BasePart;
use Sunnysideup\BuildDataObject\API\FormBuilder\Parts\KeyValuePart;
use Sunnysideup\BuildDataObject\API\FormBuilder\Parts\PlainPart;

/**
 * abstract concept of how to build a form
 */

abstract class BaseFormBuilder
{
    private $owner;
    private $toBuild;
    private $headerFields;

    protected abstract function onGetHeaderFields(InterfaceForFormController $owner, array &$headerFields);

    public function __construct(InterfaceForFormController $owner, array $toBuild)
    {
        $this->owner = $owner;
        $this->toBuild = $toBuild;
        $this->headerFields = array();
        $this->onGetHeaderFields($owner, $this->headerFields);
    }

    private function createFormPartOnItem($item) : BasePart
    {
        $name = $item['Name'];
        $isMultiple = $item['IsMultiple'];
        $sourceMethod1 = $item['SourceMethod1'];
        $sourceMethod2 = $item['SourceMethod2'];

        //work out style
        $hasKeyAndValue = $sourceMethod1 && $sourceMethod2;
        if ($hasKeyAndValue) {
            return new KeyValuePart($this->owner, $name, $isMultiple, $sourceMethod1, $sourceMethod2);
        } else {
            return new PlainPart($this->owner, $name, $isMultiple, $sourceMethod1, $sourceMethod2);
        }
    }

    public function build() : FieldList
    {
        $finalFields = new FieldList($this->headerFields);

        $parts = [];
        foreach ($this->toBuild as $item) {
            $part = $this->createFormPartOnItem($item);
            array_push($parts, $part);
        }

        $outerCount = 0;
        foreach ($parts as $part) {
            $outerCount++;
            $compositeField = $part->toCompositeField($outerCount);
            $finalFields->push($compositeField);
        }

        return $finalFields;
    }
}
