<?php

require_once 'Ebizmarts/MageMonkeyApi/controllers/ApiController.php';

class Ebizmarts_MageMonkeyApi_Api_CustomersController extends Ebizmarts_MageMonkeyApi_ApiController {

	/**
	 * Return last 5 orders.
	 */
	public function indexAction() {

		$id = $this->getRequest()->getParam('id');

		if($id) {
			$customer = Mage::getModel('customer/customer')->load((int)$id);

			if(!$customer->getId()) {
				$this->_setClientError(400, 4006);
	        	return;
			}
			else {
				$this->_setSuccess(200, $this->_customerData($customer));
	        	return;
			}

		}
		else {

			$post = $this->_jsonPayload();

			$maxLimit = 100;

			$limit     = (($post->limit > $maxLimit) ? 20 : $post->limit);
			$direction = $post->direction;

			$customerCollection = Mage::getResourceModel('customer/customer_collection')
            						->addNameToSelect();

			if($direction == 'before') {
				$customerCollection->addFieldToFilter('updated_at', array('lteq' => $post->updated_at));
				$customerCollection->setOrder('updated_at', 'DESC');
			}
			elseif($direction == 'after') {
				$customerCollection->addFieldToFilter('updated_at', array('gteq' => $post->updated_at));
				$customerCollection->setOrder('updated_at', 'ASC');
			}

			$customerCollection->setPageSize($limit)->load();

			//echo (string)$customerCollection->getSelect();

			$ret = array();

			foreach($customerCollection as $order) {
				$ret []= $this->_customerData($order);
			}

			$this->_setSuccess(200, $ret);
	        return;

    	}
	}

	protected function _customerData(Mage_Customer_Model_Customer $customer) {
        $result = array();
        $result['customer_id']              = (int) $customer->getId();
        $result['firstname']                = $customer->getFirstname();
        $result['lastname']                 = $customer->getLastname();
        $result['email']                    = $customer->getEmail();
        $result['website_id']               = (int) $customer->getWebsiteId();
        $result['group_id']                 = (int) $customer->getGroupId();

		return $result;
	}

}