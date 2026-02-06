<?php

namespace Sunnysideup\BuildDataObject\Api;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\CompositeField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBForeignKey;
use SilverStripe\ORM\FieldType\DBLocale;
use SilverStripe\ORM\FieldType\DBPrimaryKey;
use SilverStripe\ORM\FieldType\DBString;
use SilverStripe\ORM\Filters\SearchFilter;
use SilverStripe\Security\LoginAttempt;
use SilverStripe\Security\MemberPassword;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionRoleCode;
use SilverStripe\SiteConfig\SiteConfig;

class DataObjectLists
{
    use Extensible;
    use Injectable;
    use Configurable;

    protected $rootBaseClass = DataObject::class;

    protected $myBaseClass = '';

    protected $_data;

    protected $_dbfieldCache;

    protected $_classesCache = [];

    protected $_filtersCache = [];

    protected $_modelAdmins = [];

    protected $_canOptions;

    private static $excluded_data_objects = [
        PermissionRoleCode::class,
        LoginAttempt::class,
        MemberPassword::class,
        MemberPassword::class,
        SiteConfig::class,
    ];

    private static $excluded_db_fields_types = [
        DBField::class,
        DBLocale::class,
        DBString::class,
        CompositeField::class,
        DBPrimaryKey::class,
        DBForeignKey::class,
    ];

    private static $additional_db_fields = [
        'ID',
        'Created',
        'LastEdited',
        'ClassName',
    ];

    private static $_my_singleton = [];

    public static function inst(string $myBaseClass, $data)
    {
        if (! isset(self::$_my_singleton[$myBaseClass])) {
            self::$_my_singleton[$myBaseClass] = Injector::inst()->get(self::class);
        }

        self::$_my_singleton[$myBaseClass]->_data = $data;
        self::$_my_singleton[$myBaseClass]->setBaseClass($myBaseClass);

        return self::$_my_singleton[$myBaseClass];
    }

    public function setBaseClass($myBaseClass)
    {
        $this->myBaseClass = $myBaseClass;
    }

    public function DbFields()
    {
        if (! $this->_dbfieldCache) {
            $newCache = $this->walkSubclasses(function ($type) {
                $key = $type->toDropdown();
                $val = $type->toDropdown();

                return [$key => $val];
            }, DBField::class);

            // add missing field types
            $newCache['HTMLFragment'] = 'HTMLFragment';

            $this->_dbfieldCache = $newCache;
            //echo '<!--'; print_r($this->_dbfieldCache); echo '-->';
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
                    $ar[$key . '.LimitCharacters'] = $key . '.LimitCharacters';

                    break;
                default:
                    $ar[$key . '.Nice'] = $key . '.Nice';
            }
        }

        $list = [];
        if ($includeBelongs) {
            $list += $this->retrieveDBFields('belongs_to');
        }

        $list += $this->retrieveDBFields('has_one');
        foreach ($list as $key => $value) {
            if (Image::class === $value || is_subclass_of($value, Image::class)) {
                $ar[$key . '.CMSThumbnail'] = $key . '.CMSThumbnail';
            } else {
                $ar[$key . '.Title'] = $key . '.Title';
            }
        }

        $list =
            $this->retrieveDBFields('has_many') +
            $this->retrieveDBFields('many_many');
        if ($includeBelongs) {
            $list += $this->retrieveDBFields('belongs_many_many');
        }

        foreach (array_keys($list) as $key) {
            $ar[$key . '.Count'] = $key . '.Count';
        }

