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
            \SilverStripe\ORM\FieldType\DBInt::class,
            \SilverStripe\ORM\FieldType\DBVarchar::class,
            \SilverStripe\ORM\FieldType\DBEnum::class,
            \SilverStripe\Assets\Storage\DBFile::class,
            \SilverStripe\Assets\Image::class,
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
            \SilverStripe\ORM\FieldType\DBInt::class => 'Int',
            \SilverStripe\ORM\FieldType\DBVarchar::class => 'Varchar',
            \SilverStripe\ORM\FieldType\DBEnum::class => 'Enum',
            \SilverStripe\ORM\DataObject::class => 'DataObject',
            \SilverStripe\Assets\Storage\DBFile::class => 'DBFile',
            \SilverStripe\Assets\Image::class => 'Image',
        ];
        foreach ($expected_classname_dropdown as $input => $expected) {
            $result = DBTypeConverter::fromClass($input)->toDropdown();
            $this->assertSame($expected, $result, $input);
        }
    }

    public function testClassToDataObject()
    {
        $expected_classname_dbfield = [
            \SilverStripe\ORM\FieldType\DBInt::class => 'Int',
            \SilverStripe\ORM\FieldType\DBVarchar::class => 'Varchar',
            \SilverStripe\ORM\FieldType\DBEnum::class => 'Enum("foo,bar,foo2,bar2", "bar2")',
            \SilverStripe\ORM\DataObject::class => 'DataObject',
            \SilverStripe\Assets\Storage\DBFile::class => 'DBFile',
            \SilverStripe\Assets\Image::class => 'SilverStripe\\\\Assets\\\\Image',
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
            'Int' => \SilverStripe\ORM\FieldType\DBInt::class,
            'Varchar' => \SilverStripe\ORM\FieldType\DBVarchar::class,
            'Enum' => \SilverStripe\ORM\FieldType\DBEnum::class,
            'DBFile' => \SilverStripe\Assets\Storage\DBFile::class,
            'DataObject' => \SilverStripe\ORM\DataObject::class,
            'Image' => \SilverStripe\Assets\Image::class,
        ];
        foreach ($expected_dbfield_classname as $input => $expected) {
            $result = DBTypeConverter::fromDropdown($input)->toClass();
            $this->assertSame($result, $expected);
        }
    }

    public function testDataObjectToClass()
    {
        $expected_dbfield_classname = [
            'Int' => \SilverStripe\ORM\FieldType\DBInt::class,
            'Varchar' => \SilverStripe\ORM\FieldType\DBVarchar::class,
            'Varchar(256)' => \SilverStripe\ORM\FieldType\DBVarchar::class,
            'Enum' => \SilverStripe\ORM\FieldType\DBEnum::class,
            'Enum("foo,bar,foo2,bar2", "bar2")' => \SilverStripe\ORM\FieldType\DBEnum::class,
            'DataObject' => \SilverStripe\ORM\DataObject::class,
            'DBFile' => \SilverStripe\Assets\Storage\DBFile::class,
            \SilverStripe\Assets\Image::class => \SilverStripe\Assets\Image::class,
        ];
        foreach ($expected_dbfield_classname as $input => $expected) {
            $result = DBTypeConverter::fromDataObject($input)->toClass();
            $this->assertSame($result, $expected);
        }
    }
}
