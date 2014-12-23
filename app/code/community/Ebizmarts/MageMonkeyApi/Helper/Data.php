<?php

class Ebizmarts_MageMonkeyApi_Helper_Data extends Mage_Core_Helper_Abstract {

    public function generateApiKey() {
        return Mage::helper("core")->getRandomString(22);
    }

}