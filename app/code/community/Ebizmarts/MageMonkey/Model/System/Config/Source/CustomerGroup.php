<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/29/14
 * Time   : 3:36 PM
 * File   : CustomerGroup.php
 * Module : magemonkey
 */
class Ebizmarts_MageMonkey_Model_System_Config_Source_CustomerGroup
{
    protected $_group = null;

    /**
     * Load lists and store on class property
     *
     * @return void
     */
    public function __construct()
    {
        $listId = Mage::helper('monkey')->config('list');
        if (is_null($this->_group)) {
            $this->_group = Mage::getSingleton('monkey/api')
                ->listInterestGroupings($listId);
        }
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $groups = array();

        if(is_array($this->_group)){
            foreach($this->_group as $group) {
                $groups[] = array('value'=> $group['id'], 'label' => $group['name'],'style'=>'font-weight: bold;');
                $prefix = $group['id'];
                foreach($group['groups'] as $key=>$list){
                    $groups []= array('value' => $prefix.'_'.$list['name'], 'label' => $list['name'],'style'=>'padding-left:20px');
                }
            }

        }else{
            $groups []= array('value' => '', 'label' => Mage::helper('monkey')->__('--- No data ---'));
        }
        return $groups;
    }

}