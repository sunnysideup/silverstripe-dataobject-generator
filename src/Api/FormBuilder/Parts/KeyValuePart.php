<?php

namespace Sunnysideup\BuildDataObject\API\FormBuilder\Parts;

use Sunnysideup\BuildDataObject\API\FormBuilder\InnerComposite;


class KeyValuePart extends BasePart
{
    protected function onGetInnerComposite(int $index) : InnerComposite
    {
        if ($this->isMultiple) {
            $nameKey = $this->name.'__KEY__'.$index;
            $nameValue = $this->name.'__VALUE__'.$index;
        } else {
            $nameKey = $this->name.'__KEY__';
            $nameValue = $this->name.'__VALUE__';
        }
        return new InnerComposite($nameKey, $this->source1, $nameValue, $this->source2);
    }
}
