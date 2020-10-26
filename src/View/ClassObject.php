<?php

namespace Sunnysideup\BuildDataObject\View;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\View\ViewableData;

class ClassObject Extends ViewableData
{
    // use Configurable;
    // use Extensible;
    // use Injectable;

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

    public function getShortName()
    {
        $shortName = '';
        if (class_exists($this->fullName)) {
            $shortName = ClassInfo::shortName($this->fullName);
        } else {
            $array = explode('\\', $this->fullName);
            if (count($array) > 1) {
                $shortName = end($array);
            }
        }
        if ($shortName) {
            return $shortName;
        }
        return $this->fullName;
    }

    public function isClass()
    {
        if (class_exists($this->fullName)) {
            return true;
        }
        if (class_exists('\\' . $this->fullName)) {
            return true;
        }
        $array = explode('\\', $this->fullName);
        if (count($array) > 1) {
            return true;
        }
    }

    public function forTemplate()
    {
        return $this->fullName;
    }
}
