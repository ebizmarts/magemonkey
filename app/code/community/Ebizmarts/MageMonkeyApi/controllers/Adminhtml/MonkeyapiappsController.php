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
        $app->setApplicationRequestKey(Mage::helper("core")->getRandomString(22));

        //@TODO: Check that app key is not already used

        $app->save();

        $this->_getSession()->addSuccess($this->__('Added new key `%s` successfully.', $activationKey));

        $this->_redirect('*/*/');
    }

    public function toggleAction() {
        $appId = $this->getRequest()->getParam('id');

        if($appId) {
            $app = Mage::getModel('monkeyapi/application')->load($appId);

            if($app->getId()) {
                if ($app->getApplicationRequestKey() == '*')
                    $app->setApplicationRequestKey(Mage::helper("core")->getRandomString(22));
                else
                    $app->setApplicationRequestKey('*');

                $app->save();

                $this->_getSession()->addSuccess($this->__('Done.'));
            }
            else
                $this->_getSession()->addError($this->__('Application does not exist.'));

        }

        $this->_redirect('*/*/');
        return;
    }

}