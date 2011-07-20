<?php

class WepController extends Zend_Controller_Action
{

    //    protected $activity_id = '';
    public function init()
    {
        $this->_helper->layout()->setLayout('layout_wep');

        //        $this->view->blockManager()->enable('partial/dashboard.phtml');
        /* $contextSwitch = $this->_helper->contextSwitch;
        $contextSwitch->addActionContext('', 'json')
        ->initContext('json'); */
    }

    public function indexAction()
    {
        //$this->view->blockManager()->disable('partial/dashboard.phtml');
        $this->view->blockManager()->enable('partial/login.phtml');
    }

    public function dashboardAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $model = new Model_Wep();

        $activities_id = $model->listAll('iati_activities', 'account_id', $identity->account_id);
        if (empty($activities_id)) {
            $data['@version'] = '01';
            $data['@generated_datetime'] = date('Y-m-d H:i:s');
            $data['user_id'] = $identity->user_id;
            $data['account_id'] = $identity->account_id;
            $data['unqid'] = uniqid();
            $activities_id = $model->insertRowsToTable('iati_activities', $data);
        } else {
            $activities_id = $activities_id[0]['id'];
        }

        $this->view->blockManager()->enable('partial/dashboard.phtml');
        $this->view->activities_id = $activities_id;
    }

    public function listActivitiesAction()
    {
        $this->view->blockManager()->enable('partial/dashboard.phtml');
        //@todo list only activities related to the user
        if ($_GET) {
            if ($this->getRequest()->getParam('type')) {
                $tblName = $this->getRequest()->getParam('type');
            }
            /* if($identity->role == 'admin'){
             $field = 'account_id';
             }
             else{
             $field = 'user_id';
             }

             $field_data = $this->getRequest()->getParam('id'); */
            if ($this->getRequest()->getParam('account_id')) {
                $field = 'account_id';
                //print $field;exit();
                $id = $this->getRequest()->getParam('account_id');
            }
            if ($this->getRequest()->getParam('user_id')) {
                $field = 'user_id';
                $id = $this->getRequest()->getParam('user_id');
            }

            $model = new Model_Wep();
            $rowSet = $model->listAll($tblName, $field, $id);
            $this->view->rowSet = $rowSet;
        }

    }

    /* public function formAction(){
     $data = array('xmllang' => array('input'=>'Text', 'table'=>'Language'),
     '@default-currency'=> array('input' => 'Text', 'table' =>'Currency'),
     '@hierarchy' => array('input'=>'Text'), '@last-updated-datetime' => array('input'=>'Text'));
     $form = new Form_Wep_Createform();
     $form->create($data);
     //        print "ddd";
     $this->view->form = $form;

     } */

    public function registerAction()
    {
        $defaultFieldsValues = new Iati_WEP_AccountDefaultFieldValues();
        $default['field_values'] = $defaultFieldsValues->getDefaultFields();
        $defaultFieldGroup = new Iati_WEP_AccountDisplayFieldGroup();
        $default['fields'] = $defaultFieldGroup->getProperties();
        $form = new Form_Wep_Accountregister();
        $form->add($default);
        /**/;
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getPost();
                $model = new Model_Wep();
                $result = $model->getRowsByFields('account', 'username', $data['organisation_username']);
                //                print_r($a);exit()
                //                $result = $tbl->checkUnique($email);
                if (!$form->isValid($data)) {
                    $form->populate($data);
                }
                //@todo check for unique username. fix the bug
                else if (!empty($result)) {
                    $this->_helper->FlashMessenger->addMessage(array('error' => "Username already exists."));
                    $form->populate($data);
                    //$this->_redirect('wep/register');
                } else {

                    //@todo send email notification to super admin

                    /* $mailerParams = array('email'=> 'manisha@yipl.com.np');
                     $toEmail = 'manisha@yipl.com.np';
                     $template = 'user-register';
                     $Wep = new App_Notification;
                     $Wep->sendemail($mailerParams,$toEmail,$template); */

                    //                    print_r($_POST);exit();
                    $account['name'] = $data['organisation_name'];

                    $account['address'] = $data['organisation_address'];
                    $account['username'] = $data['organisation_username'];
                    $account['uniqid'] = md5(date('Y-m-d H:i:s'));
                    $account_id = $model->insertRowsToTable('account', $account);

                    $user['user_name'] = trim($data['organisation_username']) . "_admin";
                    $user['password'] = md5($data['password']);
                    $user['role_id'] = 1;
                    $user['email'] = $data['email'];
                    $user['account_id'] = $account_id;
                    $user['status'] = 0;
                    //@todo make the status of the user "0"
                    $user_id = $model->insertRowsToTable('user', $user);

                    $admin['first_name'] = $data['first_name'];
                    $admin['middle_name'] = $data['middle_name'];
                    $admin['last_name'] = $data['last_name'];
                    $admin['user_id'] = $user_id;
                    $admin_id = $model->insertRowsToTable('profile', $admin);

                    $defaultFieldsValues->setLanguage($data['default_language']);
                    $defaultFieldsValues->setCurrency($data['default_currency']);
                    $defaultFieldsValues->setReporting_org($data['default_reporting_org']);
                    $fieldString = serialize($defaultFieldsValues);
                    $defaultValues['object'] = $fieldString;
                    $defaultValues['account_id'] = $account_id;
                    $defaultValuesId = $model->insertRowsToTable('default_field_values', $defaultValues);
                    $i = 0;
                    foreach ($data['default_fields'] as $eachField) {
                        $defaultKey[$i] = $eachField;
                        $defaultFieldGroup->setProperties($eachField);
                        $i++;
                    }

                    $fieldString = serialize($defaultFieldGroup);
                    $defaultFields['object'] = $fieldString;
                    $defaultFields['account_id'] = $account_id;
                    $defaultFieldId = $model->insertRowsToTable('default_field_groups', $defaultFields);

                    $privilegeFields['resource'] = serialize($defaultKey);
                    $privilegeFields['owner_id'] = $user_id;
                    $privilegeFieldId = $model->insertRowsToTable('Privilege', $privilegeFields);

                    $this->_helper->FlashMessenger->addMessage(array('message' => "Account successfully registered."));
                    $this->_redirect('user/user/login');
                }
            } catch (Exception $e) {

            }
        }
        $this->view->form = $form;
        $this->view->blockManager()->enable('partial/login.phtml');
    }

    public function editDefaultsAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $model = new Model_Wep();
        $defaultFieldsValues = $model->getDefaults('default_field_values', 'account_id', $identity->account_id);
        $default['field_values'] = $defaultFieldsValues->getDefaultFields();
        $defaultFieldGroup = $model->getDefaults('default_field_groups', 'account_id', $identity->account_id);
        $default['fields'] = $defaultFieldGroup->getProperties();
        $form = new Form_Wep_EditDefaults();
        $form->edit($default);
        if ($_POST) {
            try {
                $data = $this->getRequest()->getPost();
                if (!$form->isValid($data)) {
                    $form->populate($data);
                } else {
                    $defaultFieldsValuesObj = new Iati_WEP_AccountDefaultFieldValues();
                    $defaultFieldGroupObj = new Iati_WEP_AccountDisplayFieldGroup();

                    $defaultFieldsValuesObj->setLanguage($data['default_language']);
                    $defaultFieldsValuesObj->setCurrency($data['default_currency']);
                    $defaultFieldsValuesObj->setReporting_org($data['default_reporting_org']);
                    $fieldString = serialize($defaultFieldsValuesObj);
                    $defaultValues['id'] = $model->getIdByField('default_field_values', 'account_id', $identity->account_id);
                    $defaultValues['object'] = $fieldString;
                    //                    $defaultValues['account_id'] = $identity->account_id;
                    $defaultValuesId = $model->updateRowsToTable('default_field_values', $defaultValues);

                    foreach ($data['default_fields'] as $eachField) {
                        $defaultFieldGroupObj->setProperties($eachField);
                    }

                    $fieldString = serialize($defaultFieldGroupObj);
                    $defaultFields['id'] = $model->getIdByField('default_field_groups', 'account_id', $identity->account_id);
                    $defaultFields['object'] = $fieldString;
                    //                    $defaultFields['account_id'] = $identity->account_id;
                    $defaultFieldId = $model->updateRowsToTable('default_field_groups', $defaultFields);

                    $this->_helper->FlashMessenger->addMessage(array('message' => "Defaults successfully updated."));
                    if ($identity->role == 'superadmin') {
                        $this->_redirect('admin/dashboard');
                    } else if ($identity->role == 'admin') {
                        $this->_redirect('wep/dashboard');
                    }
                }
                //            print_r($_POST);exit();
            } catch (Exception $e) {
                print $e;
            }
        }
        $this->view->blockManager()->enable('partial/dashboard.phtml');
        $this->view->form = $form;
    }

    public function addActivitiesAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $form = new Form_Wep_IatiActivities();
        $form->add();
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getPost();
                if (!$form->isValid($data)) {
                    $form->populate($data);
                } else {
                    $wepModel = new Model_Wep();

                    $data1['@version'] = $this->_request->getParam('version');
                    $data1['@generated_datetime'] = $this->_request->getParam('generated_datetime');
                    $data1['unqid'] = uniqid();
                    $data1['user_id'] = $identity->user_id;
                    $data1['account_id'] = $identity->account_id;

                    $activities_id = $wepModel->insertRowsToTable('iati_activities', $data1);
                    $this->_helper->FlashMessenger->addMessage(array('message' => "Activities Saved."));

                    $this->_redirect('wep/list-activities?account_id=' . $identity->account_id . '&type=iati_activities');
                }
            } catch (Exception $e) {
                print $e;
            }
        }

        $this->view->form = $form;
        $this->view->blockManager()->enable('partial/dashboard.phtml');
    }

    public function addActivityAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($_GET) {
            $activities_id = $this->getRequest()->getParam('activities_id');
        }
        $form = new Form_Wep_IatiActivity();
        $form->add(null, $activities_id, $identity->account_id);

        if ($_POST) {
            try {
                $data = $this->getRequest()->getPost();
                if (!$form->isValid($data)) {
                    $form->populate($data);
                } else {
                    $data1['@xml_lang'] = $this->_request->getParam('xml_lang');
                    $data1['@default_currency'] = $this->_request->getParam('default_currency');
                    $data1['@last_updated_datetime'] = date('Y-m-d H:i:s');
                    $data1['activities_id'] = $this->_request->getParam('activities_id');
                    //                    print_r($data1);exit();
                    $wepModel = new Model_Wep();
                    $activity_id = $wepModel->insertRowsToTable('iati_activity', $data1);
                    $this->_helper->FlashMessenger->addMessage(array('message' => "Activity inserted."));

                    $this->_redirect('wep/edit-activity-elements?activity_id=' . $activity_id);
                }
            } catch (Exception $e) {
                print $e;
            }
        }
        $this->view->form = $form;
        $this->view->blockManager()->enable('partial/dashboard.phtml');
    }

    public function getInitialValues($activity_id)
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $model = new Model_Wep();
        $defaultFieldValues = $model->getDefaults('default_field_values', 'account_id', $identity->account_id);
        $defaults = $defaultFieldValues->getDefaultFields();
        $initial['@currency'] = $defaults['currency'];
        $initial['@xml_lang'] = $defaults['language'];
        $initial['text'] = '';
        if ($class == 'ReportingOrganisation') {
            $initial['text'] = $defaults['reporting_org'];
        }
        return $initial;
    }

    public function createGlobalObject($activity_id, $class)
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $classname = 'Iati_WEP_Activity_Elements_'. $class;
        $globalobj = new $classname();
        //        $globalobj->setAccountActivity(array('account_id'=>$identity->account_id, 'activity_id'=>$activity_id));
        $globalobj->propertySetter(array('activity_id' => $activity_id));
        return $globalobj;
    }

    public function addToRegistry($object, $parent = NULL)
    {
        $registryTree = Iati_WEP_TreeRegistry::getInstance();
        $registryTree->addNode($object, $parent);
        return $registryTree;
    }

    public function addActivityElementsAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $id = null;
        if ($_GET['class']) {
            $class = $this->_request->getParam('class');
        }
        if ($_GET['activity_id']) {
            $activity_id = $this->_request->getParam('activity_id');
        }
        $model = new Model_Wep();
        $initial = $this->getInitialValues($activity_id);
        $classname = 'Iati_WEP_Activity_' . $class;

        if ($_POST) {
            $errorFlag = false;
            if (count(array_filter($_POST, 'is_array')) <= 0) {
                foreach ($_POST as $key => $eachPost) {
                    $array[$key] = array($eachPost);
                }
                $_POST = $array;
            }
            $flatPostArray = $this->_flatPostArray($_POST);
            $globalobj = $this->createGlobalObject($activity_id, $class);
            $registryTree = Iati_WEP_TreeRegistry::getInstance();
            $registryTree->addNode($globalobj, $parent);
            $firstPost = array_shift($flatPostArray);
            foreach ($flatPostArray as $eachArray) {
                $newObj = new $classname($eachArray['title_id']);
                $newObj->propertySetter($eachArray, $eachArray['title_id']);
                $newObj->setHtml();
                $newObj->validate();
                if ($newObj->hasErrors()) {
                    $newObj->setHtml();
                    $errorFlag = true;
                }
                $registryTree->addNode($newObj, $globalobj);
            }
            if (!$errorFlag) {
                foreach ($registryTree->getChildNodes($globalobj) as $eachObj) {
                    $eachObj->insert();
                }
                $this->_helper->FlashMessenger->addMessage(array('message' => "$class successfully inserted."));
                $this->_redirect("wep/edit-activity-elements?activity_id=$activity_id");
            } else {
                $formObj = new Iati_WEP_FormHelper($globalobj);
                $a = $formObj->getForm();
            }
        } else {
            $globalobj = $this->createGlobalObject($activity_id, $class);

            $registryTree = Iati_WEP_TreeRegistry::getInstance();
            $registryTree->addNode($globalobj, $parent);
            $obj = new $classname();
            $obj->propertySetter($initial);
            $obj->setHtml();
            $registryTree->addNode($obj, $globalobj);

            $formObj = new Iati_WEP_FormHelper($globalobj);
            $a = $formObj->getForm();
        }


        $this->view->form = $a;

        $this->view->blockManager()->enable('partial/dashboard.phtml');
        $this->view->blockManager()->enable('partial/activitymenu.phtml');
    }

    function flatArray ($array) {
        $result = array();
        $global = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                array_push($result, recurArray($key, $val, array()));
            }
            else {
                array_push($global, array($key => $val));
            }
        }
        $final = arrayMerge($result[0], $result[1]);
        
        for ($i = 2; $i < sizeof($result); $i++) {
            $final = arrayMerge($final, $result[i]);
        }
