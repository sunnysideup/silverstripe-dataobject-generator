<?php

namespace Sunnysideup\BuildDataObject\Api\FormBuilder\Parts;

use Sunnysideup\BuildDataObject\Api\FormData\FormDataDecomposer;

class PlainPart extends BasePart
{
    protected function onGetInnerComposite(int $index): InnerComposite
    {
        $listIndex =
            FormDataDecomposer::EXP_CHAR .
            FormDataDecomposer::LIST_IDENTIFIER .
            FormDataDecomposer::EXP_CHAR;
        $nameKey = $this->name;
        if ($this->isMultiple) {
            $nameKey .= $listIndex . $index;
        }

        return new InnerComposite($nameKey, $this->source1, null, null);
    }
}
