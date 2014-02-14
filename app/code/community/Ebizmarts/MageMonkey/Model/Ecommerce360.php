<?php

/**
 * Ecommerce360 main model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Model_Ecommerce360
{

	/**
	 * Order information to send to MC
	 *
	 * @var array
	 * @access protected
	 */
	protected $_info = array();

	/**
	 * @var integer
	 * @access protected
	 */
	protected $_auxPrice = 0;

	/**
	 * Current order
	 *
	 * @var Mage_Sales_Model_Order
	 * @access protected
	 */
	protected $_order;

	/**
	 * Skip products list
	 *
	 * @var array
	 * @access protected
	 */
	protected $_productsToSkip = array(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);

    /**
     * Retrieve Cookie Object
     *
     * @return Mage_Core_Model_Cookie
     */
    public function getCookie()
    {
        return Mage::app()->getCookie();
    }

	/**
	 * Check if Ecommerce360 integration is enabled per configuration settings
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return Mage::helper('monkey')->ecommerce360Active();
	}

	/**
	 * Add cookie to customer's session
	 *
	 * @param Varien_Event_Observer $observer
	 * @return Varien_Event_Observer
	 */
	public function saveCookie(Varien_Event_Observer $observer)
	{
		if( $this->isActive() ){
			$request = Mage::app()->getRequest();

			$thirty_days = time()+60*60*24*30;
	        if ( $request->getParam('mc_cid') ){
	            $this->getCookie()->set('magemonkey_campaign_id', $request->getParam('mc_cid'), $thirty_days);
	        }
	        if ( $request->getParam('mc_eid') ){
	            $this->getCookie()->set('magemonkey_email_id', $request->getParam('mc_eid'), $thirty_days);
	        }
		}
        return $observer;
	}

	/**
	 * Process data and send order to MC
	 *
	 * @param Varien_Event_Observer $observer
	 * @return Varien_Event_Observer
	 */
	public function run(Varien_Event_Observer $observer)
	{
        $order = $observer->getEvent()->getOrder();
		if ( ( ($this->_getCampaignCookie() &&
				$this->_getEmailCookie()) || Mage::helper('monkey')->config('ecommerce360') == 2 ) &&
					$this->isActive() ){
			$this->logSale($order);
		}
		return $observer;
	}

	/**
	 * Send order to MailChimp
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @return bool|array
	 */
	public function logSale($order)
	{

		$this->_order = $order;
		$api = Mage::getSingleton('monkey/api', array('store' => $this->_order->getStoreId()));
		if(!$api){
			return false;
		}

		$subtotal = $this->_order->getSubtotal();
		$discount = (float)$this->_order->getDiscountAmount();
		if ($discount != 0) {
			$subtotal = $subtotal + ($discount);
		}

        $this->_info = array(
				                'id'          => $this->_order->getIncrementId(),
				                'total'       => $subtotal,
				                'shipping'    => $this->_order->getShippingAmount(),
				                'tax'         => $this->_order->getTaxAmount(),
				                'store_id'    => $this->_order->getStoreId(),
				                'store_name'  => $this->_order->getStoreName(),
                                'order_date'  => $this->_order->getCreatedAt(),
				                'plugin_id'   => 1215,
				                'items'       => array()
                			);

		$emailCookie    = $this->_getEmailCookie();
		$campaignCookie = $this->_getCampaignCookie();

		$this->setItemstoSend();

		if($emailCookie && $campaignCookie){
			$this->_info ['email_id']= $emailCookie;
			$this->_info ['campaign_id']= $campaignCookie;

			//Send order to MailChimp
	    	$rs = $api->campaignEcommOrderAdd($this->_info);
		}else{
			$this->_info ['email']= $this->_order->getCustomerEmail();
			$rs = $api->ecommOrderAdd($this->_info);
		}

		if ( $rs === TRUE ){
			$this->_logCall();
			return true;
		}else{
			return $rs;
		}

    }

	/**
	 * Process order items to send to MailChimp
	 *
	 * @access private
	 * @return Ebizmarts_MageMonkey_Model_Ecommerce360
	 */
    private function setItemstoSend()
    {
    	 foreach ($this->_order->getAllItems() as $item){
			$mcitem = array();
            $product = Mage::getSingleton('catalog/product')->load($item->getProductId());

			if(in_array($product->getTypeId(), $this->_productsToSkip) && $product->getPriceType() == 0){
				if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
					$this->_auxPrice = $item->getPrice();
				}
				continue;
			}

			$mcitem['product_id'] = $product->getEntityId();
			$mcitem['sku'] = $product->getSku();
            $mcitem['product_name'] = $product->getName();

            $names = array();
            $cat_ids = $product->getCategoryIds();

            if (is_array($cat_ids) && count($cat_ids)>0){
                $category = Mage::getModel('catalog/category')->load($cat_ids[0]);
                $mcitem['category_id'] = $cat_ids[0];
                $names[] = $category->getName();
                while ($category->getParentId() && $category->getParentId()!=1){
                    $category = Mage::getModel('catalog/category')->load($category->getParentId());
                    $names[] = $category->getName();
                }
            }
            if(!isset($mcitem['category_id'])) {
            	$mcitem['category_id'] = 0;
            }
        	$mcitem['category_name'] = (count($names))? implode(" - ",array_reverse($names)) : 'None';
            $mcitem['qty'] = $item->getQtyOrdered();
         	$mcitem['cost'] = ($this->_auxPrice > 0)? $this->_auxPrice : $item->getPrice();
            $this->_info['items'][] = $mcitem;
            $this->_auxPrice = 0;
		}

		return $this;
    }

	/**
	 * Get cookie <magemonkey_email_id> from customer's session
	 *
	 * @return string|null
	 */
	protected function _getEmailCookie()
	{
		return $this->getCookie()->get('magemonkey_email_id');
	}

	/**
	 * Get cookie <magemonkey_campaign_id> from customer's session
	 *
	 * @return string|null
	 */
	protected function _getCampaignCookie()
	{
		return $this->getCookie()->get('magemonkey_campaign_id');
	}

	/**
	 * Save Api Call on db
	 *
	 * @return Ebizmarts_MageMonkey_Model_Ecommerce
	 */
    protected function _logCall()
    {
		return Mage::getModel('monkey/ecommerce')
			 ->setOrderIncrementId($this->_order->getIncrementId())
			 ->setOrderId($this->_order->getId())
	         ->setMcCampaignId($this->_getCampaignCookie())
	         ->setMcEmailId($this->_getEmailCookie())
	         ->setCreatedAt( Mage::getModel('core/date')->gmtDate() )
		     ->save();
	}

	/** Send order to MailChimp Automatically by Order Status
	 *
	 *
	 */
    public function autoExportJobs(){
        $allow_sent = false;
        $orders = Mage::getResourceModel('sales/order_collection');
        $orders->getSelect()->joinLeft( array('ecommerce'=> Mage::getSingleton('core/resource')->getTableName('monkey/ecommerce')), 'main_table.entity_id = ecommerce.order_id', 'main_table.*')->where('ecommerce.order_id is null');

        //Get status options selected in the Configuration
        $states = explode(',', Mage::helper('monkey')->config('order_status'));

		foreach($orders as $order){
			foreach($states as $state){
				if($order->getStatus() == $state || $state == 'all_status'){
					$allow_sent = true;
				}
			}

			if($allow_sent == true){
				$this->_order = $order;
				$api = Mage::getSingleton('monkey/api', array('store' => $this->_order->getStoreId()));
				if(!$api){
					return false;
				}

				$subtotal = $this->_order->getSubtotal();
				$discount = (float)$this->_order->getDiscountAmount();
				if ($discount != 0) {
					$subtotal = $subtotal + ($discount);
				}

		        $this->_info = array(
						                'id'          => $this->_order->getIncrementId(),
						                'total'       => $subtotal,
						                'shipping'    => $this->_order->getShippingAmount(),
						                'tax'         => $this->_order->getTaxAmount(),
						                'store_id'    => $this->_order->getStoreId(),
						                'store_name'  => $this->_order->getStoreName(),
                                        'order_date'  => $this->_order->getCreatedAt(),
						                'plugin_id'   => 1215,
						                'items'       => array()
		                			);

				$email    = $this->_order->getCustomerEmail();
				$campaign = $this->_order->getEbizmartsMagemonkeyCampaignId();
				$this->setItemstoSend();

				if($email && $campaign){
					$this->_info ['email_id']= $email;
					$this->_info ['campaign_id']= $campaign;

					//Send order to MailChimp
			    	$rs = $api->campaignEcommOrderAdd($this->_info);
				}else{
					$this->_info ['email']= $email;
					$rs = $api->ecommOrderAdd($this->_info);
				}
				$allow_sent = false;
                if ( isset($rs['complete']) && $rs['complete'] == TRUE ) {
					$this->_logCall();
				}
			}

		}
	 }

}
