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
		$request = $this->getRequest();

		if( !$request->isPost() ){
			$this->_redirect('adminhtml/dashboard');
			return;
		}

		$job = Mage::getModel('monkey/bulksyncExport')
					->setStatus('idle')
					->setLists(serialize($request->getParam('list')))
					->setDataSourceEntity($request->getParam('data_source_entity'))
					->save();

		if( $job->getId() ){
			$this->_getSession()->addSuccess($this->__('Export job #%s was sucessfully scheduled.', $job->getId()));
		}else{
			$this->_getSession()->addError($this->__('Could not schedule job.'));
		}

		$this->_redirectReferer();
	}

}