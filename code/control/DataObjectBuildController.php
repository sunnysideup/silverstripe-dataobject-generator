<?php


namespace SunnySideUp\BuildDataObject;

class DataObjectBuildController extends BuildController
{
    protected $myBaseClass = 'DataObject';

    protected function additionalPrimaryFields()
    {
        return [
            \HeaderField::create('Model Admin Used'),
            \DropdownField::create(
                'ModelAdmin',
                '',
                $this->prependNullOption($this->myAPI()->modelAdminOptions())
            )
        ];

    }


        protected function primaryThingsToBuild()
        {
            return [
                ['singular_name',       'text',                                      '',                       false],
                ['plural_name',         'text',                                      '',                       false],
                ['db',                  'text',                                     'dbFields',                true],
                ['belongs_to',          'text',                                     'possibleRelations',       true],
                ['has_one',             'text',                                     'possibleRelations',       true],
                ['has_many',            'text',                                     'possibleRelations',       true],
                ['many_many',           'text',                                     'possibleRelations',       true],
                ['belongs_many_many',   'text',                                     'possibleRelations',       true]
            ];
        }

        protected function secondaryThingsToBuild()
        {
            return [
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
            ];
        }
}
