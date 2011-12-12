<?php

class Ebizmarts_MageMonkey_Model_Cron
{
	/**
	 * Limit var for SQL queries
	 */
	protected $_limit = 200;

	/**
	 * Import limit var
	 */
	protected $_importLimit = 500;

	public function processImportJobs()
	{
		$job = $this->_getJob('Import');
		if(is_null($job)){
			return $this;
		}

		$start = 0;
		foreach($job->lists() as $listId){

			$store = $this->_helper()->getStoreByList($listId);
			$api = Mage::getSingleton('monkey/api', array('store' => $store));

			$api->listMembers($listId, 'subscribed', NULL, $start, $this->_importLimit);

		}
	}

	/**
	 * Process EXPORT tasks
	 */
	public function processExportJobs()
	{
		$job = $this->_getJob('Export');
		if(is_null($job)){
			return $this;
		}

		$collection = $this->_getEntityModel($job->getDataSourceEntity())
						->setPageSize($this->_limit);

		//Condition for chunk batch
		if($job->getLastProcessedId()){
			$collection->addFieldToFilter($this->_getId($job->getDataSourceEntity()), array('gt' => (int)$job->getLastProcessedId()));
		}

		$collection->load();

    	//var_dump((string)$collection->getSelect());

		$batch = array();

		foreach($job->lists() as $listId){
			$store = $this->_helper()->getStoreByList($listId);

			//if($store){

				$api = Mage::getSingleton('monkey/api', array('store' => $store));

				$processedCount = 0;
				foreach($collection as $item){
					$processedCount += 1;
					$batch []= $this->_helper()->getMergeVars($item, TRUE);
				}

				//var_dump($batch);

				if(count($batch) > 0){

					$job->setStatus('chunk_running')
						->setUpdatedAt($this->_dbDate())
						->save();

					$vals = $api->listBatchSubscribe($listId, $batch, FALSE, TRUE, FALSE);

					if ( is_null($api->errorCode) ){

						$lastId = $collection->getLastItem()->getId();
						$job->setLastProcessedId($lastId);
						$job->setProcessedCount( ( $processedCount+$job->getProcessedCount() ));

						/*if( $processedCount < $this->_limit ){
							$job->setStatus('finished');
						}*/

						$job
						->setUpdatedAt($this->_dbDate())
						->save();

					} else {

						//TODO: Do something to handle errors

					    /*echo "Batch Subscribe failed!\n";
						echo "code:".$api->errorCode."\n";
						echo "msg :".$api->errorMessage."\n";
						die;*/
						/*echo "added:   ".$vals['add_count']."\n";
						echo "updated: ".$vals['update_count']."\n";
						echo "errors:  ".$vals['error_count']."\n";
						foreach($vals['errors'] as $val){
							echo $val['email_address']. " failed\n";
							echo "code:".$val['code']."\n";
							echo "msg :".$val['message']."\n";
						}
						die;*/

					}

				}else{
					$job
					->setStatus('finished')
					->setUpdatedAt($this->_dbDate())
					->save();
				}

			//}
		}

		return $this;
	}

	protected function _getEntityModel($type)
	{
		$model = null;

		switch ($type) {
			case 'newsletter_subscriber':
				$model = Mage::getResourceSingleton('newsletter/subscriber_collection')
							//->showCustomerInfo(true)
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

	protected function _getId($type)
	{
		$idFieldName = null;

		switch ($type) {
			case 'newsletter_subscriber':
				$idFieldName = 'subscriber_id';
				break;
			default:
				$idFieldName = 'id';
		}

		return $idFieldName;
	}

	protected function _helper()
	{
		return Mage::helper('monkey');
	}

	protected function _dbDate()
	{
		return Mage::getModel('core/date')->gmtDate();
	}

	protected function _getJob($entity)
	{
		$job = Mage::getModel("monkey/bulksync{$entity}")
					->getCollection()
					->addFieldToFilter('status', array('IN' => array('idle', 'chunk_running') ))
					->addOrder('created_at', 'asc')
					->load();
		if(!$job->getFirstItem()->getId()){
			return null;
		}

		return $job->getFirstItem();
	}
}