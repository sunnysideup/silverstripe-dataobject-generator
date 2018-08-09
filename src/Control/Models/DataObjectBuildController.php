<?php


namespace Sunnysideup\BuildDataObject\Control\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\DropdownField;

use Sunnysideup\BuildDataObject\Control\BuildController;

class DataObjectBuildController extends BuildController
{
    protected $myBaseClass = DataObject::class;

    protected function additionalPrimaryFields()
    {
        return [
            HeaderField::create('ModelAdminUsedHeader', 'Model Admin Used'),
            DropdownField::create(
                ModelAdmin::class,
                '',
                $this->myAPI()->modelAdminOptions()
            )->setEmptyString('--- Please Select ---')
        ];
    }


    protected function primaryThingsToBuild()
    {
        return $this->addKeysToThingsToBuild([
            ['singular_name',       'text',                                      '',                       false],
            ['plural_name',         'text',                                      '',                       false],
            ['db',                  'text',                                     'dbFields',                true],
            ['belongs_to',          'text',                                     'possibleRelations',       true],
            ['has_one',             'text',                                     'possibleRelations',       true],
            ['has_many',            'text',                                     'possibleRelations',       true],
            ['many_many',           'text',                                     'possibleRelations',       true],
            ['belongs_many_many',   'text',                                     'possibleRelations',       true]
        ]);
    }

    protected function secondaryThingsToBuild()
    {
        return $this->addKeysToThingsToBuild([
            ['defaults',            'myDbFields',                                'text',                   true],
            ['default_sort',        'MyDbFieldsWithDefaults',                    'sortOptions',            true],
            ['indexes',             'myDbFieldsAndIndexes',                      'indexOptions',           true],
            ['required_fields',     'myDbFieldsAndHasOnesWithIDs',               'requiredOptions',        true],
            ['field_labels',        'myAllFieldsWithoutBelongs',                 'text',                   true],
            ['field_labels_right',  'myAllFieldsWithoutBelongs',                 'text',                   true],
            ['searchable_fields',   'myDbFieldsAndHasOnesWithIDs',               'possibleSearchFilters',  true],
            ['summary_fields',      'myDbFieldsFancyWithBelongsWithBasicFields', 'text',                   true],
            ['casting',             'text',                                      'dbFields',               true],
            ['canCreate',           'canOptions',                                '',                       false],
            ['canView',             'canOptions',                                '',                       false],
            ['canEdit',             'canOptions',                                '',                       false],
            ['canDelete',           'canOptions',                                '',                       false]
        ]);
    }
}