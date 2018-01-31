<?php

namespace SunnySideUp\BuildDataObject;

class API extends \Object
{
    private static $excluded_data_objects = [
        'Image_Cached',
        'PermissionRoleCode',
        'LoginAttempt',
        'MemberPassword',
        'MemberPassword',
        'SiteConfig'
    ];

    private static $excluded_db_fields_types = [
        'DBField',
        'Field',
        'DBLocale',
        'Locale',
        'StringField',
        'CompositeField',
        'PrimaryKey',
        'ForeignKey'
    ];

    private static $additional_db_fields = [
        'ID',
        'Created',
        'LastEdited',
        'ClassName'
    ];

    protected $rootBaseClass = 'DataObject';

    protected $myBaseClass = '';

    protected $_data = null;

    private static $_my_singleton = [];

    public static function inst($myBaseClass = 'DataObject', $data)
    {
        if (! isset(self::$_my_singleton[$myBaseClass])) {
            self::$_my_singleton[$myBaseClass] = \Injector::inst()->get('SunnySideUp\BuildDataObject\API');
        }
        self::$_my_singleton[$myBaseClass]->_data = $data;
        self::$_my_singleton[$myBaseClass]->setBaseClass($myBaseClass);

        return self::$_my_singleton[$myBaseClass];
    }

    public function setBaseClass($myBaseClass)
    {
        $this->myBaseClass = $myBaseClass;
    }


    protected function retrieveDBFields($name)
    {
        $data = $this->_data;
        $ar = [];
        if (isset($data->$name)) {
            foreach ($data->$name as $data) {
                if ($data->Key && $data->Value) {
                    $ar[$data->Key] = $data->Key;
                }
            }
        }

        return $ar;
    }

    protected $_dbfieldCache = [];

    public function DbFields()
    {
        if (count($this->_dbfieldCache) === 0) {
            $list = \ClassInfo::subclassesFor('DbField');
            $newList = [];
            foreach ($list as $class) {
                if (substr($class, 0, 2) == 'DB') {
                    $class = substr($class, 2, strlen($class));
                } elseif (substr($class, 0, 3) == 'SS_') {
                    $class = substr($class, 3, strlen($class));
                } elseif ('Varchar' === $class) {
                    $class = 'Varchar';
                } elseif ('HTMLVarchar' === $class) {
                    $class = 'HTMLVarchar(255)';
                } elseif ('Enum' === $class) {
                    $class = 'Enum(\\\'Foo,Bar\\\', \\\'FOO\\\')';
                } elseif ('MultiEnum' === $class) {
                    $class = 'MultiEnum(\\\'Foo,Bar\\\', \\\'FOO\\\')';
                }
                if (
                    $class == 'DbField' ||
                    is_subclass_of($class, 'TestOnly') ||
                    in_array($class, $this->Config()->get('excluded_db_fields_types'))
                ) {
                    //do nothing
                } else {
                    $newList[$class] = $class;
                }
            }
            $this->_dbfieldCache = $newList;
        }

        return $this->_dbfieldCache;
    }

    public function MyDbFields()
    {
        return $this->retrieveDBFields('db');
    }

    public function MyDbFieldsWithDefaults()
    {
        $list = $this->retrieveDBFields('db');
        $toAdd = $this->Config()->get('additional_db_fields');
        $toAdd = array_combine($toAdd, $toAdd);

        return $toAdd + $list;
    }

    public function mydbfieldsfancywithbelongswithbasicfields()
    {
        return $this->MyDbFieldsFancyWithBelongs();
    }

    public function MyDbFieldsFancyWithBelongs()
    {
        return $this->myDbFieldsFancyWithoutBelongs(true);
    }

    public function MyDbFieldsFancyWithoutBelongs($includeBelongs = false)
    {
        $ar = [];
        $list = $this->retrieveDBFields('db');
        foreach ($list as $key => $value) {
            $ar[$key] = $key;
            $shortValue = explode('(', $value);
            $shortValue = $shortValue[0];
            switch ($shortValue) {
                case 'Varchar':
                case 'HTMLTextField':
                case 'HTMLVarchar':
                case 'Text':
                    $ar[$key.'.LimitCharacters'] = $key.'.LimitCharacters';
                    break;
                default:
                    $ar[$key.'.Nice'] = $key.'.Nice';
            }
        }
        $list = [];
        if ($includeBelongs) {
            $list += $this->retrieveDBFields('belongs_to');
        }
        $list += $this->retrieveDBFields('has_one');
        foreach ($list as $key => $value) {
            if ($value === 'Image' || is_subclass_of($value, 'Image')) {
                $ar[$key.'.Thumbnail'] = $key.'.Thumbnail';
            } else {
                $ar[$key.'.Title'] = $key.'.Title';
            }
        }
        $list =
            $this->retrieveDBFields('has_many') +
            $this->retrieveDBFields('many_many');
        if ($includeBelongs) {
            $list += $this->retrieveDBFields('belongs_many_many');
        }
        foreach ($list as $key => $value) {
            $ar[$key.'.Count'] = $key.'.Count';
        }

        return $ar;
    }

    public function MyDbFieldsAndHasOnes()
    {
        return
            $this->retrieveDBFields('db') +
            $this->retrieveDBFields('has_one');
    }

    public function MyDbFieldsAndHasOnesWithIDs()
    {
        $list = $this->retrieveDBFields('db');
        $hasOnes = $this->retrieveDBFields('has_one');
        foreach ($hasOnes as $field => $type) {
            $fieldWithID = $field . 'ID';
            $list[$fieldWithID] = $fieldWithID;
        }

        return $list;
    }

