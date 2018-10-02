<?php

namespace Sunnysideup\BuildDataObject\API\FormData;

use SilverStripe\ORM\FieldType\DBField;


class CanFormData implements IFormData
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function setKeyAt($index, $key)
    {
    }

    public function setValueAt($index, $value)
    {
    }

    public function toLiveObject()
    {
        $value = $this->value;
        if ($value === 'parent') {
            return null;
        }

        $str = '';
        if ($value === 'one') {
            $str = 'DataObject::get_one($this->class) ? false : true;';
        } elseif ($value === 'true') {
            $str = 'true;';
        } elseif ($value === 'false') {
            $str = 'false;';
        } else {
            $str = 'Permission::check(\''.$value.'\', \'any\', $member);';
        }

        return DBField::create_field('Varchar', $str);
    }
}
