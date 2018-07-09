<?php

use SilverStripe\Dev\SapphireTest;
use Sunnysideup\BuildDataObject\DBTypeName;

class DBTypeNameTester extends SapphireTest
{
    public function testClassNameToClassName()
	{
		$expected_classname = [
			'SilverStripe\\ORM\\FieldType\\DBInt',
			'SilverStripe\\ORM\\FieldType\\DBVarchar',
			'SilverStripe\\ORM\\FieldType\\DBEnum',
			'SilverStripe\\Assets\\Storage\\DBFile',
			'SilverStripe\\Assets\\Image' => 'Image'
		];
		foreach ($expected_classname as $inout) {
			$type = DBTypeName::createFromClassName($inout);
			$result = $type->getFullClassName();
			$this->assertSame($inout, $result);
		}
	}

	public function testClassNameToDropdownField()
	{
		$expected_classname_dropdown = array(
			'SilverStripe\\ORM\\FieldType\\DBInt' => 'Int',
			'SilverStripe\\ORM\\FieldType\\DBVarchar' => 'Varchar',
			'SilverStripe\\ORM\\FieldType\\DBEnum' => 'Enum',
			'SilverStripe\\Assets\\Storage\\DBFile' => 'DBFile',
			'SilverStripe\\Assets\\Image' => 'Image'
		);
		foreach ($expected_classname_dropdown as $input => $expected) {
			$type = DBTypeName::createFromClassName($input);
			$result = $type->getNameForDropdownField();
			$this->assertSame($expected, $result);
		}
	}

	public function testClassNameToDataObject()
	{
		$expected_classname_dbfield = array(
			'SilverStripe\\ORM\\FieldType\\DBInt' => 'Int',
			'SilverStripe\\ORM\\FieldType\\DBVarchar' => 'Varchar',
			'SilverStripe\\ORM\\FieldType\\DBEnum' => 'Enum(array("foo", "bar", "baz"))',
			'SilverStripe\\Assets\\Storage\\DBFile' => 'DBFile',
			'SilverStripe\\Assets\\Image' => 'SilverStripe\\Assets\\Image'
		);
		foreach ($expected_classname_dbfield as $input => $expected) {
			$type = DBTypeName::createFromClassName($input);
			$result = $type->getNameForDataObject();
            $this->assertSame($expected, $result);
		}
	}

	public function testDataObjectToClassName()
	{
		$expected_dbfield_classname = array(
			'Int' => 'SilverStripe\\ORM\\FieldType\\DBInt',
			'Varchar' => 'SilverStripe\\ORM\\FieldType\\DBVarchar',
			'Varchar(256)' => 'SilverStripe\\ORM\\FieldType\\DBVarchar',
			'Enum' => 'SilverStripe\\ORM\\FieldType\\DBEnum',
			'Enum(array(\'baa\', \'boo\'))' => 'SilverStripe\\ORM\\FieldType\\DBEnum',
			'DBFile' => 'SilverStripe\\Assets\\Storage\\DBFile',
			'SilverStripe\\Assets\\Image' => 'SilverStripe\\Assets\\Image'
		);
		foreach ($expected_dbfield_classname as $input => $expected) {
			$type = DBTypeName::createFromDataObjectFieldName($input);
			$result = $type->getFullClassName();
			$this->assertSame($result, $expected);
		}
	}
}

?>
