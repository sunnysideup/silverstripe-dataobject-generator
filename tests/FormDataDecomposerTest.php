<?php

use SilverStripe\Dev\SapphireTest;
use Sunnysideup\BuildDataObject\Api\FormData\CanFormData;
use Sunnysideup\BuildDataObject\Api\FormData\FormDataDecomposer;
use Sunnysideup\BuildDataObject\Api\FormData\KeyValueArrayFormData;
use Sunnysideup\BuildDataObject\Api\FormData\PlainFormData;

/**
 * @internal
 * @coversNothing
 */
class FormDataDecomposerTest extends SapphireTest
{
    public function testEmptyFields()
    {
        $input = [
            'Null' => null,
            'False?' => false,
            'False!' => false,
            'Zero' => 0,
            'EmptyString' => '',
        ];
        $decomposer = new FormDataDecomposer($input);
        $decomposer->getResult($output);

        $this->assertCount(0, $output);
    }

    public function testPlainFields()
    {
        $input = [
            'Name' => 'File',
            'Extends' => \SilverStripe\ORM\DataObject::class,
            'singular_name' => 'File',
            'plural_name' => 'Files',
        ];
        $decomposer = new FormDataDecomposer($input);
        $decomposer->getResult($output);

        $this->assertCount(4, $output);
        foreach (array_keys($input) as $key) {
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
        foreach (array_keys($input) as $key) {
            $this->assertArrayHasKey($key, $output);
            $value = $output[$key];
            $this->assertInstanceOf(CanFormData::class, $value);
        }
    }

    public function testKeyValueFields()
    {
        $char = FormDataDecomposer::EXP_CHAR;
        $key = FormDataDecomposer::KEY_IDENTIFIER;
        $val = FormDataDecomposer::VALUE_IDENTIFIER;
        $input = [
            'db' . $char . $key . $char . '1' => 'Name',
            'db' . $char . $val . $char . '1' => 'Varchar',
            'db' . $char . $key . $char . '2' => 'File',
            'db' . $char . $val . $char . '2' => 'DBFile',
            'db' . $char . $key . $char . '3' => 'ShowInSearch',
            'db' . $char . $val . $char . '3' => 'Boolean',
            'db' . $char . $key . $char . '4' => 'CanViewType',
            'db' . $char . $val . $char . '4' => 'Enum',
        ];
        $decomposer = new FormDataDecomposer($input);
        $decomposer->getResult($output);

        $this->assertCount(1, $output);
        $this->assertArrayHasKey('db', $output);
        $element = $output['db'];
        $this->assertInstanceOf(KeyValueArrayFormData::class, $element);
        $element->getArray($array);
        $this->assertCount(4, $array);
        $this->assertSame($array[1][KeyValueArrayFormData::KEY], 'Name');
        $this->assertSame($array[1][KeyValueArrayFormData::VALUE], 'Varchar');
        $this->assertSame($array[2][KeyValueArrayFormData::KEY], 'File');
        $this->assertSame($array[2][KeyValueArrayFormData::VALUE], 'DBFile');
        $this->assertSame($array[3][KeyValueArrayFormData::KEY], 'ShowInSearch');
        $this->assertSame($array[3][KeyValueArrayFormData::VALUE], 'Boolean');
        $this->assertSame($array[4][KeyValueArrayFormData::KEY], 'CanViewType');
        $this->assertSame($array[4][KeyValueArrayFormData::VALUE], 'Enum');
    }
}
