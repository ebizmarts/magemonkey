<?php

class Ebizmarts_MageMonkey_Model_System_Config_Source_List
{

	protected $_lists   = null;

	public function __construct()
	{
		if( is_null($this->_lists) ){
			$this->_lists = Mage::getSingleton('monkey/api')
							->lists();
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

    	if($this->_lists){

    		foreach($this->_lists['data'] as $list){
    			$lists []= array('value' => $list['id'], 'label' => $list['name'] . ' (' . $list['stats']['member_count'] . ' ' . Mage::helper('monkey')->__('members') . ')');
    		}

    	}else{
    		$lists []= array('value' => '', 'label' => Mage::helper('monkey')->__('--- No data ---'));
    	}

        return $lists;
    }

}