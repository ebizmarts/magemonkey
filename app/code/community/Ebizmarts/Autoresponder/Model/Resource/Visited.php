<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_Autoresponder_Model_Resource_Visited extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ebizmarts_autoresponder/visited','id');
    }
    public function loadByCustomerProduct(Ebizmarts_Autoresponder_Model_Visited $obj,$customerId,$productId,$storeId) {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable().'.'.'customer_id =?',$customerId)
            ->where($this->getMainTable().'.'.'product_id =?',$productId)
            ->where($this->getMainTable().'.'.'store_id =?',$storeId);
        $visited_id = $this->_getReadAdapter()->fetchOne($select);
        if($visited_id) {
            $this->load($obj,$visited_id);
        }
        return $this;
    }
}