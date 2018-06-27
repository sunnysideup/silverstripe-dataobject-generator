<?php


namespace Sunnysideup\BuildDataObject\Control;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Config\Config;

use SilverStripe\Control\Session;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Form;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;

use SilverStripe\View\SSViewer;
use SilverStripe\View\ArrayData;


abstract class BuildController extends Controller
{
    private static $form_data_session_variable = 'Sunnysideup\BuildDataObject\Control\Models\DataObjectBuildController';

    private static $url_segment = 'build';

    private static $allowed_actions = [
        'primaryformstart' => true,
        'PrimaryForm' => true,
        'doprimaryform' => true,
        'secondaryformstart' => true,
        'SecondaryForm' => true,
        'dosecondaryform' => true,
        'results' => true,
        'startover' => true,
        'loadtemplate' => true,
        'debug' => true
    ];

    protected $myBaseClass = DataObject::class;

    protected $apiProvider = 'Sunnysideup\BuildDataObject\API\DataObjectAPI';

    abstract protected function primaryThingsToBuild();

    abstract protected function secondaryThingsToBuild();

    public function Link($action = null)
    {
        if ($action) {
            $action .= '/';
        }
        return
            '/'.$this->Config()->get('url_segment').
            '/'.strtolower($this->ShortBaseClass()).
            '/'.$action;
    }

    public function LoadTemplateLink($className = '')
    {
        return $this->Link('loadtemplate').'?classname='.$className;
    }

    public function Title()
    {
        return 'Build a '.$this->myBaseClass.' - Step '.$this->step.' of 2';
    }

    public function LongBaseClass()
    {
        return $this->$myBaseClass;
    }

    public function ShortBaseClass()
    {
        $reflect = new \ReflectionClass($this->myBaseClass);

        return $reflect->getShortName();
    }

    public function startover()
    {
        $this->saveData('_PrimaryForm', null);
        $this->saveData('_SecondaryForm', null);
        return $this->redirect($this->link('primaryformstart'));
    }

    public function loadtemplate($request)
    {
        $className = $request->getVar('classname');
        if (class_exists($className)) {
            $obj = Injector::inst()->get($className);
            $primaryData = $this->turnStaticsIntoSessionData('primaryThingsToBuild', $className);
            $primaryData['Name'] = $className;
            $extends = get_parent_class($className);
            $primaryData['Extends'] = $extends;
            $primaryData['singular_name'] = $obj->i18n_singular_name();
            $primaryData['plural_name'] = $obj->i18n_plural_name();
            $this->saveData('_PrimaryForm', $primaryData);

            $secondaryData = $this->turnStaticsIntoSessionData('secondaryThingsToBuild', $className);
            $this->saveData('_SecondaryForm', $secondaryData);

            return $this->redirect($this->link('primaryformstart'));
        }
    }

