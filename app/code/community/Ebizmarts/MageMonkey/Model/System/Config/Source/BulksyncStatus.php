<?php

/**
 * Bulksync status list source
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Model_System_Config_Source_BulksyncStatus
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
        	array('value' => 'idle', 'label' => Mage::helper('monkey')->__('IDLE')),
            array('value' => 'running', 'label' => Mage::helper('monkey')->__('Running Now')),
            array('value' => 'chunk_running', 'label' => Mage::helper('monkey')->__('Running')),
            array('value' => 'finished', 'label' => Mage::helper('monkey')->__('Completed')),
        );
    }

	/**
	 * Another way of returning data
	 *
	 * @return array
	 */
    public function toOption()
    {
    	$options = $this->toOptionArray();

    	$ary = array();

    	foreach($options as $option){
			$ary[$option['value']] = $option['label'];
    	}

    	return $ary;
    }

}