    public function MyDbFieldsAndIndexes()
    {
        return
            $this->MyDbFieldsWithDefaults() +
            ['index1' => 'index1'] +
            ['index2' => 'index2'] +
            ['index3' => 'index3'] +
            ['index4' => 'index4'] +
            ['index5' => 'index5'];
    }

    public function MyAllFieldsWithBelongs()
    {
        return $this->myAllFieldsWithoutBelongs(true);
    }

    public function MyAllFieldsWithoutBelongs($includeBelongs = false)
    {
        $list = $this->MyDbFieldsWithDefaults();
        if ($includeBelongs) {
            $list += $this->retrieveDBFields('belongs_to');
        }
        $list +=
            $this->retrieveDBFields('has_one') +
            $this->retrieveDBFields('has_many') +
            $this->retrieveDBFields('many_many');
        if ($includeBelongs) {
            $list += $this->retrieveDBFields('belongs_many_many');
        }

        return $list;
    }


    public function IndexOptions()
    {
        return [
            'true' => 'true',
            'unique("<column-name>")' => 'unique',
            '[\'type\' => \'<type>\', \'value\' => \'"<column-name>"\']' => 'other'
        ];
    }

    public function RequiredOptions()
    {
        return [
            'true' => 'true',
            'unique' => 'unique'
        ];
    }


    public function PossibleRelationsWithBaseClass($rootClass = '')
    {
        if ($rootClass) {
            //
        } else {
            $rootClass = $this->rootBaseClass;
        }
        $list =
            [$rootClass => $rootClass] +
            $this->possibleRelations();
        asort($list);

        return $list;
    }

    protected $_classesCache = [];

    /**
     *
     * @return array
     */
    public function PossibleRelations($rootClass = '')
    {
        if ($rootClass) {
            //
        } else {
            $rootClass = $this->rootBaseClass;
        }
        if (!isset($this->_classesCache[$rootClass])) {
            $list = \ClassInfo::subclassesFor($rootClass);
            $newList = [];
            foreach ($list as $class) {
                if (
                    $class == $rootClass ||
                    is_subclass_of($class, 'TestOnly') ||
                    in_array($class, $this->Config()->get('excluded_data_objects'))
                ) {
                    //do nothing
                } else {
                    $newList[$class] = $class;
                    $name = \Injector::inst()->get($class)->singular_name();
                    if ($name !== $class) {
                        $newList[$class] .= ' ('.$name.')';
                    }
                }
            }
            $this->_classesCache[$rootClass] = $newList;
        }

        return $this->_classesCache[$rootClass];
    }

    protected $_filtersCache = [];

    public function PossibleSearchFilters()
    {
        if (count($this->_filtersCache) === 0) {
            $list = \ClassInfo::subclassesFor('SearchFilter');
            $newList = [];
            foreach ($list as $class) {
                if ($class !== 'SearchFilter') {
                    $newList[$class] = $class;
                }
            }
            $this->_filtersCache = $newList;
        }

        return $this->_filtersCache;
    }

    protected $_modelAdmins = [];

    public function ModelAdminOptions()
    {
        if (count($this->_modelAdmins) === 0) {
            $list = \ClassInfo::subclassesFor('ModelAdmin');
            $newList = [];
            foreach ($list as $class) {
                if (
                    $class == 'ModelAdmin' ||
                    is_subclass_of($class, 'TestOnly')
                ) {
                    //do nothing
                } else {
                    $newList[$class] = $class;
                }
            }
            $newList['tba'] = 'tba';
            $this->_modelAdmins = $newList;
        }

        return $this->_modelAdmins;
    }


    public function SortOptions()
    {
        return [
            'ASC' => 'ASC',
            'DESC' => 'DESC'
        ];
    }

    protected $_canOptions = null;

    public function CanOptions()
    {
        if (! $this->_canOptions) {
            $ar = [
                'one' => 'only one (useful for can create)',
                'true' => 'always',
                'false' => 'never',
                'parent' => 'use parent class',
            ];
            $permissions = \Permission::get()->column('Code');
            $ar = $ar + array_combine($permissions, $permissions);

            $this->_canOptions = $ar;
        }

        return $this->_canOptions;
    }



    /**
     *
     * @return array
     */
    public function SiteTreeList($rootClass = 'SiteTree')
    {
        $list = \ClassInfo::subclassesFor($rootClass);
        foreach ($list as $class) {
            if (
                $class == $rootClass ||
                is_subclass_of($class, 'TestOnly') ||
                in_array($class, $this->Config()->get('excluded_data_objects'))
            ) {
                //do nothing
            } else {
                $newList[$class] = $class;
                $name = \Injector::inst()->get($class)->singular_name();
                if ($name !== $class) {
                    $newList[$class] .= ' ('.$name.')';
                }
            }
        }

        return $newList;
    }


    /**
     *
     * @return array
     */
    public function AllowedChildrenOptions($rootClass = 'SiteTree')
    {
        return ['none' => 'none'] + $this->SiteTreeList($rootClass);
    }

    /**
     *
     * @return array
     */
    public function TrueOrFalseListWithIgnore()
    {
        return [
            '' => '-- ignore --'
        ] +
        $this->TrueOrFalseList();
    }

    /**
     *
     * @return array
     */
    public function TrueOrFalseList()
    {
        return [
            'true' => 'YES',
            'false' => 'NO'
        ];
    }
}
