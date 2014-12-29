<?php

class Ebizmarts_MageMonkeyApi_Block_Adminhtml_Monkeyapilog_View extends Mage_Adminhtml_Block_Template {


    public function logObject() {
        return Mage::registry('current_log');
    }

    public function getBackUrl() {
        return $this->getUrl('*/*/');
    }

}