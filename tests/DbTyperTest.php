<?php

use SilverStripe\Dev\SapphireTest;
use Sunnysideup\BuildDataObject\API\DBTyper;

class DBTyperTester extends SapphireTest
{
    public function testClassToClass()
    {
        $expected_classname = [
            'SilverStripe\\ORM\\FieldType\\DBInt',
            'SilverStripe\\ORM\\FieldType\\DBVarchar',
            'SilverStripe\\ORM\\FieldType\\DBEnum',
            'SilverStripe\\Assets\\Storage\\DBFile',
            'SilverStripe\\Assets\\Image' => 'Image'
        ];
        // in & out are the same
        foreach ($expected_classname as $inout) {
            $result = DBTyper::fromClass($inout)->toClass();
            $this->assertSame($inout, $result, $inout);
        }
    }

    public function testClassToDropdown()
    {
        $expected_classname_dropdown = array(
            'SilverStripe\\ORM\\FieldType\\DBInt' => 'Int',
            'SilverStripe\\ORM\\FieldType\\DBVarchar' => 'Varchar',
            'SilverStripe\\ORM\\FieldType\\DBEnum' => 'Enum',
            'SilverStripe\\Assets\\Storage\\DBFile' => 'DBFile',
            'SilverStripe\\Assets\\Image' => 'Image'
        );
        foreach ($expected_classname_dropdown as $input => $expected) {
            $result = DBTyper::fromClass($input)->toDropdown();
            $this->assertSame($expected, $result, $input);
        }
    }

    public function testClassToDataObject()
    {
        $expected_classname_dbfield = array(
            'SilverStripe\\ORM\\FieldType\\DBInt' => 'Int',
            'SilverStripe\\ORM\\FieldType\\DBVarchar' => 'Varchar',
            'SilverStripe\\ORM\\FieldType\\DBEnum' => 'Enum(array("foo", "bar", "baz"))',
            'SilverStripe\\Assets\\Storage\\DBFile' => 'DBFile',
            'SilverStripe\\Assets\\Image' => 'SilverStripe\\Assets\\Image'
        );
        foreach ($expected_classname_dbfield as $input => $expected) {
            $result = DBTyper::fromClass($input)->toDataObject();
            $this->assertSame($expected, $result);
        }
    }

    public function testDropdownToDataObject()
    {
        $expected_dbfield_classname = array(
            'Int' => 'Int',
            'Varchar' => 'Varchar',
            'Enum' => 'Enum(array("foo", "bar", "baz"))',
            'DBFile' => 'DBFile',
            'Image' => 'Image'
        );
        foreach ($expected_dbfield_classname as $input => $expected) {
            $result = DBTyper::fromDropdown($input)->toDataObject();
            $this->assertSame($result, $expected);
        }
    }

    public function testDataObjectToClass()
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
            $result = DBTyper::fromDataObject($input)->toClass();
            $this->assertSame($result, $expected);
        }
    }
}
