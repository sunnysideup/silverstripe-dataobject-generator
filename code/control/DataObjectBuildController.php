<?php


namespace SunnySideUp\BuildDataObject;

class DataObjectBuildController extends BuildController
{
    protected $myBaseClass = 'DataObject';

    protected function primaryThingsToBuild()
    {
        return [
            ['singular_name',     'text',          ''],
            ['plural_name',       'text',          ''],
            ['db',                'array',         'dbFields'],
            ['belongs_to',        'array',         'possibleRelations'],
            ['has_one',           'array',         'possibleRelations'],
            ['has_many',          'array',         'possibleRelations'],
            ['many_many',         'array',         'possibleRelations'],
            ['belongs_many_many', 'array',         'possibleRelations']
        ];
    }

    protected function secondaryThingsToBuild()
    {
        return [
            ['defaults',            'myDbFields',                                'text'],
            ['default_sort',        'MyDbFieldsWithDefaults',                    'sortOptions'],
            ['indexes',             'myDbFieldsAndIndexes',                      'indexOptions'],
            ['required_fields',     'myDbFieldsAndHasOnesWithIDs',               'requiredOptions'],
            ['field_labels',        'myAllFieldsWithoutBelongs',                 'text'],
            ['field_labels_right',  'myAllFieldsWithoutBelongs',                 'text'],
            ['searchable_fields',   'myDbFieldsAndHasOnesWithIDs',               'possibleSearchFilters'],
            ['summary_fields',      'myDbFieldsFancyWithBelongsWithBasicFields', 'text'],
            ['casting',             'text',                                      'dbFields'],
            ['canCreate',           'canOptions',                                'ignore'],
            ['canView',             'canOptions',                                'ignore'],
            ['canEdit',             'canOptions',                                'ignore'],
            ['canDelete',           'canOptions',                                'ignore']
        ];
    }
}
