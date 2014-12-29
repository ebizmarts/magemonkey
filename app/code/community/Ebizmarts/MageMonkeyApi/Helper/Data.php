<?php

class Ebizmarts_MageMonkeyApi_Helper_Data extends Mage_Core_Helper_Abstract {

    public function generateApiKey() {
        return Mage::helper("core")->getRandomString(22);
    }

    public function formatTimeSeconds($time) {
        $time  = floor($time*10.0)/10.0;
        return sprintf("%.1f", $time);
    }

}