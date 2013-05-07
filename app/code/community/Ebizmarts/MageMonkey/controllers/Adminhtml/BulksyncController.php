<?php

/**
 * Bulksync controller to schedule and manage jobs
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Adminhtml_BulksyncController extends Mage_Adminhtml_Controller_Action
{

	protected $_defredirect = 'monkey/adminhtml_ecommerce/';

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

	public function queueAction()
	{
		$this->_initAction();
		$this->_title($this->__('Job Queue'));
        $this->renderLayout();
	}

	/**
	 * Just the import grid for AJAX calls
	 */
	public function importgridAction()
	{
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('monkey/adminhtml_bulksync_queueImport_grid')->toHtml()
        );
	}

	/**
	 * Just the export grid for AJAX calls
	 */
	public function exportgridAction()
	{
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('monkey/adminhtml_bulksync_queueExport_grid')->toHtml()
        );
	}

	/**
	 * Initialize job based on job_id url param
	 *
	 * @return Ebizmarts_MageMonkey_Model_BulksyncExport|Ebizmarts_MageMonkey_Model_BulksyncImport
	 */
    protected function _initJob()
    {
        $id     = $this->getRequest()->getParam('job_id');
        $entity = $this->getRequest()->getParam('entity');
        $job = Mage::getModel("monkey/bulksync{$entity}")->load($id);

        if (!$job->getId()) {
            $this->_getSession()->addError($this->__('This job no longer exists.'));
            $this->_redirect($this->_defredirect);
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }

        return $job;
    }

	/**
	 * Delete a job from current schedule
	 *
	 * @return void
	 */
	public function deleteAction()
	{
        if ($job = $this->_initJob()) {

            try {
                $job->delete();
                $this->_getSession()->addSuccess(
                    $this->__('The job has been deleted.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The job has not been deleted.'));
                Mage::logException($e);
            }
            $this->_redirectReferer($this->_defredirect);

        }
	}

	/**
	 * Reset job status to IDLE
	 *
	 * @return void
	 */
	public function resetAction()
	{
        if ($job = $this->_initJob()) {

            try {

                $job->setStatus('idle')
                ->setProcessedCount(0)
                ->setLastProcessedId(0)
                ->save();

                $this->_getSession()->addSuccess(
                    $this->__('The job has been updated.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The job has not been updated.'));
                Mage::logException($e);
            }
            $this->_redirectReferer($this->_defredirect);

        }
	}

	/**
	 * Schedule a job on database
	 *
	 * @return void
	 */
	public function saveAction()
	{
		$request = $this->getRequest();

		if( !$request->isPost() ){
			$this->_redirect('adminhtml/dashboard');
			return;
		}

		$job = new Varien_Object;
		if($request->getPost('direction') == 'import'){

			$job = Mage::getModel('monkey/bulksyncImport')
					->setStatus('idle')
					->setLists(serialize($request->getPost('list')))
					->setImportTypes(serialize($request->getPost('import_types')))
					->setCreateCustomer((int)$request->getPost('create_customers'));

			if($request->getPost('since')){
				$job->setSince($request->getPost('since') . ' 00:00:00');
			}

			$job->save();

		}elseif($request->getPost('direction') == 'export'){

			$job = Mage::getModel('monkey/bulksyncExport')
					->setStatus('idle')
                    ->setStoreId((int)$request->getPost('store_id'))
					->setLists(serialize($request->getPost('list')))
					->setDataSourceEntity($request->getPost('data_source_entity'))
					->save();
		}

		if( $job->getId() ){
			$this->_getSession()->addSuccess($this->__('Job #%s was sucessfully scheduled.', $job->getId()));
		}else{
			$this->_getSession()->addError($this->__('Could not schedule job.'));
		}

		$this->_redirectReferer($this->_defredirect);
	}

}
