<?php

/**
 * Transactional email Mandrill grid
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Block_Adminhtml_Transactionalemail_Mandrill_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('mandrill_valid_emails');
        $this->setUseAjax(false);
        $this->setSaveParametersInSession(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection()
    {
		$apiKey  = Mage::helper('monkey')->getMandrillApiKey(0);
        $mail = Ebizmarts_MageMonkey_Model_TransactionalEmail_Adapter::factory('mandrill')
					->setApiKey($apiKey);
		$emails = $mail->usersSenders();

		if($emails !== FALSE){
			$_emails = array();
			foreach($emails as $email){
				$_emails []= array(
									'email'       => $email->address,
									'created_at'  => $email->created_at,
									'enabled'     => ($email->is_enabled === TRUE ? Mage::helper('monkey')->__('Yes') : Mage::helper('monkey')->__('No')),
								  );
			}
			$collection = Mage::getModel('monkey/custom_collection', array($_emails));
		}else{
			$collection = Mage::getModel('monkey/custom_collection', array(array()));
		}

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('email', array(
            'header'=> Mage::helper('monkey')->__('Email Address'),
            'index' => 'email',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('enabled', array(
            'header'=> Mage::helper('monkey')->__('Enabled'),
            'index' => 'enabled',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('created_at', array(
            'header'=> Mage::helper('monkey')->__('Created At'),
            'index' => 'created_at',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('monkey')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getEmail',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('monkey')->__('Disable'),
                        'confirm' => Mage::helper('monkey')->__('This action takes immediate effect, so use it with care.'),
                        'url'     => array(
                            'base' => '*/*/mandrillDisable',
                            'params' => array('store' => $this->getRequest()->getParam('store')),
                        ),
                        'field'   => 'email'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
