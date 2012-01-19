<?php

/**
 * Transactional email Mandrill grid
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
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
		$apiKey  = Mage::helper('monkey')->getApiKey(0);
        $mail = Ebizmarts_MageMonkey_Model_TransactionalEmail_Adapter::factory('mandrill')
					->setApiKey($apiKey);
		$emails = $mail->usersSenders();

		if($emails !== FALSE){
			$_emails = array();
			foreach($emails->email_addresses as $email){
				$_emails []= array('email' => $email);
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

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('monkey')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getEmail',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('monkey')->__('Delete'),
                        'confirm' => Mage::helper('monkey')->__('This action takes immediate effect, so use it with care.'),
                        'url'     => array(
                            'base' => '*/*/mandrillDelete',
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
