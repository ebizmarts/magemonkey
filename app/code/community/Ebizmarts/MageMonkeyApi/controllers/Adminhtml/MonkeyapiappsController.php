<?php

class Ebizmarts_MageMonkeyApi_Adminhtml_MonkeyapiappsController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->_title($this->__('Applications'))
            ->_title($this->__('MageMonkey API'));

        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('monkeyapi/adminhtml_monkeyapiapps_grid')->toHtml()
        );
    }

    public function newAction() {

        $activationKey = Mage::helper("core")->getRandomString(6);

        $app = Mage::getModel('monkeyapi/application');

        $app->setApplicationKey($activationKey);
        $app->setApplicationRequestKey(Mage::helper('monkeyapi')->generateApiKey());

        //@TODO: Check that app key is not already used

        $app->save();

        $this->_getSession()->addSuccess($this->__('Added new key `%s` successfully.', $activationKey));

        $this->_redirect('*/*/');
    }

    /**
     * Toggle activation key status, if disabled cannot be used.
     * @throws Exception
     */
    public function toggleAction() {
        $appId = $this->getRequest()->getParam('id');

        if($appId) {
            $app = Mage::getModel('monkeyapi/application')->load($appId);

            if($app->getId()) {
                if ($app->getApplicationRequestKey() == '*')
                    $app->setApplicationRequestKey(Mage::helper('monkeyapi')->generateApiKey());
                else
                    $app->setApplicationRequestKey('*');

                $app->save();

                $this->_getSession()->addSuccess($this->__('Success!'));
            }
            else
                $this->_getSession()->addError($this->__('Application does not exist.'));

        }

        $this->_redirect('*/*/');
        return;
    }

    /**
     * Toggle activation key status, use this when you want to activate the same key on another device.
     * @throws Exception
     */
    public function resetAction() {
        $appId = $this->getRequest()->getParam('id');

        if($appId) {
            $app = Mage::getModel('monkeyapi/application')->load($appId);

            if($app->getId()) {

                $app->setApplicationRequestKey(Mage::helper('monkeyapi')->generateApiKey());
                $app->setActivated(0);

                $app->save();

                $this->_getSession()->addSuccess($this->__('Success!'));
            }
            else
                $this->_getSession()->addError($this->__('Application does not exist.'));

        }

        $this->_redirect('*/*/');
        return;
    }

}