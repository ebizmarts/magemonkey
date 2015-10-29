<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/29/14
 * Time   : 5:47 PM
 * File   : ConfigController.php
 * Module : magemonkey
 */
class Ebizmarts_MageMonkey_Adminhtml_ConfigController extends Mage_Adminhtml_Controller_Action
{
    public function getGroupsAction()
    {
        $params = $this->getRequest()->getParams();
        $listId = $params['list'];
        if (isset($params['store'])) {
            $store = $params['store'];
            $store = $this->_getStoreByCode($store);
            $storeId = $store->getId();
        } else {
            $storeId = null;
        }
        $originalGroups = Mage::getStoreConfig('monkey/general/cutomergroup', $storeId);
        $originalGroups = explode(",", $originalGroups);
        $groups = Mage::getSingleton('monkey/api')->listInterestGroupings($listId);
        $rc = array();
        if (is_array($groups)) {
            foreach ($groups as $group) {
                $rc[] = array('value' => $group['id'], 'label' => $group['name'], 'disabled' => 1, 'style' => 'font-weight: bold');
                $prefix = $group['id'];
                foreach ($group['groups'] as $item) {
                    if (in_array($prefix . '_' . $item['name'], $originalGroups)) {
                        $rc[] = array('value' => $prefix . '_' . $item['name'], 'label' => $item['name'], 'style' => 'padding-left:20px', "selected" => true);
                    } else {
                        $rc[] = array('value' => $prefix . '_' . $item['name'], 'label' => $item['name'], 'style' => 'padding-left:20px', "selected" => false);
                    }
                }
            }
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($rc));
    }

    public function upgradeForPatchAction(){

        $prefix = Mage::getConfig()->getTablePrefix();
        if($prefix){
            $pre = $prefix[0];
        }else{
            $pre = '';
        }
        $resource = Mage::getSingleton('core/resource')
            ->getConnection('core_write');

        $table = $resource->getTableName($pre.'permission_block');
        $exists = (bool)$resource->showTableStatus($table);
        if($exists) {
            $blocks = array(
                array('block_name' => 'ebizmarts_abandonedcart/email_order_items', 'is_allowed' => 1),
                array('block_name' => 'ebizmarts_autoresponder/email_backtostock_item', 'is_allowed' => 1),
                array('block_name' => 'ebizmarts_autoresponder/email_related_items', 'is_allowed' => 1),
                array('block_name' => 'ebizmarts_autoresponder/email_review_items', 'is_allowed' => 1),
                array('block_name' => 'ebizmarts_autoresponder/email_wishlist_items', 'is_allowed' => 1),
            );
            $notUpdated = false;
            foreach ($blocks as $item) {
                $currentRow = Mage::getResourceModel('admin/block_collection')
                    ->addFieldToFilter('block_name', array('eq' => $item['block_name']));
                $row = $currentRow->getFirstItem();
                if (!$row->getBlockId()) {
                    $notUpdated = true;
                    $row->setBlockName($item['block_name'])
                        ->setIsAllowed($item['is_allowed'])
                        ->save();
                }
            }
            Mage::app()->getResponse()->setBody($notUpdated);
        }
        return 0;
    }

    protected function _getStoreByCode($storeCode)
    {
        $stores = array_keys(Mage::app()->getStores());
        foreach ($stores as $id) {
            $store = Mage::app()->getStore($id);
            if ($store->getCode() == $storeCode) {
                return $store;
            }
        }
        return false;
    }

    protected function _isAllowed() {
        switch ($this->getRequest()->getActionName()) {
            case 'getGroups':
                $acl = 'system/config/monkey';
                break;
        }
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }

}