<?php

/**
 * Transactional emails data source
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_System_Config_Source_TransactionalEmails
{

	/**
	 * Return available options
	 *
	 * @return array
	 */
    public function toOptionArray()
    {
		return array(
			array('value' => 'false', 'label' => Mage::helper('monkey')->__('Disabled')),
			array('value' => 'sts', 'label' => Mage::helper('monkey')->__('STS')),
			array('value' => 'mandrill', 'label' => Mage::helper('monkey')->__('Mandrill'))
		);
    }

}
