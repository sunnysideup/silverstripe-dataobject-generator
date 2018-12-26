<?php

namespace Sunnysideup\BuildDataObject\Api\FormBuilder\Parts;

use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use Sunnysideup\BuildDataObject\Api\FormBuilder\Parts\InnerComposite;

abstract class BasePart
{
    private $owner;
    private $maxChildren;
    private $innerComposites;
    private $formFieldsWithMultiple;
    protected $name;
    protected $source1;
    protected $source2;
    protected $isMultiple;

    abstract protected function onGetInnerComposite(int $index) : InnerComposite;

    public function __construct($owner, $name, $isMultiple, $sourceMethod1, $sourceMethod2)
    {
        $this->owner = $owner;
        $this->name = $name;
        $this->isMultiple = $isMultiple;

        if ($isMultiple) {
            $this->maxChildren = 24;
        } else {
            $this->maxChildren = 1;
        }

        //work out sources
        $this->source1 = $owner->callAPIMethod($sourceMethod1, null);
        $this->source2 = $owner->callAPIMethod($sourceMethod2, 'ignore');

        $this->innerComposites = array();
        $this->formFieldsWithMultiple = array();

        //work out field names
        $this->workOutFields();
    }

    private function workOutFields()
    {
        for ($i = 1; $i <= $this->maxChildren; $i++) {
            $innerComposite = $this->onGetInnerComposite($i);
            array_push($this->innerComposites, $innerComposite);
            if ($this->isMultiple) {
                $name = $innerComposite->getNameKey();
                $this->formFieldsWithMultiple[$name] = true;
            }
        }
    }

    private function createHeaderField() : HeaderField
    {
        $title = str_replace('_', ' ', $this->name);
        return HeaderField::create($this->name.'_HEADER', $title);
    }

    private function isMultiple(InnerComposite $innerComposite)
    {
        return isset($this->formFieldsWithMultiple[$innerComposite->getNameKey()]);
    }

    public function toCompositeField(int $outerPosition) : CompositeField
    {
        $compositeField = CompositeField::create();
        $compositeField->addExtraClass('OuterComposite pos'.$outerPosition);

        $headerField = $this->createHeaderField();
        $compositeField->push($headerField);

        $innerCount = 0;
        foreach ($this->innerComposites as $innerCount => $innerComposite) {
            $innerCount++;
            $isMultiple = $this->isMultiple($innerComposite);
            $innerField = $innerComposite->toInnerFormField($innerCount, $isMultiple);
            $compositeField->push($innerField);
        }

        if ($this->isMultiple) {
            $compositeField->addExtraClass('multiple');
        }
        return $compositeField;
    }
}
