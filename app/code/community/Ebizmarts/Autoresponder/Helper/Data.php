<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 4/28/13
 * Time   : 11:20 AM
 * File   : Data.php
 * Module : Ebizmarts_Magemonkey
 */ 
class Ebizmarts_Autoresponder_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getLists()
    {
        $types = array();
        foreach (Mage::getConfig()->getNode('default/ebizmarts_autoresponder')->asArray() as $key =>$data) {
            if(isset($data['listname'])) {
                $types[$key]['listname'] = (string)$data['listname'];
                $collection = Mage::getModel('ebizmarts_autoresponder/unsubscribe')->getCollection();
                $email = $this->_getEmail();
                $collection->addFieldToFilter('main_table.email',array('eq'=>$email))
                    ->addFieldToFilter('main_table.list',array('eq'=>$key));
                if($collection->getSize() > 0) {
                    $types[$key]['checked'] = "";
                }
                else {
                    $types[$key]['checked'] = "checked";
                }
            }
        }
        return $types;
    }
    protected function _getEmail()
    {
        return Mage::helper('customer')->getCustomer()->getEmail();
    }
}