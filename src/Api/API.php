<?php

namespace Sunnysideup\BuildDataObject\API;
use SilverStripe\Security\PermissionRoleCode;
use SilverStripe\Security\LoginAttempt;
use SilverStripe\Security\MemberPassword;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBLocale;
use SilverStripe\Forms\CompositeField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBEnum;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\Filters\SearchFilter;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Security\Permission;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\View\ViewableData;


class DataObjectAPI extends ViewableData
{
    private static $excluded_data_objects = [
        PermissionRoleCode::class,
        LoginAttempt::class,
        MemberPassword::class,
        MemberPassword::class,
        SiteConfig::class
    ];

    private static $excluded_db_fields_types = [
        DBField::class,
        DBLocale::class,
        DBString::class,
        CompositeField::class,
        DBPrimaryKey::class,
        DBForeignKey::class
    ];

    private static $additional_db_fields = [
        'ID',
        'Created',
        'LastEdited',
        'ClassName'
    ];

    protected $rootBaseClass = DataObject::class;

    protected $myBaseClass = '';

    protected $_data = null;

    private static $_my_singleton = [];

    public static function inst($myBaseClass = DataObject::class, $data)
    {
        if (! isset(self::$_my_singleton[$myBaseClass])) {
            self::$_my_singleton[$myBaseClass] = Injector::inst()->get('Sunnysideup\BuildDataObject\API\DataObjectAPI');
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
            $list = ClassInfo::subclassesFor(DBField::class);
            $newList = [];
            foreach ($list as $fullQName) {
                $shortName = $this->dbFieldNameForClass($fullQName);

                if (DBHTMLVarchar::class === $fullQName) {
                    $fullQName = DBHTMLVarchar::class.'(255)';
                    $shortName = 'DBHTMLVarchar(255)';
                } elseif (DBEnum::class === $fullQName) {
                    $fullQName = DBEnum::class.'(\\\'Foo,Bar\\\', \\\'FOO\\\')';
                    $shortName = $shortName;
                } elseif (DBMultiEnum::class === $fullQName) {
                    $fullQName = DBMultiEnum::class.'(\\\'Foo,Bar\\\', \\\'FOO\\\')';
                    $shortName = $shortName;
                }
                if (
                    $fullQName == DBField::class ||
                    is_subclass_of($fullQName, TestOnly::class) ||
                    in_array($fullQName, $this->Config()->get('excluded_db_fields_types'))
                ) {
                    //do nothing
                } else {
                    $newList[$shortName] = $shortName;
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
                case 'DBVarchar':
                case 'DBHTMLTextField':
                case 'DBHTMLVarchar':
                case 'DBText':
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
            if ($value === Image::class || is_subclass_of($value, Image::class)) {
                $ar[$key.'.CMSThumbnail'] = $key.'.CMSThumbnail';
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
            $list = ClassInfo::subclassesFor($rootClass);
            $newList = [];
            foreach ($list as $class) {
                if (
                    $class == $rootClass ||
                    is_subclass_of($class, TestOnly::class) ||
                    in_array($class, $this->Config()->get('excluded_data_objects'))
                ) {
                    //do nothing
                } else {
                    $newList[$class] = $class;
                    $name = Injector::inst()->get($class)->singular_name();
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
            $list = ClassInfo::subclassesFor(SearchFilter::class);
            $newList = [];
            foreach ($list as $class) {
                if ($class !== SearchFilter::class) {
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
            $list = ClassInfo::subclassesFor(ModelAdmin::class);
            $newList = [];
            foreach ($list as $class) {
                if (
                    $class == ModelAdmin::class ||
                    is_subclass_of($class, TestOnly::class)
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
            $permissions = Permission::get()->column('Code');
            $ar = $ar + array_combine($permissions, $permissions);

            $this->_canOptions = $ar;
        }

        return $this->_canOptions;
    }



    /**
     *
     * @return array
     */
    public function SiteTreeList($rootClass = SiteTree::class)
    {
        $list = ClassInfo::subclassesFor($rootClass);
        foreach ($list as $class) {
            if (
                $class == $rootClass ||
                is_subclass_of($class, TestOnly::class) ||
                in_array($class, $this->Config()->get('excluded_data_objects'))
            ) {
                //do nothing
            } else {
                $newList[$class] = $class;
                $name = Injector::inst()->get($class)->singular_name();
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
    public function AllowedChildrenOptions($rootClass = SiteTree::class)
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


    public function removeNamespacesFromArrayValues(array $source) : array
    {
        // strip 'SilverStripe\...\DB'
        $source = array_map(
            function($e){
                return $this->shortNameForClass($e);
            },
            $source
        );
        return $source;
    }

    /**
     * turns SilverStripe\ORM\DB into DB
     *
     * @param string $longClassName
     * @return string
     */
    public function shortNameForClass($longClassName)
    {
        if(class_exists($longClassName)) {
            return ClassInfo::shortName($longClassName);
        } else {
            return $longClassName;
        }
    }

    /**
     * convert a class name into the corresponding DB field name
     * @param string $className
     * @return string
     */
    public function dbFieldNameForClass($className)
    {
        $shortName = $this->shortNameForClass($className);
        // remove the beginning 'DB'
        if (substr($shortName, 0, 2) == 'DB') {
            $shortName = substr($shortName, 2, strlen($shortName));
        }
        switch ($shortName) {
            case 'File':
            case 'Enum':
                $shortName = 'DB'.$shortName;
                break;
        }

        return $shortName;
    }
}
