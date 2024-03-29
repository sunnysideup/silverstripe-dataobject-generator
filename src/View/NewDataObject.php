<?php

namespace Sunnysideup\BuildDataObject\View;

use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class NewDataObject extends ArrayData
{
    protected $listForUseStatements = [
        \SilverStripe\ORM\DataObject::class => true,
        \SilverStripe\Security\Permission::class => true,
        \SilverStripe\ORM\FieldType\DBField::class => true,
    ];

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
        if (property_exists($this, 'Name') && null !== $this->Name && (property_exists($this, 'NameSpace') && null !== $this->NameSpace)) {
            return trim($this->NameSpace, '\\') . '\\' . trim($this->Name, '\\');
        }

        return 'self::class';
    }

    public function getShortClassNameForObject()
    {
        return trim($this->Name, '\\');
    }

    public function getFinalListToUse()
    {
        $al = ArrayList::create();
        $array = $this->listForUseStatements;
        foreach (array_keys($array) as $fullClassName) {
            //just in case ...
            $fullClassName = str_replace('\\\\', '\\', (string) $fullClassName);
            $al->push(
                ArrayData::create(['FullClassName' => $fullClassName])
            );
        }

        return $al->Sort(['FullClassName' => 'ASC']);
    }

    //################################
    // List to use for the use statements ...
    //################################

    protected function convertLongToShortClassName($arrayData)
    {
        if ($arrayData instanceof ArrayData) {
            $map = $arrayData->toMap();
            foreach ($map as $key => $value) {
                if (is_string($value)) {
                    if ('NameSpace' !== $key) {
                        $classObject = $this->addToListToUse($value);
                        if ($classObject) {
                            $arrayData->setField($key, $classObject);
                        }
                    }
                } elseif ($value instanceof ArrayList) {
                    foreach ($value as $innerArrayData) {
                        $this->convertLongToShortClassName($innerArrayData);
                    }
                }
            }
        } else {
            user_error('Data provided should be an array data ' . print_r($arrayData, 1));
        }
    }

    protected function addToListToUse($suspectedClassName)
    {
        $object = ClassObject::create($suspectedClassName);
        if ($object->isClass()) {
            $this->listForUseStatements[$object->getFullName()] = true;

            return $object;
        }
    }
}
