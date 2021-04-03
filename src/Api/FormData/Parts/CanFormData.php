<?php

namespace Sunnysideup\BuildDataObject\Api\FormData\Parts;

use SilverStripe\ORM\FieldType\DBField;

class CanFormData implements InterfaceForFormDataParts
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
        if ('parent' === $value) {
            return null;
        }

        $str = '';
        if ('one' === $value) {
            $str = 'DataObject::get_one($this->class) ? false : true;';
        } elseif ('true' === $value) {
            $str = 'true;';
        } elseif ('false' === $value) {
            $str = 'false;';
        } elseif ('basedonmodeladmin' === $value) {
            $str = 'Permission::check(\'CMS_ACCESS_\'.$this->Config()->get(\'primary_model_admin_class\'), \'any\', $member);';
        } else {
            $str = "Permission::check('" . $value . '\', \'any\', $member);';
        }

        return DBField::create_field('Varchar', $str);
    }
}
