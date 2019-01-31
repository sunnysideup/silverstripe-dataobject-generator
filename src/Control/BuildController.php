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
use Sunnysideup\BuildDataObject\Api\DBTypeConverter;
use Sunnysideup\BuildDataObject\Api\FormData\FormDataDecomposer;
use Sunnysideup\BuildDataObject\Api\FormBuilder\InterfaceForFormController;
use Sunnysideup\BuildDataObject\Api\FormBuilder\PrimaryFormBuilder;
use Sunnysideup\BuildDataObject\Api\FormBuilder\SecondaryFormBuilder;

use Sunnysideup\BuildDataObject\Api\DataObjectLists;

abstract class BuildController extends Controller implements InterfaceForFormController
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







    ######################
    # ABSTRACT AND PROVIDERS
    ######################

    protected $myBaseClass = DataObject::class;

    protected $apiProvider = DataObjectLists::class;

    abstract protected function primaryThingsToBuild();

    abstract protected function secondaryThingsToBuild();










    ######################
    # DEBUG STUFF
    ######################

    private static $debug = true;

    private static function var_export_debug(&$var)
    {
        if (self::$debug) {
            /// DEBUG DEBUG ///
            echo '<!-- ';
            var_export($var);
            echo ' -->';
        }
    }

    private static function print_r_debug(&$var)
    {
        if (self::$debug) {
            /// DEBUG DEBUG ///
            echo '<!-- ';
            print_r($var);
            echo ' -->';
        }
    }

    public function debug()
    {
        $this->processedFormData($this->retrieveData());
        // print_r($this->CanMethodBuilder('canEdit'));
        echo '<pre>';
        print_r($this->finalData);
        echo '</pre>';
        die('<hr />');
    }





    ######################
    # STRING VARS
    ######################

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


    // InterfaceForFormController implements

    public function getBaseClass() : string
    {
        return $this->myBaseClass;
    }

    public function getShortBaseClass() : string
    {
        return $this->ShortBaseClass();
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


    protected function ShortBaseClass()
    {
        return ClassInfo::shortName($this->myBaseClass);
    }







    ######################
    # ACTIONS AND FORMS
    ######################

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
            $primaryData['default_classname'] = $obj->config()->get('default_classname');
            $primaryData['api_access'] = $obj->config()->get('api_access');
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
        $keyIndex =
            FormDataDecomposer::EXP_CHAR.
            FormDataDecomposer::KEY_IDENTIFIER.
            FormDataDecomposer::EXP_CHAR;
        $valIndex =
            FormDataDecomposer::EXP_CHAR.
            FormDataDecomposer::VALUE_IDENTIFIER.
            FormDataDecomposer::EXP_CHAR;
        $listIndex =
            FormDataDecomposer::EXP_CHAR.
            FormDataDecomposer::LIST_IDENTIFIER.
            FormDataDecomposer::EXP_CHAR;
        foreach ($thingsToBuild as $static) {
            $varName = $static['Name'];
            $varValue = Config::inst()->get($className, $varName);
            if (is_array($varValue)) {
                if ($this->isAssoc($varValue)) {
                    $count = 0;
                    foreach ($varValue as $varInnerKey => $varInnerValue) {
                        if (is_array($varInnerValue)) {
                            //we will ignore these values for now
                        } else {
                            $count++;
                            $data[$varName.$keyIndex.$count] = $varInnerKey;
                            $data[$varName.$valIndex.$count] = trim(preg_replace("/\([^)]+\)/", "", $varInnerValue));
                        }
                    }
                } else {
                    $count = 0;
                    foreach ($varValue as $varInnerKey => $varInnerValue) {
                        if (is_array($varInnerValue)) {
                            //we will ignore these values for now
                        } else {
                            $count++;
                            $data[$varName.$listIndex.$count] = trim(preg_replace("/\([^)]+\)/", "", $varInnerValue));
                        }
                    }
                }
            } else {
                $data[$varName] = $varValue;
            }
        }

        return $data;
    }

    protected function isAssoc(array $arr)
    {
        if (array() === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
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
        Config::modify()->set(SSViewer::class, 'source_file_comments', false);

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
        Config::modify()->set(SSViewer::class, 'source_file_comments', false);

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


    public function Form()
    {
        return $this->form;
    }

    public function PrevLink()
    {
        return $this->prevLink;
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
        $varName = $var.$name;
        $this->getRequest()->getSession()->clear($varName);
        $this->getRequest()->getSession()->set($varName, null);
        $this->getRequest()->getSession()->set($varName, $data);
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


    public function results()
    {
        Config::modify()->set(SSViewer::class, 'source_file_comments', false);

        $this->processedFormData($this->retrieveData());
        $this->getFinalData()->CompileDataForRendering();
        return HTTPRequest::send_file(
            $this->renderWith($this->resultsTemplateForBuilder()),
            $this->getFinalData()->getShortClassNameForObject().'.php'
        );
    }







    #######################################
    # Process results
    #######################################


    protected function processedFormData($data = null)
    {
        if (! $this->finalData) {
            if (! $data) {
                $data = $this->retrieveData();
            }
            $decomposer = new FormDataDecomposer($data);
            $this->finalData = $decomposer->toArrayData();
            $this->finalData->setBaseClassName($this->baseClass);
            //add more data ....
        }

        /// DEBUG DEBUG ///
        // self::print_r_debug($this->_processed_data);
        return $this->finalData;
    }


    public function getFinalData()
    {
        return $this->finalData;
    }


    protected function resultsTemplateForBuilder()
    {
        return $this->ShortThisClass().'Results';
    }







    #######################################
    # INFO FROM API
    #######################################


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
