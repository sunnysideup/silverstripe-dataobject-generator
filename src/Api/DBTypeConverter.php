<?php

namespace Sunnysideup\BuildDataObject\Api;

use SilverStripe\Core\ClassInfo;

class DBTypeConverter
{
    /**
     * @var string[]
     */
    public const PREFIXES_OF_KNOWN_DB_TYPES = [
        'SilverStripe\\ORM\\FieldType\\DB', // trim 'DB' from built-in types (e.g. 'DBInt' => 'Int')
        // 'SilverStripe\\ORM\\',              // for DataObject
        'SilverStripe\\Assets\\Storage\\',   // preserve 'DB' for composite objects (e.g. 'DBFile' => 'DBFile')
    ];

    /**
     * @var string[]
     */
    public const PREFIXES_OF_KNOWN_COMPOSITED_TYPES = [
        'SilverStripe\\Assets\\',            // keep fully qualified name
    ];

    private $fullQualClassName;

    private function __construct($fullQualClassName)
    {
        $this->fullQualClassName = $fullQualClassName;
    }

    public static function fromClass(string $fullQualClassName): DBTypeConverter
    {
        return new DBTypeConverter($fullQualClassName);
    }

    public static function fromDataObject(string $dbTypeName): DBTypeConverter
    {
        $inst = self::fromAny($dbTypeName, self::PREFIXES_OF_KNOWN_DB_TYPES);

        return $inst ?: new DBTypeConverter($dbTypeName);
    }

    public static function fromDropdown(string $ddName): DBTypeConverter
    {
        $inst = self::fromAny($ddName, self::PREFIXES_OF_KNOWN_DB_TYPES);
        if (! $inst) {
            $inst = self::fromAny($ddName, self::PREFIXES_OF_KNOWN_COMPOSITED_TYPES);
        }

        return $inst ?: new DBTypeConverter($ddName);
    }

    public function toClass(): string
    {
        return $this->fullQualClassName;
    }

    public function toDropdown(): string
    {
        $dbFieldName = $this->dbFieldNameFromKnownClassName();
        if ($dbFieldName) {
            return $dbFieldName;
        }

        if (class_exists($this->fullQualClassName)) {
            return ClassInfo::shortName($this->fullQualClassName);
        }

        return $this->fullQualClassName;
    }

    public function toDataObject(): string
    {
        $dbFieldName = $this->dbFieldNameFromKnownClassName();
        if ($dbFieldName) {
            // add placeholder for Enum and MultiEnum
            if ('Enum' === $dbFieldName || 'MultiEnum' === $dbFieldName) {
                $dbFieldName .= '(array("foo", "bar", "baz"))';
            }
        } else {
            $dbFieldName = $this->fullQualClassName;
        }

        return str_replace('\\', '\\\\', $dbFieldName);
    }

    private static function fromAny(string $dbTypeName, array $prefixes)
    {
        // remove everything after '('
        // e.g. 'Varchar' => 'Varchar(32)'
        $trimmedTypeName = trim(preg_replace('#\(.*$#', '', (string) $dbTypeName));
        // look for known classes
        foreach ($prefixes as $prefix) {
            // convert to fully qualified name and test it
            $fullyQualifiedName = $prefix . $trimmedTypeName;
            if (class_exists($fullyQualifiedName)) {
                return new DBTypeConverter($fullyQualifiedName);
            }
        }

        return null;
    }

    private function dbFieldNameFromKnownClassName()
    {
        foreach (self::PREFIXES_OF_KNOWN_DB_TYPES as $prefix) {
            if (0 === strpos($this->fullQualClassName, $prefix)) {
                return substr((string) $this->fullQualClassName, strlen($prefix), strlen($this->fullQualClassName));
            }
        }

        return null;
    }
}
