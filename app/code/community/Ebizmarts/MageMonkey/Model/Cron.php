<?php

class Ebizmarts_MageMonkey_Model_Cron
{
	/**
	 * Limit var for SQL queries
	 */
	protected $_limit = 200;

	public function processImportJobs()
	{

	}

	/**
	 * Process EXPORT tasks
	 */
	public function processExportJobs()
	{
		$job = Mage::getModel('monkey/bulksyncExport')
					->getCollection()
					->addFieldToFilter('status', array('IN' => array('idle', 'chunk_running') ))
					->addOrder('created_at', 'asc')
					->load();

		if(!$job->getFirstItem()->getId()){
			return $this;
		}

		$job = $job->getFirstItem();

		$lists = unserialize($job->getLists());
		var_dump($lists, $job->getDataSourceEntity());

		$collection = $this->_getEntityModel($job->getDataSourceEntity())
						->setPageSize($this->_limit);

		$collection->load();

		var_dump((string)$collection->getSelect());

		$batch = array();

		foreach($lists as $list){
			$store = $this->_helper()->getStoreByList($list);

			if($store){

				foreach($collection as $item){
					$batch []= $this->_helper()->getMergeVars($item, TRUE);
				}

			}
		}

		if(count($batch) > 0){
			var_dump($batch);die;
		}

		return $this;
	}

	protected function _getEntityModel($type)
	{
		$model = null;

		switch ($type) {
			case 'newsletter_subscriber':
				$model = Mage::getResourceSingleton('newsletter/subscriber_collection')
							->showCustomerInfo(true)
            				->addSubscriberTypeField()
            				->showStoreInfo();
				break;
			case 'customer':

				//TODO: Add default Billing and Shipping address data

				$model = Mage::getResourceModel('customer/customer_collection')
							->addNameToSelect()
							->addAttributeToSelect('gender')
							->addAttributeToSelect('dob');
				break;
		}

		return $model;
	}

	protected function _helper()
	{
		return Mage::helper('monkey');
	}

}