<?php


namespace SunnySideUp\BuildDataObject;

class PageBuildController extends BuildController
{
    protected $myBaseClass = 'Page';

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
            ['description',         'text',                                      '',                       false],
            ['can_create',          'TrueOrFalseList',                           '',                       false],
            ['can_be_root',         'TrueOrFalseList',                           '',                       false],
            ['allowed_children',    'allowedChildrenOptions',                    '',                       true],
            ['default_child',       'SiteTreeList',                              '',                       false],
            ['default_parent',      'SiteTreeList',                              '',                       false],
            ['hide_ancestor',       'SiteTreeList',                              '',                       false],
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