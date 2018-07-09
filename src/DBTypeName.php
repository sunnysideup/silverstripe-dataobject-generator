<?php

namespace Sunnysideup\BuildDataObject;

use SilverStripe\Core\ClassInfo;

class DBTypeName
{
    // fully qualified class name
    private $fullClass;

// createFromClassName($className):
//  return new DBTypeName(...);

// createFromDataObjectFieldName($dbFieldName):
//  return new DBTypeName(...);

// getFullClassName():
//   'SilverStripe\ORM\FieldType\DBInt'
//   'SilverStripe\ORM\FieldType\DBVarchar'
//   'SilverStripe\ORM\FieldType\DBEnum'
//   'SilverStripe\Assets\Storage\DBFile'
//   'SilverStripe\Assets\Image'

// getNameForDropdownField():
//   $fullClass => result
//   'SilverStripe\ORM\FieldType\DBInt' => 'Int'
//   'SilverStripe\ORM\FieldType\DBVarchar' => 'Varchar'
//   'SilverStripe\ORM\FieldType\DBEnum' => 'Enum'
//   'SilverStripe\Assets\Storage\DBFile' 'DBFile'
//   'SilverStripe\Assets\Image' => 'Image'

// getNameForDataObject():
//   $fullClass => result
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


	private function __construct($fullyQualifiedName)
	{
		$this->fullClass = $fullyQualifiedName;
	}

	public static function createFromClassName($fullyQualifiedName)
	{
		return new DBTypeName($fullyQualifiedName);
	}

	//const PREFIX_ASSET = 'SilverStripe\\Assets\\'; // we need to keep fully qualified name of this one

	const PREFIX_FOR_KNOWN_DB_TYPES = array(
		'SilverStripe\\ORM\\FieldType\\DB', // trim 'DB' from built-in types (e.g. 'DBInt' => 'Int')
		'SilverStripe\\Assets\\Storage\\' // preserve 'DB' for composite objects (e.g. 'DBFile' => 'DBFile')
	);

	public static function createFromDataObjectFieldName($dbFieldName)
	{
		// remove everything after '('
		// e.g. 'Varchar(32)' => 'Varchar', 'Enum(array(...))' => 'Enum'
		$trimmedName = trim(preg_replace('/\(.*$/', '', $dbFieldName));
		// look for known classes
		foreach (self::PREFIX_FOR_KNOWN_DB_TYPES as $prefix) {
			// convert to fully qualified name
			$fullyQualifiedName = $prefix.$trimmedName;
			if (class_exists($fullyQualifiedName)) {
				return new DBTypeName($fullyQualifiedName);
			}
		}
		return new DBTypeName($dbFieldName);
	}

	public function getFullClassName()
	{
		return $this->fullClass;
	}

    public function getNameForDropdownField()
    {
        $dbFieldName = $this->dbFieldNameFromKnownClassName();
        if ($dbFieldName) {
            return $dbFieldName;
        } else if (class_exists($this->fullClass)) {
            return ClassInfo::shortName($this->fullClass);
        } else {
            return $this->fullClass;
        }
    }

    public function getNameForDataObject()
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
    		return $this->fullClass;
        }
    }

    private function dbFieldNameFromKnownClassName()
    {
        foreach (self::PREFIX_FOR_KNOWN_DB_TYPES as $prefix) {
			if (strpos($this->fullClass, $prefix) === 0) {
				return substr($this->fullClass, strlen($prefix), strlen($this->fullClass));
            }
        }
        return null;
    }
}

?>
