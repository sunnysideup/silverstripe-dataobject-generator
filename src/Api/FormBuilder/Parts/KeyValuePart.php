<?php

namespace Sunnysideup\BuildDataObject\Api\FormBuilder\Parts;

use Sunnysideup\BuildDataObject\Api\FormData\FormDataDecomposer;

class KeyValuePart extends BasePart
{
    protected function onGetInnerComposite(int $index): InnerComposite
    {
        $char = FormDataDecomposer::EXP_CHAR;
        $key = FormDataDecomposer::KEY_IDENTIFIER;
        $val = FormDataDecomposer::VALUE_IDENTIFIER;

        $keyIndex = $char . $key . $char;
        $valIndex = $char . $val . $char;

        $nameKey = $this->name . $keyIndex;
        $nameValue = $this->name . $valIndex;

        if ($this->isMultiple) {
            $nameKey .= $index;
            $nameValue .= $index;
        }
        return new InnerComposite($nameKey, $this->source1, $nameValue, $this->source2);
    }
}
