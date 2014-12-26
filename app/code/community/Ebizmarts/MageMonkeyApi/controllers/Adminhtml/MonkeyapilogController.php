<?php

class Ebizmarts_MageMonkeyApi_Adminhtml_MonkeyapilogController extends Mage_Adminhtml_Controller_Action {

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

}