<?php


namespace SunnySideUp\BuildDataObject;

class BuildController extends \Controller {

    private static $form_data_session_variable = 'SunnySideUp\BuildDataObject\BuildController';

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

    private static $allowed_actions = [
        'primaryformstart' => true,
        'PrimaryForm' => true,
        'doprimaryform' => true,
        'secondaryformstart' => true,
        'SecondaryForm' => true,
        'dosecondaryform' => true,
        'results' => true,
        'startover' => true,
        'debug' => true
    ];

    function Title()
    {
        return 'Build a Data Object - Step '.$this->step.' of 2';
    }

    function jQueryLink()
    {
        return \Director::absoluteURL('/framework/thirdparty/jquery/jquery.js');
    }

    function startover()
    {
        $this->saveData('_PrimaryForm', null);
        $this->saveData('_SecondaryForm', null);
        return $this->redirect($this->link('primaryformstart'));
    }

    /**
     *
     * @var Form
     */
    protected $step = 1;

    /**
     *
     * @var Form
     */
    protected $form = null;

    /**
     *
     * @var Form
     */
    protected $prevLink = null;

    /**
     *
     * @var ArrayList
     */
    protected $finalData = null;

    function Link($action = null)
    {
        if($action) {
            $action .= '/';
        }
        return '/build-data-object/'.$action;
    }

    function index()
    {
        return $this->redirect($this->Link('primaryformstart'));
    }

    function primaryformstart()
    {
        $this->PrimaryForm();
        $this->prevLink = $this->Link('startover');

        return $this->renderWith('BuildDataObject');
    }


    function PrimaryForm()
    {
        $this->form = $this->createForm('PrimaryForm', 'Build Model');

        return $this->form;
    }

    function doprimaryform($data, $form)
    {
        $this->saveData('_PrimaryForm', $data);

        return $this->redirect($this->Link('secondaryformstart'));
    }


    function secondaryformstart()
    {
        $this->step = 2;
        $this->SecondaryForm();
        $this->prevLink = $this->Link('primaryformstart');

        return $this->renderWith('BuildDataObject');
    }

    function SecondaryForm()
    {
        $this->form = $this->createForm('SecondaryForm', 'Download Example Class');

        return $this->form;
    }


    function dosecondaryform($data, $form)
    {
        $this->saveData('_SecondaryForm', $data);

        return $this->redirect($this->Link('results'));
    }

    function results()
    {
        $this->finalData = $this->processedFormData($this->retrieveData());
        return \SS_HTTPRequest::send_file(
            $this->renderWith('BuildDataObjectResults'),
            $this->finalData->Name.'.php'
        );
    }

    function debug()
    {
        $this->finalData = $this->processedFormData($this->retrieveData());
        print_r($this->CanMethodBuilder('canEdit'));
        print_r($this->finalData);
        die('-----------------------------');
    }

    function Form()
    {
        return $this->form;
    }

    function FinalData()
    {
        return $this->finalData;
    }

    function PrevLink()
    {
        return $this->prevLink;
    }

