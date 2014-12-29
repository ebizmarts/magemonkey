<?php

class Ebizmarts_MageMonkeyApi_Adminhtml_MonkeyapilogController extends Mage_Adminhtml_Controller_Action {

    protected function _initLog($id) {

        $log = Mage::getModel('monkeyapi/log');

        if ($id) {
            $log->load($id);
        }

        Mage::register('current_log', $log);
        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('Calls'))
            ->_title($this->__('MageMonkey API'));

        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('monkeyapi/adminhtml_monkeyapilog_grid')->toHtml()
        );
    }

    /**
     * View additional data for the request.
     */
    public function viewAction() {
        $this->_title($this->__('View log'))
        ->_title($this->__('Calls'))
            ->_title($this->__('MageMonkey API'));

        $id = $this->getRequest()->getParam('id');

        $this->_initLog($id);
        $this->loadLayout();
        $this->_setActiveMenu('system');

        $log = Mage::registry('current_log');

        if(!$log->getId()) {
            $this->_getSession()->addError($this->__('Entry does not exist.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->renderLayout();

    }

}