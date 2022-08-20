<?php

namespace Sunnysideup\BuildDataObject\Control\Models;

use Sunnysideup\BuildDataObject\Control\BuildController;

class PageBuildController extends BuildController
{
    protected $myBaseClass = \Page::class;

    protected function primaryThingsToBuild()
    {
        return $this->addKeysToThingsToBuild([
            ['singular_name',       'text',                                     '',                        false],
            ['plural_name',         'text',                                     '',                        false],
            ['description',         'text',                                     '',                        false],
            ['default_classname',   'text',                                     '',                        false],
            ['api_access',          'TrueOrFalseList',                          '',                        false],
            ['db',                  'text',                                     'dbFields',                true],
            ['belongs_to',          'text',                                     'possibleRelations',       true],
            ['has_one',             'text',                                     'possibleRelations',       true],
            ['has_many',            'text',                                     'possibleRelations',       true],
            ['many_many',           'text',                                     'possibleRelations',       true],
            ['belongs_many_many',   'text',                                     'possibleRelations',       true],
            ['casting',             'text',                                      'dbFields',               true],
        ]);
    }

    protected function secondaryThingsToBuild()
    {
        return $this->addKeysToThingsToBuild([
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
            ['owns',                'myPossibleRelations',                       '',                       true],
            ['owned_by',            'myPossibleRelations',                      '',                       true],
            ['cascade_deletes',     'myPossibleRelations',                       '',                       true],
            ['cascade_duplicates',  'myPossibleRelations',                       '',                       true],
            ['field_labels',        'myAllFieldsWithoutBelongs',                 'text',                   true],
            ['field_labels_right',  'myAllFieldsWithoutBelongs',                 'text',                   true],
            ['searchable_fields',   'myDbFieldsAndHasOnesWithIDs',               'possibleSearchFilters',  true],
            ['summary_fields',      'myDbFieldsFancyWithBelongsWithBasicFields', 'text',                   true],
            ['canCreate',           'canOptions',                                '',                       false],
            ['canView',             'canOptions',                                '',                       false],
            ['canEdit',             'canOptions',                                '',                       false],
            ['canDelete',           'canOptions',                                '',                       false],
        ]);
    }
}