//        print_r($final);
    }

    /**
     * Actual recursion happens here
     *
     */
    function recurArray ($key, $arr, $array) {

        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                $array[$k] = recurArray($key, $v, array());
            }
        }
        else {
            return array($key => $arr);
        }

        return $array;
    }

    /**
     * Merges the array elements
     * array must be identical in shape and size
     *
     *
     */
    function arrayMerge ($arr1, $arr2) {
        foreach ($arr1 as $key => $val) {
            if (is_array($val)) {
                $arr1[$key] = arrayMerge($val, $arr2[$key]);
            }
            else {
                list($k, $v) = each($arr2);
                $arr1[$k] = $v;
                return $arr1;
            }
        }
        return $arr1;
    }

    public function editActivityElementsAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $model = new Model_Wep();

        $id = null;
        if ($_GET['class']) {
            $class = $this->_request->getParam('class');
        }
        if ($_GET['activity_id']) {
            $activity_id = $this->_request->getParam('activity_id');
            $activity_info = $model->listAll('iati_activity', 'id', $activity_id);
            if (empty($activity_info)) {
                //@todo
            }
            $activity = $activity_info[0];
            $activity['@xml_lang'] = $model->fetchValueById('Language', $activity_info[0]['@xml_lang'], 'Code');
            $activity['@default_currency'] = $model->fetchValueById('Currency', $activity_info[0]['@default_currency'], 'Code');
        }
        $this->view->activityInfo = $activity;
        $initial = $this->getInitialValues($activity_id, $class);
        $classname = 'Iati_WEP_Activity_'. $class . 'Factory';
        if(isset($class)){
            if($_POST){
                //                print_r($_POST);exit;
                $activity = new Iati_WEP_Activity_Elements_Activity();
                $activity->setAttributes(array('activity_id' => $activity_id));

                $dbWrapper = new Iati_WEP_Activity_DbWrapper($activity);
                $registryTree = Iati_WEP_TreeRegistry::getInstance();
                $registryTree->addNode($dbWrapper);

                $factory = new $classname();
                $factory->setInitialValues($_POST);
                $tree = $factory->factory($class);


            }
            else{
                $activity = new Iati_WEP_Activity_Elements_Activity();
                $activity->setAttributes(array('activity_id' => $activity_id));

                $dbWrapper = new Iati_WEP_Activity_DbWrapper($activity);
                $registryTree = Iati_WEP_TreeRegistry::getInstance();
                $registryTree->addNode($dbWrapper);

                $factory = new $classname();
                $factory->setInitialValues($initial);
                $tree = $factory->factory($class);

                $formHelper = new Iati_WEP_FormHelper();
                $a = $formHelper->getForm();

            }
        }
        $this->_helper->layout()->setLayout('layout_wep');
        $this->view->blockManager()->enable('partial/dashboard.phtml');
        $this->view->blockManager()->enable('partial/activitymenu.phtml');
         
        $this->view->form = $a;
    }

    public function editActivityElementsAction1()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $model = new Model_Wep();
        $id = null;
        if($_GET['class']){
            $class = $this->_request->getParam('class');
        }
        if($_GET['activity_id']){
            $activity_id = $this->_request->getParam('activity_id');
            $activity_info = $model->listAll('iati_activity', 'id', $activity_id);
            if(empty($activity_info)){
                //@todo
            }
            $activity = $activity_info[0];
            $activity['@xml_lang'] = $model->fetchValueById('Language', $activity_info[0]['@xml_lang'], 'Code');
            $activity['@default_currency'] = $model->fetchValueById('Currency', $activity_info[0]['@default_currency'], 'Code');
        }
        $this->view->activityInfo = $activity;
        $initial = $this->getInitialValues($activity_id, $class);
        $classname = 'Iati_WEP_Activity_'. $class;
        if($class){
            if($_POST){
                if(count(array_filter($_POST,'is_array')) <= 0){
                    foreach($_POST as $key => $eachPost){
                        $array[$key] = array($eachPost);
                    }
                    $_POST = $array;
                }
                $errorFlag = false;
                $flatPostArray = $this->_flatPostArray($_POST);

                $globalobj = $this->createGlobalObject($activity_id, $class);

                $registryTree = Iati_WEP_TreeRegistry::getInstance();
                $registryTree->addNode($globalobj);

                $firstObj = array_shift($flatPostArray);
                foreach ($flatPostArray as $eachArray) {
                    $newObj = new $classname($eachArray['title_id']);
                    $newObj->propertySetter($eachArray, $eachArray['title_id']);
                    $newObj->setHtml();
                    $newObj->validate();

                    if($newObj->hasErrors()){
                        $newObj->setHtml();
                        $errorFlag = true;
                    }
                    $registryTree->addNode($newObj, $globalobj);
                }
                if (!$errorFlag) {
                    foreach ($registryTree->getChildNodes($globalobj) as $eachObj) {
                        if ($eachObj->getTitleId() != 0) {
                            $eachObj->update();
                        } else {
                            $eachObj->insert();
                        }
                    }
                    $this->_helper->FlashMessenger->addMessage(array('message' => "$class successfully inserted."));
                    $this->_redirect("wep/edit-activity-elements/?activity_id=".$activity_id."&class=".$class);

                }
                else{
                    $formObj = new Iati_WEP_FormHelper($globalobj);
                    $a = $formObj->getForm();
                }
            } else {
                $globalobj = $this->createGlobalObject($activity_id, $class);
                $registryTree = Iati_WEP_TreeRegistry::getInstance();
                $registryTree->addNode($globalobj);
                $rowSet = $globalobj->retrieve($activity_id);
                 

                if (empty($rowSet)) {
                    $this->_helper->FlashMessenger->addMessage(array('message' => "$class not found for this activity. Please add $class"));
                    $this->_redirect("wep/add-activity-elements/?activity_id=" . $activity_id . "&class=" . $class);
                }
                foreach ($rowSet as $eachArray) {
                    $obj = new $classname();
                    $eachArray['title_id'] = $eachArray['id'];
                    $obj->propertySetter($eachArray, $eachArray['title_id']);
                    $obj->setHtml();
                    $registryTree->addNode($obj, $globalobj);
                }
                $formObj = new Iati_WEP_FormHelper($globalobj);
                $a = $formObj->getForm();
            }
        }
        $this->_helper->layout()->setLayout('layout_wep');
        $this->view->blockManager()->enable('partial/dashboard.phtml');
        $this->view->blockManager()->enable('partial/activitymenu.phtml');

        $this->view->form = $a;
    }

    public function viewActivitiesAction()
    {
        //        print_r("sag");exit();
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($_GET) {
            $activities_id = $this->getRequest()->getParam('activities_id');
            $wepModel = new Model_Wep();
            $activities = $wepModel->listAll('iati_activities', 'id', $activities_id);

            $this->view->activities_info = $activities_info[0];

            $activityArray = $wepModel->listAll('iati_activity', 'activities_id', $activities_id);

            //foreach activity get activity title
            $wepModel = new Model_Wep();
            $titleArray = array();
            if ($activityArray) {
                $i = 0;
                foreach($activityArray as $key=>$activity){

                    $title = $wepModel->listAll('iati_title', 'activity_id', $activity['id']);
                    //                    print_r($title[0]['text']);exit;
                    $activity_array[$i]['title'] = ($title[0]['text'])?$title[0]['text']:'No title';
                    $activity_array[$i]['last_updated_datetime'] = $activity['@last_updated_datetime'];
                    $activity_array[$i]['id'] = $activity['id'];
                    $i++;
                }
            }

            /* $i = 0;
             foreach($activityArray as $key=> $eachActivity){
             $xml_lang = $wepModel->getCode('Language', $eachActivity['@xml_lang'], '1');
             $a[$i]['id'] = $eachActivity['id'];
             $a[$i]['@xml_lang'] = $xml_lang[0][0]['Code'];
             $currency = $wepModel->getCode('Currency', $eachActivity['@default_currency'], '1');
             $a[$i]['@default_currency'] = $currency[0][0]['Code'];
             $a[$i]['@hierarchy'] = $eachActivity['@hierarchy'];
             $a[$i]['@last_updated_datetime'] = $eachActivity['@last_updated_datetime'];
             $a[$i]['activities_id'] = $eachActivity['activities_id'];
             $i++;
             }*/

            $this->view->blockManager()->enable('partial/dashboard.phtml');
            $this->view->activity_array = $activity_array;
        }
    }

    public function editActivityAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($_GET) {
            $activity_id = $this->getRequest()->getParam('activity_id');
            $wepModel = new Model_Wep();
            $activity_info = $wepModel->listAll('iati_activity', 'id', $activity_id);
            $activity['xml_lang'] = $activity_info[0]['@xml_lang'];
            $activity['default_currency'] = $activity_info[0]['@default_currency'];
            $activity['hierarchy'] = $activity_info[0]['@hierarchy'];
            $activity['last_updated_datetime'] = $activity_info[0]['@last_updated_datetime'];
            $activity['activities_id'] = $activity_info[0]['activities_id'];
            //            print_r($activity);exit();
            $form = new Form_Wep_IatiActivity();
            $form->add('edit', $activity_info[0]['activities_id'], $identity->account_id);

            if ($this->getRequest()->isPost()) {
                $formData = $this->getRequest()->getPost();
                if (!$form->isValid($formData)) {

                    $form->populate($formData);
                } else {
                    //                    print $activity_info[0]['id'];exit;
                    //                    print_r($formData);exit();
                    $data['id'] = $activity_info[0]['id'];
                    $data['@xml_lang'] = $formData['xml_lang'];
                    $data['@default_currency'] = $formData['default_currency'];
                    $data['@hierarchy'] = $formData['hierarchy'];
                    $data['@last_updated_datetime'] = date('Y-m-d H:i:s');
                    $data['activities_id'] = $formData['activities_id'];
                    $activity_id = $wepModel->updateRowsToTable('iati_activity', $data);

                    $this->_redirect('wep/view-activities/?activities_id=' . $data['activities_id']);
                }//end of inner if
            } else {
                $form->populate($activity);
            }

            $this->view->form = $form;

            /* $default_fields_group = $wepModel->getDefaults('display_field_groups', 'account_id', $identity->account_id);

            $defaultFields = $default_fields_group->getProperties(); */
            //$activityArray = $wepModel->listAll('iati_activity', 'activities_id', $activities_id);
            //            print_r($activityArray);
            //$this->view->activity_array = $activityArray;
        }
        $this->view->blockManager()->enable('partial/dashboard.phtml');
    }

    public function removeElementsAction()
    {
        if($this->_request->isGet()){
            try{
                //                print_r($this->_request->getParam('class'));exit;
                $id = $this->_request->getParam('id');
                $string = 'Iati_WEP_Activity_' . $this->_request->getParam('class');
                $obj = new $string();
                $class = $obj->getTableName();
                $model = new Model_Wep();
                $model->deleteRowById($id, $class);
                print 'success';
                exit();
            } catch (Exception $e) {
                print $e;
                exit();
            }
            catch(Exception $e){
                print $e; exit();
            }

        }
        else{
            //            print "dd";exit();
        }
    }

    public function activitybarAction()
    {

        if ($_GET) {
            print_r($_GET); //exit();
        }
        if ($_POST) {
            print_r($_POST);
            exit();
        }

        /* $identity = Zend_Auth::getInstance()->getIdentity();
         $form1 = new Form_Wep_IatiActivities();
         $form1->add();
         $form2 = new Form_Wep_IatiActivity();
         $form2->add(null, $identity->account_id); */
        /*  $this->_helper->layout()->setLayout('layout_wep');
         $this->view->blockManager()->enable('partial/activitymenu.phtml');
         */


        //        print_r($selectedDefaultField);//exit();
        //        $this->view->menu = $selectedDefaultField;
    }

    public function deleteAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($_GET) {
            print_r($_GET);
            exit();
            $id = $this->_request->getParam('id');
            $type = $this->_request->getParam('type');
            $model = new Model_Wep();
            $model->delete($id, $type);
            //@todo delete all the activity and the elements
            $this->_helper->FlashMessenger->addMessage(array('message' => "Activities Saved."));

            $this->_redirect('wep/list-activities?account_id=' . $identity->account_id . '&type=iati_activities');
        }
    }

    public function formAction()
    {
        //        print "dfasf";exit();
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($_GET) {
            $name = $this->getRequest()->getParam('name');
            $string = 'Form_Wep_' . $name;
            $form = new $string();
            $form->add('', $identity->account_id);

            if ($_POST) {
                //                print_r($_POST);exit();
            }
            $this->view->form = $form;
        }

        $this->_helper->layout()->setLayout('layout_wep');
        $this->view->blockManager()->enable('partial/activitymenu.phtml');
    }

    public function testAction()
    {

    }

}