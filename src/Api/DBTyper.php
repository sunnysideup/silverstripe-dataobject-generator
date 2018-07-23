<?php

namespace Sunnysideup\BuildDataObject\API;

use SilverStripe\Core\ClassInfo;

class DBTyper
{
    private $fullQualClassName;

    private function __construct($fullQualClassName)
    {
        $this->fullQualClassName = $fullQualClassName;
    }

    const PREFIXES_OF_KNOWN_DB_TYPES = array(
        'SilverStripe\\ORM\\FieldType\\DB', // trim 'DB' from built-in types (e.g. 'DBInt' => 'Int')
        'SilverStripe\\ORM\\',              // for DataObject
        'SilverStripe\\Assets\\Storage\\'   // preserve 'DB' for composite objects (e.g. 'DBFile' => 'DBFile')
    );

    const PREFIXES_OF_KNOWN_COMPOSITED_TYPES = array(
        'SilverStripe\\Assets\\'            // keep fully qualified name
    );

    private static function fromAny(string $dbTypeName, array $prefixes)
    {
        // remove everything after '('
        // e.g. 'Varchar(32)' => 'Varchar', 'Enum(array(...))' => 'Enum'
        $trimmedTypeName = trim(preg_replace('/\(.*$/', '', $dbTypeName));
        // look for known classes
        foreach ($prefixes as $prefix) {
            // convert to fully qualified name and test it
            $fullyQualifiedName = $prefix.$trimmedTypeName;
            if (class_exists($fullyQualifiedName)) {
                return new DBTyper($fullyQualifiedName);
            }
        }
        return null;
    }

    public static function fromClass(string $fullQualClassName) : DBTyper
    {
        return new DBTyper($fullQualClassName);
    }

    public static function fromDataObject(string $dbTypeName) : DBTyper
    {
        $inst = self::fromAny($dbTypeName, self::PREFIXES_OF_KNOWN_DB_TYPES);
        return $inst ? $inst : new DBTyper($dbTypeName);
    }

    public static function fromDropdown(string $ddName) : DBTyper
    {
        $inst = self::fromAny($ddName, self::PREFIXES_OF_KNOWN_DB_TYPES);
        if (!$inst) {
            $inst = self::fromAny($ddName, self::PREFIXES_OF_KNOWN_COMPOSITED_TYPES);
        }
        return $inst ? $inst : new DBTyper($ddName);
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
                $dbFieldName .= '(array("foo", "bar", "baz"))';
            }
        } else {
            $dbFieldName = $this->fullQualClassName;
        }
        return str_replace('\\', '\\\\', $dbFieldName);
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
