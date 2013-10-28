<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 10/22/13
 * Time   : 5:21 PM
 * File   : Review.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_Resource_Review extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ebizmarts_autoresponder/review','id');
    }
    public function loadByToken(Ebizmarts_Autoresponder_Model_Review $obj,$token) {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable().'.'.'token =?',$token);
        $token_id = $this->_getReadAdapter()->fetchOne($select);
        if($token_id) {
            $this->load($obj,$token_id);
        }
        return $this;
    }
}