<?php

namespace Sunnysideup\BuildDataObject\API\FormBuilder\Parts;

use Sunnysideup\BuildDataObject\API\FormBuilder\Parts\InnerComposite;


class PlainPart extends BasePart
{
    protected function onGetInnerComposite(int $index) : InnerComposite
    {
        if ($this->isMultiple) {
            $nameKey = $this->name.$index;
        } else {
            $nameKey = $this->name;
        }

        return new InnerComposite($nameKey, $this->source1, null, null);
    }
}
