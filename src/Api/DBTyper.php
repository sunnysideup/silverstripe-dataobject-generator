<?php

namespace Sunnysideup\BuildDataObject\API;

use SilverStripe\Core\ClassInfo;

class DBTyper
{
    private $fullQualClassName;

// toClass():
//   'SilverStripe\ORM\FieldType\DBInt'
//   'SilverStripe\ORM\FieldType\DBVarchar'
//   'SilverStripe\ORM\FieldType\DBEnum'
//   'SilverStripe\Assets\Storage\DBFile'
//   'SilverStripe\Assets\Image'

// getNameForDropdownField():
//   $fullQualClassName => result
//   'SilverStripe\ORM\FieldType\DBInt' => 'Int'
//   'SilverStripe\ORM\FieldType\DBVarchar' => 'Varchar'
//   'SilverStripe\ORM\FieldType\DBEnum' => 'Enum'
//   'SilverStripe\Assets\Storage\DBFile' 'DBFile'
//   'SilverStripe\Assets\Image' => 'Image'

// getNameForDataObject():
//   $fullQualClassName => result
//   'SilverStripe\ORM\FieldType\DBInt' => 'Int'
//   'SilverStripe\ORM\FieldType\DBVarchar' => 'Varchar'
//   'SilverStripe\ORM\FieldType\DBEnum' => 'Enum(array("foo", "bar", "baz"))'
//   'SilverStripe\Assets\Storage\DBFile' 'DBFile'
//   'SilverStripe\Assets\Image' => SilverStripe\Assets\Image::class
//     or
//   'SilverStripe\Assets\Image' => 'SilverStripe\\Assets\\Image'


// $obj::class returns the fully qualified class name

// class: 'SilverStripe\ORM\FieldType\DBEnum'
// 'Enum'
// 'Enum("foo", "bar", "baz")'
// 'Enum(array("foo", "bar", "baz"))'

// class: 'SilverStripe\Assets\Storage\DBFile'
// 'DBFile'
// 'File'								<== WRONG!!

// class: 'SilverStripe\Assets\Image'
// 'SilverStripe\\Assets\\Image'
// 'SilverStripe\Assets\Image'			<== may be wrong
// 'Image'								<== WRONG!!


    private function __construct($fullQualClassName)
    {
        $this->fullQualClassName = $fullQualClassName;
    }

    //const PREFIX_ASSET = 'SilverStripe\\Assets\\'; // we need to keep fully qualified name of this one

    const PREFIXES_OF_KNOWN_DB_TYPES = array(
        'SilverStripe\\ORM\\FieldType\\DB', // trim 'DB' from built-in types (e.g. 'DBInt' => 'Int')
        'SilverStripe\\Assets\\Storage\\' // preserve 'DB' for composite objects (e.g. 'DBFile' => 'DBFile')
    );

    public static function fromClass(string $fullQualClassName) : DBTyper
    {
        return new DBTyper($fullQualClassName);
    }

    public static function fromDataObject(string $dbTypeName) : DBTyper
    {
        // remove everything after '('
        // e.g. 'Varchar(32)' => 'Varchar', 'Enum(array(...))' => 'Enum'
        $trimmedTypeName = trim(preg_replace('/\(.*$/', '', $dbTypeName));
        // look for known classes
        foreach (self::PREFIXES_OF_KNOWN_DB_TYPES as $prefix) {
            // convert to fully qualified name and test it
            $fullyQualifiedName = $prefix.$trimmedTypeName;
            if (class_exists($fullyQualifiedName)) {
                return new DBTyper($fullyQualifiedName);
            }
        }
        return new DBTyper($dbTypeName);
    }

    public static function fromDropdown(string $ddName) : DBTyper
    {
        return self::fromDataObject($ddName);
    }

    public function toClass() : string
    {
        return $this->fullQualClassName;
    }

    public function toDropdown() : string
    {
        $dbFieldName = $this->dbFieldNameFromKnownClassName();
        if ($dbFieldName) {
            return $dbFieldName;
        } elseif (class_exists($this->fullQualClassName)) {
            return ClassInfo::shortName($this->fullQualClassName);
        } else {
            return $this->fullQualClassName;
        }
    }

    public function toDataObject() : string
    {
        $dbFieldName = $this->dbFieldNameFromKnownClassName();
        if ($dbFieldName) {
            // add placeholder for Enum and MultiEnum
            if ($dbFieldName === 'Enum' || $dbFieldName === 'MultiEnum') {
                return $dbFieldName.'(array("foo", "bar", "baz"))';
            } else {
                return $dbFieldName;
            }
        } else {
            return $this->fullQualClassName;
        }
    }

    private function dbFieldNameFromKnownClassName()
    {
        foreach (self::PREFIXES_OF_KNOWN_DB_TYPES as $prefix) {
            if (strpos($this->fullQualClassName, $prefix) === 0) {
                return substr($this->fullQualClassName, strlen($prefix), strlen($this->fullQualClassName));
            }
        }
        return null;
    }
}
