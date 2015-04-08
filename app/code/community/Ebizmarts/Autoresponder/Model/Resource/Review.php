<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Autoresponder_Model_Resource_Review extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ebizmarts_autoresponder/review', 'id');
    }

    public function loadByToken(Ebizmarts_Autoresponder_Model_Review $obj, $token)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable() . '.' . 'token =?', $token);
        $token_id = $this->_getReadAdapter()->fetchOne($select);
        if ($token_id) {
            $this->load($obj, $token_id);
        }
        return $this;
    }
}