<?php

namespace Sunnysideup\BuildDataObject\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\DataObject;
use Sunnysideup\BuildDataObject\Api\FormBuilder\InterfaceForFormController;
use Sunnysideup\BuildDataObject\View\NewDataObject;

abstract class BuildController extends Controller implements \Sunnysideup\BuildDataObject\Api\FormBuilder\InterfaceForFormController
{
    //#####################
    // ABSTRACT AND PROVIDERS
    //#####################

    protected $myBaseClass = DataObject::class;

    protected $apiProvider = \Sunnysideup\BuildDataObject\API\DataObjectLists::class;

    /**
     * @var int
     */
    protected $step = 1;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var string
     */
    protected $prevLink;

    /**
     * @var NewDataObject
     */
    protected $finalData;

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
        'debug' => true,
    ];

    //#####################
    // DEBUG STUFF
    //#####################

    private static $debug = true;

    private $_data;

    public function debug()
    {
        $this->processedFormData($this->retrieveData());
        // print_r($this->CanMethodBuilder('canEdit'));
        print_r($this->finalData);
        die('-----------------------------');
    }

    //#####################
    // STRING VARS
    //#####################

    public function Link($action = null)
    {
        if ($action) {
            $action .= '/';
        }

        return Director::baseURL() . $this->Config()->get('url_segment') .
            '/' . strtolower($this->ShortBaseClass()) .
            '/' . $action;
    }

    public function LoadTemplateLink($className = '')
    {
        return $this->Link('loadtemplate') . '?classname=' . $className;
    }

    // InterfaceForFormController implements

    public function getBaseClass(): string
    {
        return $this->myBaseClass;
    }

    public function getShortBaseClass(): string
    {
        return $this->ShortBaseClass();
    }

    public function Title()
    {
        return 'Build a ' . $this->ShortBaseClass() . ' - Step ' . $this->step . ' of 2';
    }

    public function LongBaseClass()
    {
        return $this->myBaseClass;
    }

    public function ShortThisClass()
    {
        return ClassInfo::shortName($this);
    }

    //#####################
    // ACTIONS AND FORMS
    //#####################

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
            $primaryData['Name'] = \Sunnysideup\BuildDataObject\Api\DBTypeConverter::fromClass($className)->toDropdown();
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

    public function Form()
    {
        return $this->form;
    }

    public function PrevLink()
    {
        return $this->prevLink;
    }

    public function results()
    {
        Config::modify()->set('SSViewer', 'source_file_comments', false);

        $this->processedFormData($this->retrieveData());
        $this->getFinalData()->CompileDataForRendering();

        return HTTPRequest::send_file(
            $this->renderWith($this->resultsTemplateForBuilder()),
            $this->getFinalData()->getShortClassNameForObject() . '.php'
        );
    }

    public function getFinalData()
    {
        return $this->finalData;
    }

    public function getAdditionalPrimaryFields(): array
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
                return $this->myAPI()->{$method}();
            }

            return null;
        }

        return $defaultValueIfMethodIsNull;
    }

    abstract protected function primaryThingsToBuild();

    abstract protected function secondaryThingsToBuild();

    protected function ShortBaseClass()
    {
        return ClassInfo::shortName($this->myBaseClass);
    }

    protected function turnStaticsIntoSessionData($method, $className)
    {
        $data = [];
        $thingsToBuild = $this->{$method}();
        foreach ($thingsToBuild as $static) {
            $varName = $static['Name'];
            $varValue = Config::inst()->get($className, $varName);
            if (is_array($varValue)) {
                $count = 0;
                foreach ($varValue as $varInnerKey => $varInnerValue) {
                    ++$count;
                    $data[$varName . '__KEY__' . $count] = $varInnerKey;
                    $data[$varName . '__VALUE__' . $count] = trim(preg_replace('#\\([^)]+\\)#', '', $varInnerValue));
                }
            } else {
                $data[$varName] = $varValue;
            }
        }

        return $data;
    }

    protected function createForm(string $formName, string $actionTitle): ?Form
    {
        if ('PrimaryForm' === $formName) {
            $builder = new \Sunnysideup\BuildDataObject\Api\FormBuilder\PrimaryFormBuilder($this, $this->primaryThingsToBuild());
        } elseif ('SecondaryForm' === $formName) {
            $builder = new \Sunnysideup\BuildDataObject\Api\FormBuilder\SecondaryFormBuilder($this, $this->secondaryThingsToBuild());
        } else {
            user_error('Set right form type: ' . $formName . ' is not valid');

            return null;
        }

        $finalFields = $builder->build();

        $actions = FieldList::create(
            [FormAction::create('do' . strtolower($formName), $actionTitle)]
        );

        $form = Form::create($this, $formName, $finalFields, $actions);
        $form->setFormAction($this->Link($formName));
        $form->loadDataFrom($this->retrieveData());

        return $form;
    }

    /**
     * returns an array of fields.
     *
     * @return array
     */
    protected function additionalPrimaryFields()
    {
        return [];
    }

    protected function saveData($name, $data)
    {
        unset($data['url'], $data['SecurityID']);

        if (is_array($data)) {
            foreach (array_keys($data) as $key) {
                if (0 === strpos($key, 'action_')) {
                    unset($data[$key]);
                }
            }
        }
        $var = $this->Config()->get('form_data_session_variable');
        $varName = $var . $name;
        $this->getRequest()->getSession()->clear($varName);
        $this->getRequest()->getSession()->set($varName, null);
        $this->getRequest()->getSession()->set($varName, $data);
        //$this->getRequest()->getSession()->save();
    }

    protected function retrieveData()
    {
        if (! $this->_data) {
            $var = $this->Config()->get('form_data_session_variable');
            $retrieveDataPrimary = $this->getRequest()->getSession()->get($var . '_PrimaryForm');
            if (! is_array($retrieveDataPrimary)) {
                $retrieveDataPrimary = [];
            }
            $retrieveDataSecondary = $this->getRequest()->getSession()->get($var . '_SecondaryForm');
            if (! is_array($retrieveDataSecondary)) {
                $retrieveDataSecondary = [];
            }
            $this->_data = $retrieveDataPrimary + $retrieveDataSecondary;
        }

        return $this->_data;
    }

    //######################################
    // Process results
    //######################################

    protected function processedFormData($data = null)
    {
        if (! $this->finalData) {
            if (! $data) {
                $data = $this->retrieveData();
            }
            $decomposer = new \Sunnysideup\BuildDataObject\Api\FormData\FormDataDecomposer($data);
            $this->finalData = $decomposer->toArrayData();
            $this->finalData->setBaseClassName($this->baseClass);
            //add more data ....
        }

        /// DEBUG DEBUG ///
        // self::print_r_debug($this->_processed_data);
        return $this->finalData;
    }

    protected function resultsTemplateForBuilder()
    {
        return $this->ShortThisClass() . 'Results';
    }

    //######################################
    // INFO FROM API
    //######################################

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
            'IsMultiple',
        ];
        foreach ($array as $arrayRowKey => $arrayRowValues) {
            $newArray[$arrayRowKey] = [];
            foreach ($arrayRowValues as $arrayColumnKey => $arrayColumnValue) {
                $newArray[$arrayRowKey][$fields[$arrayColumnKey]] = $arrayColumnValue;
            }
        }

        return $newArray;
    }

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
}