    protected function turnStaticsIntoSessionData($method, $className)
    {
        $data = [];
        $thingsToBuild = $this->$method();
        foreach ($thingsToBuild as $static) {
            $varName = $static['Name'];
            $varValue = Config::inst()->get($className, $varName);
            if (is_array($varValue)) {
                $count = 0;
                foreach ($varValue as $varInnerKey => $varInnerValue) {
                    $count++;
                    $data[$varName.'__KEY__'.$count] = $varInnerKey;
                    $data[$varName.'__VALUE__'.$count] = trim(preg_replace("/\([^)]+\)/", "", $varInnerValue));
                }
            } else {
                $data[$varName] = $varValue;
            }
        }

        return $data;
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

    public function index()
    {
        return $this->redirect($this->Link('primaryformstart'));
    }

    public function primaryformstart()
    {
        $this->PrimaryForm();
        $this->prevLink = $this->Link('startover');
        Config::inst()->update('SSViewer', 'source_file_comments', false);

        return $this->renderWith('BuildControllerForm');
    }

    public function PrimaryForm()
    {
        $this->form = $this->createForm('PrimaryForm', 'Build Model');

        return $this->form;
    }

    public function doprimaryform($data, $form)
    {
        $this->saveData('_PrimaryForm', $data);

        return $this->redirect($this->Link('secondaryformstart'));
    }


    public function secondaryformstart()
    {
        $this->step = 2;
        $this->SecondaryForm();
        $this->prevLink = $this->Link('primaryformstart');
        Config::inst()->update('SSViewer', 'source_file_comments', false);

        return $this->renderWith('BuildControllerForm');
    }

    public function SecondaryForm()
    {
        $this->form = $this->createForm('SecondaryForm', 'Download Example Class');

        return $this->form;
    }


    public function dosecondaryform($data, $form)
    {
        $this->saveData('_SecondaryForm', $data);

        return $this->redirect($this->Link('results'));
    }

    public function results()
    {
        $this->finalData = $this->processedFormData($this->retrieveData());
        Config::inst()->update('SSViewer', 'source_file_comments', false);

        return HTTPRequest::send_file(
            $this->renderWith($this->resultsTemplateForBuilder()),
            $this->finalData->Name.'.php'
        );
    }

    public function debug()
    {
        $this->finalData = $this->processedFormData($this->retrieveData());
        print_r($this->CanMethodBuilder('canEdit'));
        print_r($this->finalData);
        die('-----------------------------');
    }

    public function Form()
    {
        return $this->form;
    }

    public function FinalData()
    {
        return $this->finalData;
    }

    public function PrevLink()
    {
        return $this->prevLink;
    }

    protected function ClassNameForObject()
    {
        if (isset($this->finalData->Name)) {
            return $this->finalData->Name;
        } else {
            return 'self::$class';
        }
    }

    public function MyCanMethodBuilder($type, $value)
    {
        if ($value === 'parent') {
            return null;
        } elseif ($value === 'one') {
            $str = 'DataObject::get_one($this->class) ? false : true;';
        } elseif ($value === 'true') {
            $str = 'true;';
        } elseif ($value === 'false') {
            $str = 'false;';
        } else {
            $str = 'Permission::check(\''.$value.'\', \'any\', $member);';
        }

        return DBField::create_field('DBVarchar', $str);
    }


    protected function createForm($formName, $actionTitle)
    {
        if ($formName === 'PrimaryForm') {
            $isPrimary = true;
            $isSecond = false;
        } elseif ($formName === 'SecondaryForm') {
            $isPrimary = false;
            $isSecond = true;
        } else {
            user_error('Set right form type: '.$formName.' is not valid');
        }

        $finalFields = FieldList::create();

        if ($isPrimary) {
            $toBuild = $this->primaryThingsToBuild();
            $possibleExtensions = $this->myAPI()->PossibleRelationsWithBaseClass($this->myBaseClass);
            $finalFields->push(HeaderField::create('BasedOnHeader', 'Based On (OPTIONAL) ...'));
            $possibleBasedOn = $possibleExtensions;
            unset($possibleBasedOn[DataObject::class]);
            $possibleBasedOn = ['' => '--- PRELOAD VALUES FROM ---'] + $possibleBasedOn;
            $finalFields->push(DropdownField::create('Template', '', $possibleBasedOn));
            $finalFields->push(HeaderField::create('NameHeader', 'Name your '.$this->myBaseClass));
            $finalFields->push(TextField::create('Name', ''));
            $finalFields->push(HeaderField::create('ExtendsHeader', 'Extends'));
            asort($possibleExtensions);
            $finalFields->push(
                DropdownField::create(
                    'Extends',
                    '',
                    $possibleExtensions
                )
            );
            $additionalFields = $this->additionalPrimaryFields();
            foreach ($additionalFields as $additionalField) {
                $finalFields->push($additionalField);
            }
        } else {
            $toBuild = $this->secondaryThingsToBuild();
        }
        $formFields = [];
        $formFieldsWithMultiple = [];

        $count = 0;
        //build fields ...
        foreach ($toBuild as $item) {
            $name = $item['Name'];
            $sourceMethod1 = $item['SourceMethod1'];
            $sourceMethod2 = $item['SourceMethod2'];
            $isMultiple = $item['IsMultiple'];


            //work out style
            $hasKeyAndValue = false;
            if ($sourceMethod1 && $sourceMethod2) {
                $hasKeyAndValue = true;
            }
            $formFields[$count] = [];
            if ($isMultiple) {
                $max = 12;
            } else {
                $max = 1;
            }
            $formFields[$count][0] = [
                $name.'_HEADER',
                HeaderField::class,
                $name
            ];


            //work out sources
            if ($sourceMethod1 && $this->myAPI()->hasMethod($sourceMethod1)) {
                $source1 = $this->myAPI()->$sourceMethod1();
            } else {
                $source1 = null;
            }
            if ($sourceMethod2 && $this->myAPI()->hasMethod($sourceMethod2)) {
                $source2 = $this->myAPI()->$sourceMethod2();
            } elseif ($sourceMethod2) {
                $source2 = null;
            } else {
                $source2 = 'ignore';
            }

            //work out field names

            for ($i = 1; $i <= $max; $i++) {
                if ($hasKeyAndValue) {
                    if ($isMultiple) {
                        $nameKey = $name.'__KEY__'.$i;
                        $nameValue = $name.'__VALUE__'.$i;
                        $formFieldsWithMultiple[$nameKey] = $nameKey;
                    } else {
                        $nameKey = $name.'__KEY__';
                        $nameValue = $name.'__VALUE__';
                    }
                } else {
                    if ($isMultiple) {
                        $nameKey = $name.$i;
                        $nameValue = '';
                        $formFieldsWithMultiple[$nameKey] = $nameKey;
                    } else {
                        $nameKey = $name;
                        $nameValue = '';
                    }
                }
                if ($hasKeyAndValue) {
                    //key field
                    if ($source1) {
                        $formFields[$count][$i]['KEY'] = [
                            $nameKey,
                            DropdownField::class,
                            $source1
                        ];
                    } else {
                        $formFields[$count][$i]['KEY'] = [
                            $nameKey,
                            TextField::class
                        ];
                    }

                    //value field
                    if ($source2) {
                        $formFields[$count][$i]['VALUE'] = [
                            $nameValue,
                            DropdownField::class,
                            $source2
                        ];
                    } else {
                        $formFields[$count][$i]['VALUE'] = [
                            $nameValue,
                            TextField::class
                        ];
                    }
                } else {
                    //keys only!
                    if ($source1) {
                        $formFields[$count][$i] = [
                            $nameKey,
                            DropdownField::class,
                            $source1
                        ];
                    } else {
                        $formFields[$count][$i] = [
                            $nameKey,
                            TextField::class
                        ];
                    }
                }
            }
            if ($i > 2) {
                $formFields[$count][$i + 1] = [
                    $name.'_ADD_'.$i,
                    LiteralField::class,
                    '
                    <div class="CompositeField add-and-remove">
                        <a href="#" class="add first-add"><i class="material-icons">add_circle_outline</i></a>
                        <a href="#" class="remove"><i class="material-icons">remove_circle_outline</i></a>
                    </div>
                    '
                ];
            }
            $count++;
        }
        //create fields ...
        $count = 0;
        foreach ($formFields as $outerCount => $subFieldList) {
            $count++;
            $compositeField = CompositeField::create();
            $compositeField->addExtraClass('OuterComposite pos'.$count);
            $innerCount = 0;
            foreach ($subFieldList as $innerCount => $fieldDetails) {
                $innerCount++;
                if (isset($fieldDetails['KEY']) && isset($fieldDetails['VALUE'])) {
                    $subCompositeField = CompositeField::create();
                    $subCompositeField->addExtraClass('InnerComposite pos'.$innerCount);
                    foreach ($fieldDetails as $fieldDetailsInner) {
                        $fieldName = $fieldDetailsInner[0];
                        $fieldType = $fieldDetailsInner[1];
                        $additionalClasses = [];
                        if (strpos($fieldName, '__KEY__')) {
                            $additionalClasses[] = 'mykey';
                        }
                        if (strpos($fieldName, '__VALUE__')) {
                            $additionalClasses[] = 'myvalue';
                        }
                        if (isset($fieldDetailsInner[2])) {
                            $source = $fieldDetailsInner[2];
                            asort($source);
                            $source = $this->prependNullOption($source);
                            $tempField = $fieldType::create($fieldName, '', $source);
                        } else {
                            $tempField = $fieldType::create($fieldName, '');
                        }
                        if (count($additionalClasses)) {
                            $classes = implode(' ', $additionalClasses);
                            $tempField->addExtraClass($classes);
                        }
                        $subCompositeField->push($tempField);
                    }
                    $compositeField->push($subCompositeField);
                } else {
                    $fieldName = $fieldDetails[0];
                    if (isset($formFieldsWithMultiple[$fieldName])) {
                        $subCompositeField = CompositeField::create();
                        $subCompositeField->addExtraClass('InnerComposite pos'.$innerCount);
                    } else {
                        $subCompositeField = null;
                    }
                    $fieldType = $fieldDetails[1];
                    if ($fieldType === DropdownField::class) {
                        $source = $fieldDetails[2];
                        asort($source);
                        $source = $this->prependNullOption($source);
                        $myTempfield = $fieldType::create($fieldName, '', $source);
                    } elseif ($fieldType === HeaderField::class) {
                        $title = str_replace('_', ' ', $fieldDetails[2]);
                        $myTempfield = $fieldType::create($fieldName, $title);
                    } elseif ($fieldType === LiteralField::class) {
                        $title = $fieldDetails[2];
                        $myTempfield = $fieldType::create($fieldName, $title);
                    } else {
                        $myTempfield = $fieldType::create($fieldName, '');
                    }
                    if ($subCompositeField) {
                        $subCompositeField->push($myTempfield);
                        $compositeField->push($subCompositeField);
                    } else {
                        $compositeField->push($myTempfield);
                    }
                }
            }
            $finalFields->push($compositeField);
        }
        $actions = FieldList::create(
            [FormAction::create('do'.strtolower($formName), $actionTitle)]
        );

        $form = Form::create($this, $formName, $finalFields, $actions);
        $form->setFormAction($this->Link($formName));
        $form->loadDataFrom($this->retrieveData());

        return $form;
    }

    /**
     * returns an array of fields
     * @return array
     */
    protected function additionalPrimaryFields()
    {
        return [];
    }

    protected function saveData($name, $data)
    {
        unset($data['url']);
        unset($data['SecurityID']);
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (strpos($key, 'action_') === 0) {
                    unset($data[$key]);
                }
            }
        }
        $var = $this->Config()->get('form_data_session_variable');
        $this->getRequest()->getSession()->clear($var.$name);
        $this->getRequest()->getSession()->save();
        $this->getRequest()->getSession()->set($var.$name, null);
        $this->getRequest()->getSession()->save();
        $this->getRequest()->getSession()->set($var.$name, $data);
        $this->getRequest()->getSession()->save();
    }

    private $_data = null;

    protected function retrieveData()
    {
        if (! $this->_data) {
            $var = $this->Config()->get('form_data_session_variable');
            $retrieveDataPrimary = $this->getRequest()->getSession()->get($var.'_PrimaryForm');
            if ($retrieveDataPrimary && (is_array($retrieveDataPrimary) || is_object($retrieveDataPrimary))) {
                //do nothing
            } else {
                $retrieveDataPrimary = [];
            }
            $retrieveDataSecondary = $this->getRequest()->getSession()->get($var.'_SecondaryForm');
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

    protected function processedFormData($data = null)
    {
        if (! $this->_processed_data) {
            if (! $data) {
                $data = $this->retrieveData();
            }
            $array = [];
            foreach ($data as $key => $value) {
                if ($key && $value) {
                    if (
                        strpos($key, '__KEY__') ||
                        strpos($key, '__VALUE__')
                    ) {
                        $parts = explode('__', $key);
                        if (!isset($array[$parts[0]])) {
                            $array[$parts[0]] = [];
                        }
                        if (! isset($array[$parts[0]][$parts[2]])) {
                            $array[$parts[0]][$parts[2]] = [];
                        }
                        $array[$parts[0]][$parts[2]][$parts[1]] = $value;
                    } elseif (substr($key, 0, 3) === 'can') {
                        $array[$key] = $this->MyCanMethodBuilder($key, $value);
                    } else {
                        $array[$key] = $value;
                    }
                }
            }
            foreach ($array as $field => $values) {
                $alInner = ArrayList::create();
                if (is_array($values)) {
                    foreach ($values as $key => $valuePairs) {
                        if (isset($valuePairs['KEY']) && isset($valuePairs['VALUE'])) {
                            if ($valuePairs['VALUE'] == 'true') {
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
                            $alInner->push(ArrayData::create($valuePairArray));
                        }
                    }
                    $array[$field] = $alInner;
                } else {
                    //do nothing
                }
            }
            $this->_processed_data = ArrayData::create($array);
        }

        return $this->_processed_data;
    }


    protected function resultsTemplateForBuilder()
    {
        return str_replace(__NAMESPACE__ .'\\', '', $this->class).'Results';
    }


    protected function prependNullOption($source)
    {
        $source = ['' => '--- Please Select ---'] + $source;

        return $source;
    }


    protected function myAPI()
    {
        $class = $this->apiProvider;

        return $class::inst($this->myBaseClass, $this->processedFormData());
    }

    protected function addKeysToThingsToBuild($array)
    {
        $newArray = [];
        $fields = [
            'Name',
            'SourceMethod1',
            'SourceMethod2',
            'IsMultiple'
        ];
        foreach ($array as $arrayRowKey => $arrayRowValues) {
            $newArray[$arrayRowKey] = [];
            foreach ($arrayRowValues as $arrayColumnKey => $arrayColumnValue) {
                $newArray[$arrayRowKey][$fields[$arrayColumnKey]] = $arrayColumnValue;
            }
        }

        return $newArray;
    }
}
