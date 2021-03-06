<?php

use SilverStripe\Dev\SapphireTest;
use Sunnysideup\BuildDataObject\Api\DBTypeConverter;

/**
 * @internal
 * @coversNothing
 */
class DBTypeConverterTest extends SapphireTest
{
    public function testClassToClass()
    {
        $expected_classname = [
            'SilverStripe\\ORM\\FieldType\\DBInt',
            'SilverStripe\\ORM\\FieldType\\DBVarchar',
            'SilverStripe\\ORM\\FieldType\\DBEnum',
            'SilverStripe\\Assets\\Storage\\DBFile',
            'SilverStripe\\Assets\\Image',
        ];
        // in & out are the same
        foreach ($expected_classname as $inout) {
            $result = DBTypeConverter::fromClass($inout)->toClass();
            $this->assertSame($inout, $result, $inout);
        }
    }

    public function testClassToDropdown()
    {
        $expected_classname_dropdown = [
            'SilverStripe\\ORM\\FieldType\\DBInt' => 'Int',
            'SilverStripe\\ORM\\FieldType\\DBVarchar' => 'Varchar',
            'SilverStripe\\ORM\\FieldType\\DBEnum' => 'Enum',
            'SilverStripe\\ORM\\DataObject' => 'DataObject',
            'SilverStripe\\Assets\\Storage\\DBFile' => 'DBFile',
            'SilverStripe\\Assets\\Image' => 'Image',
        ];
        foreach ($expected_classname_dropdown as $input => $expected) {
            $result = DBTypeConverter::fromClass($input)->toDropdown();
            $this->assertSame($expected, $result, $input);
        }
    }

    public function testClassToDataObject()
    {
        $expected_classname_dbfield = [
            'SilverStripe\\ORM\\FieldType\\DBInt' => 'Int',
            'SilverStripe\\ORM\\FieldType\\DBVarchar' => 'Varchar',
            'SilverStripe\\ORM\\FieldType\\DBEnum' => 'Enum("foo,bar,foo2,bar2", "bar2")',
            'SilverStripe\\ORM\\DataObject' => 'DataObject',
            'SilverStripe\\Assets\\Storage\\DBFile' => 'DBFile',
            'SilverStripe\\Assets\\Image' => 'SilverStripe\\\\Assets\\\\Image',
        ];
        foreach ($expected_classname_dbfield as $input => $expected) {
            $result = DBTypeConverter::fromClass($input)->toDataObject();
            $this->assertSame($expected, $result);
        }
    }

    public function testDropdownToDataObject()
    {
        $expected_dbfield_classname = [
            'Int' => 'Int',
            'Varchar' => 'Varchar',
            'Enum' => 'Enum("foo,bar,foo2,bar2", "bar2")',
            'DBFile' => 'DBFile',
            'DataObject' => 'DataObject',
            'Image' => 'SilverStripe\\\\Assets\\\\Image',
        ];
        foreach ($expected_dbfield_classname as $input => $expected) {
            $result = DBTypeConverter::fromDropdown($input)->toDataObject();
            $this->assertSame($result, $expected);
        }
    }

    public function testDropdownToClass()
    {
        $expected_dbfield_classname = [
            'Int' => 'SilverStripe\\ORM\\FieldType\\DBInt',
            'Varchar' => 'SilverStripe\\ORM\\FieldType\\DBVarchar',
            'Enum' => 'SilverStripe\\ORM\\FieldType\\DBEnum',
            'DBFile' => 'SilverStripe\\Assets\\Storage\\DBFile',
            'DataObject' => 'SilverStripe\\ORM\\DataObject',
            'Image' => 'SilverStripe\\Assets\\Image',
        ];
        foreach ($expected_dbfield_classname as $input => $expected) {
            $result = DBTypeConverter::fromDropdown($input)->toClass();
            $this->assertSame($result, $expected);
        }
    }

    public function testDataObjectToClass()
    {
        $expected_dbfield_classname = [
            'Int' => 'SilverStripe\\ORM\\FieldType\\DBInt',
            'Varchar' => 'SilverStripe\\ORM\\FieldType\\DBVarchar',
            'Varchar(256)' => 'SilverStripe\\ORM\\FieldType\\DBVarchar',
            'Enum' => 'SilverStripe\\ORM\\FieldType\\DBEnum',
            'Enum("foo,bar,foo2,bar2", "bar2")' => 'SilverStripe\\ORM\\FieldType\\DBEnum',
            'DataObject' => 'SilverStripe\\ORM\\DataObject',
            'DBFile' => 'SilverStripe\\Assets\\Storage\\DBFile',
            'SilverStripe\\Assets\\Image' => 'SilverStripe\\Assets\\Image',
        ];
        foreach ($expected_dbfield_classname as $input => $expected) {
            $result = DBTypeConverter::fromDataObject($input)->toClass();
            $this->assertSame($result, $expected);
        }
    }
}
