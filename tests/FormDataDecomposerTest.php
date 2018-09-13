<?php

use SilverStripe\Dev\SapphireTest;
use Sunnysideup\BuildDataObject\API\FormData\FormDataDecomposer;
use Sunnysideup\BuildDataObject\API\FormData\PlainFormData;
use Sunnysideup\BuildDataObject\API\FormData\CanFormData;
use Sunnysideup\BuildDataObject\API\FormData\KeyValueArrayFormData;

class FormDataDecomposerTest extends SapphireTest
{
    public function testEmptyFields()
    {
        $input = [
            'Null' => null,
            'False?' => false,
            'False!' => FALSE,
            'Zero' => 0,
            'EmptyString' => ''
        ];
        $decomposer = new FormDataDecomposer($input);
        $decomposer->getResult($output);

        $this->assertCount(0, $output);
    }

    public function testPlainFields()
    {
        $input = [
            'Name' => 'File',
            'Extends' => 'SilverStripe\\ORM\\DataObject',
            'singular_name' => 'File',
            'plural_name' => 'Files',
        ];
        $decomposer = new FormDataDecomposer($input);
        $decomposer->getResult($output);

        $this->assertCount(4, $output);
        foreach ($input as $key => $_) {
            $this->assertArrayHasKey($key, $output);
            $value = $output[$key];
            $this->assertInstanceOf(PlainFormData::class, $value);
        }
    }

    public function testCanFields()
    {
        $input = [
            'canCreate' => 'ADMIN',
            'canView' => 'true',
            'canEdit' => 'CMS_ACCESS_AssetAdmin',
            'canDelete' => 'false',
        ];
        $decomposer = new FormDataDecomposer($input);
        $decomposer->getResult($output);

        $this->assertCount(4, $output);
        foreach ($input as $key => $_) {
            $this->assertArrayHasKey($key, $output);
            $value = $output[$key];
            $this->assertInstanceOf(CanFormData::class, $value);
        }
    }

    public function testKeyValueFields()
    {
        $input = [
            'db__KEY__1' => 'Name',
            'db__VALUE__1' => 'Varchar',
            'db__KEY__2' => 'File',
            'db__VALUE__2' => 'DBFile',
            'db__KEY__3' => 'ShowInSearch',
            'db__VALUE__3' => 'Boolean',
            'db__KEY__4' => 'CanViewType',
            'db__VALUE__4' => 'Enum',
        ];
        $decomposer = new FormDataDecomposer($input);
        $decomposer->getResult($output);

        $this->assertCount(1, $output);
        $this->assertArrayHasKey('db', $output);
        $element = $output['db'];
        $this->assertInstanceOf(KeyValueArrayFormData::class, $element);
        $element->getArray($array);
        $this->assertCount(4, $array);
        $this->assertEquals($array[1][KeyValueArrayFormData::KEY], 'Name');
        $this->assertEquals($array[1][KeyValueArrayFormData::VALUE], 'Varchar');
        $this->assertEquals($array[2][KeyValueArrayFormData::KEY], 'File');
        $this->assertEquals($array[2][KeyValueArrayFormData::VALUE], 'DBFile');
        $this->assertEquals($array[3][KeyValueArrayFormData::KEY], 'ShowInSearch');
        $this->assertEquals($array[3][KeyValueArrayFormData::VALUE], 'Boolean');
        $this->assertEquals($array[4][KeyValueArrayFormData::KEY], 'CanViewType');
        $this->assertEquals($array[4][KeyValueArrayFormData::VALUE], 'Enum');
    }
}
