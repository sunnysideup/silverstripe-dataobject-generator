<?php

namespace Sunnysideup\BuildDataObject\View;
use SilverStripe\View\ViewableData;
use SilverStripe\Core\ClassInfo;


class ClassObject  extends ViewableData {

    protected $fullName = '';

    public function __construct($fullName)
    {
        $this->fullName = $fullName;
    }

    public function FullName()
    {
        return $this->getFullName();
    }

    public function getFullName()
    {
        return $this->fullName;
    }

    public function ShortName()
    {
        return $this->getShortName();
    }

    function getShortName()
    {
        $shortName = '';
        if(class_exists($this->fullName)) {
            $shortName = ClassInfo::shortName($this->fullName);
        } else {
            $array = explode('\\', $this->fullName);
            if(count($array) > 1) {
                $shortName = end($array);
            }
        }
        if($shortName) {
            return $shortName;
        }
        return $this->fullName;
    }

    public function isClass()
    {
        if(class_exists($this->fullName)) {
            return true;
        } else {
            if(class_exists('\\'.$this->fullName)) {
                return true;
            }
            $array = explode('\\', $this->fullName);
            if(count($array) > 1) {
                return true;
            }
        }

    }

    function forTemplate()
    {
        user_error('You need to add .ShortName or .FullName to variables with the following value: '.$this->fullName.'.');
    }
}
