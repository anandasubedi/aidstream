<?php

class App_Acl extends Zend_Acl
{

    public function __construct()
    {
        $this->add(new Zend_Acl_Resource('default'))
                ->add(new Zend_Acl_Resource('default:error'), 'default')
                ->add(new Zend_Acl_Resource('default:index'), 'default')
                ->add(new Zend_Acl_Resource('default:code-list'), 'default')
                ->add(new Zend_Acl_Resource('default:form'), 'default')
                ->add(new Zend_Acl_Resource('default:addelement'), 'default')
                ->add(new Zend_Acl_Resource('default:iati'), 'default')
                ->add(new Zend_Acl_Resource('default:iatixmlcompliance'), 'default')
                ->add(new Zend_Acl_Resource('default:crstoiati'), 'default')
                ->add(new Zend_Acl_Resource('default:api'), 'default')
                ->add(new Zend_Acl_Resource('default:activityviewer'), 'default')
                ->add(new Zend_Acl_Resource('default:wep'), 'default')
                ->add(new Zend_Acl_Resource('default:admin'), 'default')
                ->add(new Zend_Acl_Resource('default:iatixmlController'), 'default');

        $this->add(new Zend_Acl_Resource('nullresources'));



        $this->add(new Zend_Acl_Resource('user'))
                ->add(new Zend_Acl_Resource('user:user'), 'user');
        //user controller of user module has been inherited from user module


        $this->addRole(new Zend_Acl_Role('guest'));
        $this->addRole(new Zend_Acl_Role('user'), 'guest');
        $this->addRole(new Zend_Acl_Role('admin'), 'user');
        $this->addRole(new Zend_Acl_Role('superadmin'), 'admin');



        $this->allow('guest', 'user:user', 'login');
        $this->deny('guest', 'user:user', 'register');
        $this->allow('guest', 'user:user', 'forgotpassword');
        $this->allow('guest', 'user:user', 'resetpassword');
        $this->allow('guest', 'default:index', 'index');
        $this->allow('guest', 'default:error', 'error');
        $this->allow('guest', 'default:code-list', 'code-list-index');
        $this->allow('guest', 'default:code-list', 'view-code');
        $this->allow('guest', 'default:code-list', 'view-category');
        $this->allow('guest', 'default:form');
        $this->allow('guest', 'default:iati');
        $this->allow('guest', 'default:iatixmlcompliance');
        $this->allow('guest', 'default:crstoiati');
        $this->allow('guest', 'default:api');
        $this->allow('guest', 'default:activityviewer');
        $this->allow('guest', 'default:iatixmlController');
        $this->allow('guest', 'default:wep', 'register');
        $this->allow('guest', 'default:wep', 'index');
        $this->allow('guest', 'nullresources');

//        deny users from trying to login again
        $this->deny('user', 'user:user', 'login');
        $this->deny('user', 'user:user', 'register');

//        this allows the role user to use user module's user controller's logout action
        $this->allow('user', 'user:user', 'logout');
        $this->allow('user', 'user:user', 'changepassword');
        $this->allow('user', 'user:user', 'myaccount');
        $this->allow('user', 'user:user', 'edit');
        $this->allow('user', 'default:wep', 'list-activities');
        $this->allow('user', 'default:wep', 'view-activities', new App_ResourceAssertion('view'));
        $this->allow('user', 'default:wep', 'view-activity');
        $this->allow('user', 'default:wep', 'add-activities');
        $this->allow('user', 'default:wep', 'add-activity', new App_ResourceAssertion('add'));
        $this->allow('user', 'default:wep', 'activitybar');
        $this->allow('user', 'default:wep', 'add-activity-elements', new App_ResourceAssertion('add'));
        $this->allow('user', 'default:wep', 'edit-activity-elements', new App_ResourceAssertion('edit'));
        $this->allow('user', 'default:addelement');
        $this->allow('user', 'default:wep', 'delete', new App_ResourceAssertion('delete'));
        $this->allow('user', 'default:wep', 'edit-activity');
        $this->allow('user', 'default:wep', 'dashboard');
        $this->allow('user', 'default:wep', 'edit-defaults');
        $this->allow('user', 'default:wep', 'remove-elements');


        $this->allow('admin', 'user');

        $this->allow('admin', 'default:code-list');
        $this->allow('admin', 'default:wep', 'view-activities');
        $this->allow('admin', 'default:wep', 'delete');
        $this->allow('admin', 'default:wep', 'add-activity');
        $this->allow('admin', 'default:wep', 'add-activity-elements');
        $this->allow('admin', 'default:wep', 'edit-activity-elements');
        $this->allow('admin', 'default:admin', 'register-user');
//        $this->allow('admin', 'user:user', 'test', new App_ResourceAssertion('title'));
//        $this->allow('admin', 'default:wep', 'edit-activity-elements', new App_sResourceAssertion('title'));
        $this->deny('user', 'user:user', 'register');
        $this->allow('superadmin', 'default:admin');
    }

    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        // by default, undefined resources are allowed to all
        if (!$this->has($resource)) {
            $resource = 'nullresources';
        }
        return parent::isAllowed($role, $resource, $privilege);
    }

    /*
     * resource and privileges and users for the setDynamicPermissions can be varied as needed
     */

    public function setDynamicPermisssion()
    {
        $this->addResource('resource');
        /**
         * Adds an "allow" rule to the ACL
         *
         * @param  Zend_Acl_Role_Interface|string|array     $roles
         * @param  Zend_Acl_Resource_Interface|string|array $resources
         * @param  string|array                             $privileges
         * @param  Zend_Acl_Assert_Interface                $assert
         * @uses   Zend_Acl::setRule()
         * @return Zend_Acl Provides a fluent interface
         */
//        $this->allow('admin', 'resource', 'ActivityDate', new App_ResourceAssertion('activity_date'));
//        $this->allow('admin', 'resource', 'ParticipatingOrganisation', new App_ResourceAssertion('participating_organisation'));
//        $this->allow('admin', 'resource', 'Title', new App_ResourceAssertion('title'));
        $this->allow('user', 'resource', 'ActivityDate', new App_ResourceAssertion('activity_date'));
        $this->allow('user', 'resource', 'ParticipatingOrganisation', new App_ResourceAssertion('participating_organisation'));
        $this->allow('user', 'resource', 'Title', new App_ResourceAssertion('title'));
        $this->allow('admin', 'user');
    }

}