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

    protected abstract function onGetInnerComposite(int $index) : InnerComposite;

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

    private function createAddRemoveField($position) : LiteralField
    {
        return LiteralField::create(
            $this->name.'_ADD_'.$position,
            '
                <div class="CompositeField add-and-remove">
                    <a href="#" class="add first-add"><i class="material-icons">add_circle_outline</i></a>
                    <a href="#" class="remove"><i class="material-icons">remove_circle_outline</i></a>
                </div>
            '
        );
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
            // TODO: add buttons dynamically in the front-end side rather than doing here
            // e.g. jQuery(() => $('.OuterComposite.multiple').append(buttons));
            //$addRemoveField = $this->createAddRemoveField($innerCount);
            //$compositeField->push($addRemoveField);
            $compositeField->addExtraClass('multiple');
        }
        return $compositeField;
    }
}