    function MyCanMethodBuilder($type, $value) {
        if($value === 'parent') {
            return null;
        } elseif($value === 'true') {
            $str = 'true;';
        } elseif($value === 'false') {
            $str = 'false;';
        } else {
            $str = 'Permission::check(\''.$value.'\', \'any\', $member);';
        }

        return \DBField::create_field('Varchar', $str);
    }

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
            ['defaults',            'myDbFields',                  'text'],
            ['default_sort',        'myDbFields',                  'sortOptions'],
            ['indexes',             'myDbFieldsAndIndexes',        'indexOptions'],
            ['required_fields',     'myDbFieldsAndHasOnesWithIDs', 'requiredOptions'],
            ['field_labels',        'myAllFieldsWithoutBelongs',   'text'],
            ['field_labels_right',  'myAllFieldsWithoutBelongs',   'text'],
            ['searchable_fields',   'myDbFieldsAndHasOnesWithIDs', 'possibleSearchFilters'],
            ['summary_fields',      'myDbFieldsFancyWithBelongs',  'text'],
            ['casting',             'text',                        'dbFields'],
            ['canCreate',           'canOptions',                  'ignore'],
            ['canView',             'canOptions',                  'ignore'],
            ['canEdit',             'canOptions',                  'ignore'],
            ['canDelete',           'canOptions',                  'ignore']
        ];
    }

    protected function createForm($formName, $actionTitle)
    {
        if($formName === 'PrimaryForm') {
            $isPrimary = true;
            $isSecond = false;
        } elseif($formName === 'SecondaryForm') {
            $isPrimary = false;
            $isSecond = true;
        } else {
            user_error('Set right form type: '.$formName.' is not valid');
        }

        $finalFields = \FieldList::create();

        if($isPrimary) {
            $toBuild = $this->primaryThingsToBuild();
            $finalFields->push(\HeaderField::create('Name your DataObject'));
            $finalFields->push(\TextField::create('Name', ''));
            $finalFields->push(\HeaderField::create('Model Admin Used'));
            $finalFields->push(\DropdownField::create('ModelAdmin', '', $this->prependNullOption($this->modelAdminOptions())));
        } else {
            $toBuild = $this->secondaryThingsToBuild();
        }
        $formFields = [];

        $count = 0;
        //build fields ...
        foreach($toBuild as $item) {
            $name = $item[0];
            if($isPrimary) {
                $type = $item[1];
            } else {
                if(substr($name, 0, 3) === 'can') {
                    $type = 'options';
                } else {
                    $type = 'array';
                }
            }
            $formFields[$count] = [];
            $formFields[$count][0] = [
                $name.'_HEADER',
                'HeaderField',
                $name
            ];
            switch ($type) {
                case 'array':
                    if($isSecond) {
                        //key source
                        $sourceMethod1 = $item[1];
                        if($sourceMethod1 === 'text') {
                            $source1 = null;
                        } else {
                            $source1 = $this->$sourceMethod1();
                        }
                        //value source
                        $sourceMethod2 = $item[2];
                        if($sourceMethod2 === 'text') {
                            $source2 = null;
                        } else {
                            $source2 = $this->$sourceMethod2();
                        }
                    } else {
                        $sourceMethod = $item[2];
                        $source = $this->$sourceMethod();
                    }
                    for($i = 1; $i < 13; $i++) {
                        if($isSecond) {
                            if($source1 === 'ignore') {
                                $formFields[$count][$i]['KEY'] = [
                                    $name,
                                    'DropdownField',
                                    $source1
                                ];
                            } elseif($source1) {
                                $formFields[$count][$i]['KEY'] = [
                                    $name.'__KEY__'.$i,
                                    'DropdownField',
                                    $source1
                                ];
                            } else {
                                $formFields[$count][$i]['KEY'] = [
                                    $name.'__KEY__'.$i,
                                    'TextField'
                                ];
                            }
                            if($source2 === 'ignore') {
                                //do nothing ...
                            } elseif($source2) {
                                $formFields[$count][$i]['VALUE'] = [
                                    $name.'__VALUE__'.$i,
                                    'DropdownField',
                                    $source2
                                ];
                            } else {
                                $formFields[$count][$i]['VALUE'] = [
                                    $name.'__VALUE__'.$i,
                                    'TextField'
                                ];
                            }
                        } else {
                            $formFields[$count][$i]['KEY'] = [
                                $name.'__KEY__'.$i,
                                'TextField'
                            ];
                            $formFields[$count][$i]['VALUE'] = [
                                $name.'__VALUE__'.$i,
                                'DropdownField',
                                $source
                            ];
                        }
                    }
                    $formFields[$count][13] = [
                        $name.'_ADD_'.$i,
                        'LiteralField',
                        '
                        <div class="CompositeField add-and-remove">
                            <a href="#" class="add first-add"><i class="material-icons">add_circle_outline</i></a>
                            <a href="#" class="remove"><i class="material-icons">remove_circle_outline</i></a>
                        </div>
                        '
                    ];
                    break;
                case 'options':
                    $formFields[$count][1] = [
                        $name,
                        'DropdownField',
                        $this->canOptions()
                    ];
                    break;
                case 'text':
                default:
                    $formFields[$count][1] = [
                        $name,
                        'TextField'
                    ];
                    break;
                    //do nothing
            }
            $count++;
        }
        //create fields ...
        $count = 0;
        foreach($formFields as $subFieldList) {
            $count++;
            $compositeField = \CompositeField::create();
            $compositeField->addExtraClass('OuterComposite pos'.$count);
            $innerCount = 0;
            foreach($subFieldList as $fieldDetails) {
                $innerCount++;
                if(isset($fieldDetails['KEY']) && isset($fieldDetails['VALUE'])) {
                    $subCompositeField = \CompositeField::create();
                    $subCompositeField->addExtraClass('InnerComposite pos'.$innerCount);
                    $innerInnerCount = 0;
                    foreach($fieldDetails as $fieldDetailsInner) {
                        $fieldName = $fieldDetailsInner[0];
                        $fieldType = $fieldDetailsInner[1];
                        $additionalClasses = [];
                        if(strpos($fieldName, '__KEY__')) {
                            $additionalClasses[] = 'mykey';
                        }
                        if(strpos($fieldName, '__VALUE__')) {
                            $additionalClasses[] = 'myvalue';
                        }
                        if(isset($fieldDetailsInner[2])) {
                            $source = $fieldDetailsInner[2];
                            $source = $this->prependNullOption( $source );
                            $tempField = $fieldType::create($fieldName, '', $source);
                        } else {
                            $tempField = $fieldType::create($fieldName, '');
                        }
                        if(count($additionalClasses)) {
                            $classes = implode(' ', $additionalClasses);
                            $tempField->addExtraClass($classes);
                        }
                        $subCompositeField->push($tempField);
                    }
                    $compositeField->push($subCompositeField);
                } else {
                    $fieldName = $fieldDetails[0];
                    $fieldType = $fieldDetails[1];
                    if($fieldType === 'DropdownField') {
                        $source = $fieldDetails[2];
                        $source = $this->prependNullOption($source);
                        $compositeField->push($fieldType::create($fieldName, '', $source));
                    } elseif($fieldType === 'HeaderField') {
                        $title = str_replace('_', ' ', $fieldDetails[2]);
                        $compositeField->push($fieldType::create($fieldName, $title));
                    } elseif($fieldType === 'LiteralField') {
                        $title = $fieldDetails[2];
                        $compositeField->push($fieldType::create($fieldName, $title));
                    } else {
                        $compositeField->push($fieldType::create($fieldName, ''));
                    }
                }
            }
            $finalFields->push($compositeField);
        }
        $actions = \FieldList::create(
            [\FormAction::create('do'.strtolower($formName), $actionTitle)]
        );

        $form = \Form::create($this, $formName, $finalFields, $actions);
        $form->setFormAction($this->Link($formName));
        $form->loadDataFrom($this->retrieveData());

        return $form;
    }

    private $_dbfieldCache = [];

    function dbFields()
    {
        if(count($this->_dbfieldCache) === 0) {
            $list = \ClassInfo::subclassesFor('DbField');
            $newList = [];
            foreach($list as $class) {
                if(substr($class, 0, 2) == 'DB') {
                    $class = substr($class, 2, strlen($class));
                }
                if(substr($class, 0, 3) == 'SS_') {
                    $class = substr($class, 3, strlen($class));
                }
                if('Varchar' === $class) {
                    $class = 'Varchar(n)';
                }
                if('HTMLVarchar' === $class) {
                    $class = 'HTMLVarchar(n)';
                }
                if('Enum' === $class) {
                    $class = 'Enum(\\\'Foo,Bar\\\', \\\'FOO\\\')';
                }
                if('MultiEnum' === $class) {
                    $class = 'MultiEnum(\\\'Foo,Bar\\\', \\\'FOO\\\')';
                }
                if(
                    $class == 'DbField' ||
                    is_subclass_of($class, 'TestOnly') ||
                    in_array($class, $this->Config()->get('excluded_db_fields_types'))
                ) {
                    //do nothing
                } else {
                    $newList[$class] = $class;
                }
            }
            ksort($newList);
            $this->_dbfieldCache = $newList;
        }

        return $this->_dbfieldCache;
    }

    protected function myDbFields()
    {
        return $this->retrieveDBFields('db');
    }

    protected function myDbFieldsFancyWithBelongs()
    {
        return $this->myDbFieldsFancyWithoutBelongs(true);
    }

    protected function myDbFieldsFancyWithoutBelongs($includeBelongs = false)
    {
        $ar = [];
        $list = $this->retrieveDBFields('db');
        foreach($list as $key => $value) {
            $ar[$key] = $key;
            $shortValue = explode('(',$value);
            $shortValue = $shortValue[0];
            switch($shortValue) {
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
        if($includeBelongs) {
            $list += $this->retrieveDBFields('belongs_to');
        }
        $list += $this->retrieveDBFields('has_one');
        foreach($list as $key => $value) {
            if($value === 'Image' || is_subclass_of($value, 'Image')) {
                $ar[$key.'.Thumbnail'] = $key.'.Thumbnail';
            } else {
                $ar[$key.'.Title'] = $key.'.Title';
            }
        }
        $list =
            $this->retrieveDBFields('has_many') +
            $this->retrieveDBFields('many_many');
        if($includeBelongs) {
            $list += $this->retrieveDBFields('belongs_many_many');
        }
        foreach($list as $key => $value) {
            $ar[$key.'.Count'] = $key.'.Count';
        }

        return $ar;
    }

    protected function myDbFieldsAndHasOnes()
    {
        return
            $this->retrieveDBFields('db') +
            $this->retrieveDBFields('has_one');
    }

    protected function myDbFieldsAndHasOnesWithIDs()
    {
        $list = $this->retrieveDBFields('db');
        $hasOnes = $this->retrieveDBFields('has_one');
        foreach($hasOnes as $field => $type) {
            $fieldWithID = $field . 'ID';
            $list[$fieldWithID] = $fieldWithID;
        }

        return $list;
    }

    protected function myDbFieldsAndIndexes()
    {
        return
            $this->retrieveDBFields('db') +
            ['index1' => 'index1'] +
            ['index2' => 'index2'] +
            ['index3' => 'index3'] +
            ['index4' => 'index4'] +
            ['index5' => 'index5'];
    }

    protected function myAllFieldsWithBelongs()
    {
        return $this->myAllFieldsWithoutBelongs(true);
    }

    protected function myAllFieldsWithoutBelongs($includeBelongs = false)
    {
        $list = $this->retrieveDBFields('db');
        if($includeBelongs) {
            $list += $this->retrieveDBFields('belongs_to');
        }
        $list +=
            $this->retrieveDBFields('has_one') +
            $this->retrieveDBFields('has_many') +
            $this->retrieveDBFields('many_many');
        if($includeBelongs) {
            $list += $this->retrieveDBFields('belongs_many_many');
        }

        return $list;
    }

    protected function retrieveDBFields($name)
    {
        $data = $this->processedFormData();
        $ar = [];
        if(isset($data->$name)) {
            foreach($data->$name as $data) {
                if($data->Key && $data->Value) {
                    $ar[$data->Key] = $data->Key;
                }
            }
        }

        return $ar;
    }

    protected function indexOptions()
    {
        return [
            'true' => 'true',
            'unique("<column-name>")' => 'unique',
            '[\'type\' => \'<type>\', \'value\' => \'"<column-name>"\']' => 'other'
        ];
    }

    protected function requiredOptions()
    {
        return [
            'true' => 'true',
            'unique' => 'unique'
        ];
    }

    private $_classesCache = [];

    protected function possibleRelations()
    {
        if(count($this->_classesCache) === 0) {
            $list = \ClassInfo::subclassesFor('DataObject');
            $newList = [];
            foreach($list as $class) {
                if(
                    $class == 'DataObject' ||
                    is_subclass_of($class, 'TestOnly') ||
                    in_array($class, $this->Config()->get('excluded_data_objects'))
                ) {
                    //do nothing
                } else {
                    $newList[$class] = $class;
                    $name = \Injector::inst()->get($class)->singular_name();
                    if($name !== $class) {
                        $newList[$class] .= ' ('.$name.')';
                    }
                }
            }
            $this->_classesCache = $newList;
        }

        return $this->_classesCache;
    }

    private $_filtersCache = [];

    protected function possibleSearchFilters()
    {
        if(count($this->_filtersCache) === 0) {
            $list = \ClassInfo::subclassesFor('SearchFilter');
            $newList = [];
            foreach($list as $class) {
                if($class !== 'SearchFilter') {
                    $newList[$class] = $class;
                }
            }
            $this->_filtersCache = $newList;
        }

        return $this->_filtersCache;
    }

    private $_modelAdmins = [];

    protected function modelAdminOptions()
    {
        if(count($this->_modelAdmins) === 0) {
            $list = \ClassInfo::subclassesFor('ModelAdmin');
            $newList = [];
            foreach($list as $class) {
                if(
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


    protected function sortOptions()
    {
        return [
            'ASC' => 'ASC',
            'DESC' => 'DESC'
        ];
    }

    private $_canOptions = null;

    protected function canOptions()
    {
        if(! $this->_canOptions) {
            $ar = [
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

    protected function saveData($name, $data)
    {
        $var = $this->Config()->get('form_data_session_variable');
        \Session::set($var.$name, $data);
        \Session::save();
    }

    private $_data = null;

    protected function retrieveData()
    {
        if(! $this->_data) {
            $var = $this->Config()->get('form_data_session_variable');
            $retrieveDataPrimary = \Session::get($var.'_PrimaryForm');
            if ($retrieveDataPrimary && (is_array($retrieveDataPrimary) || is_object($retrieveDataPrimary))) {
                //do nothing
            } else {
                $retrieveDataPrimary = [];
            }
            $retrieveDataSecondary = \Session::get($var.'_SecondaryForm');
            if ($retrieveDataSecondary && (is_array($retrieveDataSecondary) || is_object($retrieveDataSecondary))) {
                //do nothing
            } else {
                $retrieveDataSecondary = [];
            }
            $this->_data = $retrieveDataPrimary + $retrieveDataSecondary;
        }

        return $this->_data;
    }

    private $_processed_data = null;

    protected function processedFormData($data = null) {
        if(! $this->_processed_data) {
            if(! $data) {
                $data = $this->retrieveData();
            }
            $array = [];
            foreach($data as $key => $value) {
                if($key && $value) {
                    if(
                        strpos($key, '__KEY__') ||
                        strpos($key, '__VALUE__')
                    ) {
                        $parts = explode('__', $key);
                        if(!isset($array[$parts[0]])) {
                            $array[$parts[0]] = [];
                        }
                        if(! isset($array[$parts[0]][$parts[2]])) {
                            $array[$parts[0]][$parts[2]] = [];
                        }
                        $array[$parts[0]][$parts[2]][$parts[1]] = $value;
                    } elseif(substr($key, 0, 3) === 'can') {
                        $array[$key] = $this->MyCanMethodBuilder($key, $value);
                    } else {
                        $array[$key] = $value;
                    }
                }
            }
            foreach($array as $field => $values) {
                $alInner = \ArrayList::create();
                if(is_array($values)) {
                    foreach($values as $key => $valuePairs) {
                        if(isset($valuePairs['KEY']) && isset($valuePairs['VALUE'])) {
                            if($valuePairs['VALUE'] == 'true') {
                                $valuePairArray = [
                                    'Key' => $valuePairs['KEY'],
                                    'UnquotedValue' => $valuePairs['VALUE'],
                                ];
                            } else {
                                $valuePairArray = [
                                    'Key' => $valuePairs['KEY'],
                                    'Value' => $valuePairs['VALUE'],
                                ];
                            }
                            $alInner->push(\ArrayData::create($valuePairArray));
                        }
                    }
                    $array[$field] = $alInner;
                } else {
                    //do nothing
                }
            }
            $this->_processed_data = \ArrayData::create($array);
        }

        return $this->_processed_data;
    }


    protected function prependNullOption($source)
    {
        $source = ['' => '--- Please Select ---'] + $source;

        return $source;
    }

}