        return $ar;
    }

    public function MyDbFieldsAndHasOnes()
    {
        return $this->retrieveDBFields('db') +
            $this->retrieveDBFields('has_one');
    }

    public function MyDbFieldsAndHasOnesWithIDs()
    {
        $list = $this->retrieveDBFields('db');
        $hasOnes = $this->retrieveDBFields('has_one');
        foreach (array_keys($hasOnes) as $field) {
            $fieldWithID = $field . 'ID';
            $list[$fieldWithID] = $fieldWithID;
        }

        return $list;
    }

    public function MyDbFieldsAndIndexes()
    {
        return $this->MyDbFieldsWithDefaults() +
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
            '[\'type\' => \'<type>\', \'value\' => \'"<column-name>"\']' => 'other',
        ];
    }

    public function RequiredOptions()
    {
        return [
            'true' => 'true',
            'unique' => 'unique',
        ];
    }

    public function PossibleRelationsWithBaseClass($rootClass = '')
    {
        if (!$rootClass) {
            $rootClass = $this->rootBaseClass;
        }

        $list =
            [$rootClass => \Sunnysideup\BuildDataObject\Api\DBTypeConverter::fromClass($rootClass)->toDropdown()] +
            $this->possibleRelations();
        asort($list);

        return $list;
    }

    /**
     * @return array
     */
    public function possibleRelations(string $rootClass = '')
    {
        if ($rootClass === '' || $rootClass === '0') {
            $rootClass = $this->rootBaseClass;
        }

        if (! isset($this->_classesCache[$rootClass])) {
            $newList = $this->walkSubclasses(function ($type) {
                $key = $type->toClass();
                $val = $type->toDropdown();
                $singular = Injector::inst()->get($key)->singular_name();
                if ($singular !== $val) {
                    $val .= ' (' . $singular . ')';
                }

                return [$key => $val];
            }, $rootClass);
            $this->_classesCache[$rootClass] = $newList;
        }

        return $this->_classesCache[$rootClass];
    }

    public function PossibleSearchFilters()
    {
        if (0 === count($this->_filtersCache)) {
            $list = ClassInfo::subclassesFor(SearchFilter::class);
            $newList = [];
            foreach ($list as $class) {
                if (SearchFilter::class !== $class) {
                    $newList[$class] = $class;
                }
            }

            $this->_filtersCache = $newList;
        }

        return $this->_filtersCache;
    }

    public function ModelAdminOptions()
    {
        if (0 === count($this->_modelAdmins)) {
            $list = ClassInfo::subclassesFor(ModelAdmin::class);
            $newList = [];
            foreach ($list as $class) {
                if (
                    ModelAdmin::class === $class ||
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
            'DESC' => 'DESC',
        ];
    }

    public function CanOptions()
    {
        if (! $this->_canOptions) {
            $ar = [
                'one' => 'only one (useful for can create)',
                'true' => 'always',
                'basedonmodeladmin' => 'mimic my Model Admin',
                'false' => 'never',
                'parent' => 'use parent class',
            ];
            $permissions = Permission::get()->column('Code');
            $ar += array_combine($permissions, $permissions);

            $this->_canOptions = $ar;
        }

        return $this->_canOptions;
    }

    public function SiteTreeList($rootClass = SiteTree::class): array
    {
        $list = ClassInfo::subclassesFor($rootClass);
        $newList = [];
        foreach ($list as $class) {
            if (
                $class === $rootClass ||
                is_subclass_of($class, TestOnly::class) ||
                in_array($class, $this->Config()->get('excluded_data_objects'), true)
            ) {
                //do nothing
            } else {
                $newList[$class] = $class;
                $name = Injector::inst()->get($class)->singular_name();
                if ($name !== $class) {
                    $newList[$class] .= ' (' . $name . ')';
                }
            }
        }

        return $newList;
    }

    /**
     * @param mixed $rootClass
     *
     * @return array
     */
    public function AllowedChildrenOptions($rootClass = SiteTree::class)
    {
        return ['none' => 'none'] + $this->SiteTreeList($rootClass);
    }

    /**
     * @return array
     */
    public function TrueOrFalseListWithIgnore()
    {
        return [
            '' => '-- ignore --',
        ] +
        $this->TrueOrFalseList();
    }

    /**
     * @return array
     */
    public function TrueOrFalseList()
    {
        return [
            'true' => 'YES',
            'false' => 'NO',
        ];
    }

    public function MyCanMethodBuilder($type, $value)
    {
        if ('parent' === $value) {
            return null;
        }

        if ('one' === $value) {
            $str = 'DataObject::get_one($this->class) ? false : true;';
        } elseif ('true' === $value) {
            $str = 'true;';
        } elseif ('false' === $value) {
            $str = 'false;';
        } else {
            $str = "Permission::check('" . $value . '\', \'any\', $member);';
        }

        return DBField::create_field('Varchar', $str);
    }

    protected function retrieveDBFields($name)
    {
        $data = $this->_data;
        $ar = [];
        if (isset($data->{$name})) {
            foreach ($data->{$name} as $data) {
                if ($data->Key && $data->Value) {
                    $ar[$data->Key] = $data->Key;
                }
            }
        }

        return $ar;
    }

    private function isExcludedClass($fqClass, $fqRootClass): bool
    {
        return $fqClass === $fqRootClass
            || is_subclass_of($fqClass, TestOnly::class)
            || in_array($fqClass, $this->Config()->get('excluded_db_fields_types'), true);
    }

    private function walkSubclasses(callable $callback, string $fqRootClass): array
    {
        $array = [];
        $fqChildren = ClassInfo::subclassesFor($fqRootClass);
        foreach ($fqChildren as $fqChildClass) {
            if ($this->isExcludedClass($fqChildClass, $fqRootClass)) {
                //do nothing
            } else {
                $type = \Sunnysideup\BuildDataObject\Api\DBTypeConverter::fromClass($fqChildClass);
                $result = call_user_func($callback, $type);
                if (is_array($result)) {
                    $array += $result;
                }
            }
        }

        return $array;
    }
}
