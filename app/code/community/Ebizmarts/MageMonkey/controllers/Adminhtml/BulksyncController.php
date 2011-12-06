<?php

class Ebizmarts_MageMonkey_Adminhtml_BulksyncController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->_title($this->__('Newsletter'))
             ->_title($this->__('MailChimp'));

        $this->loadLayout();
        $this->_setActiveMenu('newsletter/magemonkey');
        return $this;
    }

	/**
	 * Magento to MailChimp
	 */
	public function exportAction()
	{
		$this->_initAction();
		$this->_title($this->__('Export'));
        $this->renderLayout();
	}

	/**
	 * MailChimp to Magento
	 */
	public function importAction()
	{
		$this->_initAction();
		$this->_title($this->__('Import'));
        $this->renderLayout();
	}

	public function saveAction()
	{
		var_dump($this->getRequest()->getParams());die;
	}

}