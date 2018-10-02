<?php


namespace Sunnysideup\BuildDataObject\Control;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\ClassInfo;

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
use Sunnysideup\BuildDataObject\API\DBTypeConverter;
use Sunnysideup\BuildDataObject\API\FormData\FormDataDecomposer;
use Sunnysideup\BuildDataObject\API\FormBuilder\IFormBuilderOwner;
use Sunnysideup\BuildDataObject\API\FormBuilder\PrimaryFormBuilder;
use Sunnysideup\BuildDataObject\API\FormBuilder\SecondaryFormBuilder;

abstract class BuildController extends Controller implements IFormBuilderOwner
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

    private static $debug = true;

    private static function var_export_debug(&$var)
    {
        if (self::$debug) {
            /// DEBUG DEBUG ///
            echo '<!-- ';var_export($var); echo ' -->';
        }
    }

    private static function print_r_debug(&$var)
    {
        if (self::$debug) {
            /// DEBUG DEBUG ///
            echo '<!-- ';print_r($var); echo ' -->';
        }
    }

    public function Link($action = null)
    {
        if ($action) {
            $action .= '/';
        }
        return
            Director::baseURL().$this->Config()->get('url_segment').
            '/'.strtolower($this->ShortBaseClass()).
            '/'.$action;
    }

    public function LoadTemplateLink($className = '')
    {
        return $this->Link('loadtemplate').'?classname='.$className;
    }

    public function Title()
    {
        return 'Build a '.$this->ShortBaseClass().' - Step '.$this->step.' of 2';
    }

    public function LongBaseClass()
    {
        return $this->$myBaseClass;
    }

    public function ShortThisClass()
    {
        return ClassInfo::shortName($this);
    }

    public function ShortBaseClass()
    {
        return ClassInfo::shortName($this->myBaseClass);
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
            $primaryData['Name'] = DBTypeConverter::fromClass($className)->toDropdown();
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
        Config::modify()->set('SSViewer', 'source_file_comments', false);

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
        Config::modify()->set('SSViewer', 'source_file_comments', false);

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
        Config::modify()->set('SSViewer', 'source_file_comments', false);

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

        return DBField::create_field('Varchar', $str);
    }


    protected function createForm($formName, $actionTitle)
    {
        if ($formName === 'PrimaryForm') {
            $builder = new PrimaryFormBuilder($this, $this->primaryThingsToBuild());
        } elseif ($formName === 'SecondaryForm') {
            $builder = new SecondaryFormBuilder($this, $this->secondaryThingsToBuild());
        } else {
            user_error('Set right form type: '.$formName.' is not valid');
        }

        $finalFields = $builder->build();

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
        $this->getRequest()->getSession()->set($var.$name, null);
        $this->getRequest()->getSession()->set($var.$name, $data);
        //$this->getRequest()->getSession()->save();
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
            $decomposer = new FormDataDecomposer($data);
            $this->_processed_data = $decomposer->toArrayData();
        }

        /// DEBUG DEBUG ///
        // self::print_r_debug($this->_processed_data);
        return $this->_processed_data;
    }


    protected function resultsTemplateForBuilder()
    {
        return $this->ShortThisClass().'Results';
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

    // IFormBuilderOwner implements

    public function getBaseClass() : string
    {
        return $this->myBaseClass;
    }

    public function getShortBaseClass() : string
    {
        return $this->ShortBaseClass();
    }

    public function getAdditionalPrimaryFields() : array
    {
        return $this->additionalPrimaryFields();
    }

    public function getPossibleRelationsWithBaseClass()
    {
        return $this->myAPI()->PossibleRelationsWithBaseClass($this->myBaseClass);
    }

    public function callAPIMethod($method, $defaultValueIfMethodIsNull)
    {
        if ($method) {
            if ($this->myAPI()->hasMethod($method)) {
                return $this->myAPI()->$method();
            }
            return null;
        }
        return $defaultValueIfMethodIsNull;
    }
}
