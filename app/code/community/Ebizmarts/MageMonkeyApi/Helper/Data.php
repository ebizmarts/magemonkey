<?php

class Ebizmarts_MageMonkeyApi_Helper_Data extends Mage_Core_Helper_Abstract {

    public function generateApiKey() {
        return Mage::helper("core")->getRandomString(22);
    }

    public function formatTimeSeconds($time) {
        $time  = floor($time*10.0)/10.0;
        return sprintf("%.1f", $time);
    }

    public function formatFloat($float) {
        return 0.01 * (int)($float*100);
    }

    public function defaultCurrency() {
        return $this->currency((string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE));
    }

    public function currency($code) {
        $currencyObj = new stdClass();
        $currencyObj->code   = $code;
        $currencyObj->symbol = Mage::app()->getLocale()->currency($currencyObj->code)->getSymbol();

        return $currencyObj;
    }

}