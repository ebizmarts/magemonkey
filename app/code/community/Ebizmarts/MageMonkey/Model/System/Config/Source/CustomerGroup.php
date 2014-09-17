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
    protected $_group   = null;

    /**
     * Load lists and store on class property
     *
     * @return void
     */
    public function __construct()
    {
        $listId = Mage::getStoreConfig('monkey/general/list');
        if( is_null($this->_group) ){
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
        $lists = array();

//        if(is_array($this->_group)){
//            foreach($this->_group as $group) {
//                $lists[] = array('value'=> $group['id'], 'label' => $group['name'],'style'=>'font-weight: bold;');
//                $prefix = $group['id'];
//                foreach($group['groups'] as $key=>$list){
//                    $lists []= array('value' => $prefix.'_'.$key, 'label' => $list['name'],'style'=>'padding-left:20px');
//                }
//            }
//
//        }else{
//            $lists []= array('value' => '', 'label' => Mage::helper('monkey')->__('--- No data ---'));
//        }

        return $lists;
    }

}