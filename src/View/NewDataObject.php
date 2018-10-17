<?php

namespace Sunnysideup\BuildDataObject\View;

use SilverStripe\View\ArrayData;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\ClassInfo;
use Sunnysideup\BuildDataObject\View\ClassObject;

class NewDataObject extends ArrayData
{

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function CompileDataForRendering()
    {
        $this->convertLongToShortClassName($this);
    }

    public function setBaseClassName($baseClass)
    {
        return $this->myBaseClass = $baseClass;
    }

    public function getBaseClassName()
    {
        return $this->myBaseClass;
    }

    public function getShortBaseClassName()
    {
        return ClassInfo::shortName($this->myBaseClass);
    }


    public function getClassNameForObject()
    {
        if (isset($this->Name) && isset($this->NameSpace)) {
            return trim($this->NameSpace, '\\') . '\\' . trim($this->Name, '\\');
        } else {
            return 'self::class';
        }
    }

    public function getShortClassNameForObject()
    {
        return trim($this->Name, '\\');
    }





    #################################
    # List to use for the use statements ...
    #################################

    protected function convertLongToShortClassName($arrayData)
    {
        if($arrayData instanceof ArrayData) {
            $map = $arrayData->toMap();
            foreach($map as $key => $value) {
                if(is_string($value)) {
                    if($key !== 'NameSpace') {
                        if($classObject = $this->addToListToUse($value)) {
                            $arrayData->setField($key, $classObject);
                        }
                    }
                } elseif($value instanceof ArrayList){
                    foreach($value as $innerArrayData) {
                        $this->convertLongToShortClassName($innerArrayData);
                    }
                }
            }
        } else {
            user_error('Data provided should be an array data '.print_r($arrayData, 1));
        }
    }

    protected $listForUseStatements = [
        'SilverStripe\ORM\DataObject' => true,
        'SilverStripe\Security\Permission' => true,
        'SilverStripe\ORM\FieldType\DBField' => true
    ];


    protected function addToListToUse($suspectedClassName)
    {
        $object = ClassObject::create($suspectedClassName);
        if($object->isClass()) {
            $this->listForUseStatements[$object->getFullName()] = true;

            return $object;
        }
    }

    public function getFinalListToUse()
    {
        $al = ArrayList::create();
        $array = $this->listForUseStatements;
        foreach($array as $fullClassName => $trueIsTrue) {
            //just in case ...
            $fullClassName = str_replace('\\\\', '\\', $fullClassName);
            $al->push(
                ArrayData::create(['FullClassName' => $fullClassName])
            );
        }
        $al = $al->Sort('FullClassName');

        return $al;
    }


}
