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
//		if( $this->isActive() ){
//            $request = Mage::app()->getRequest();
//
//			$thirty_days = time()+60*60*24*30;
//	        if ( $request->getParam('mc_cid') ){
//	            $this->getCookie()->set('magemonkey_campaign_id', $request->getParam('mc_cid'), $thirty_days);
//	        }
//	        if ( $request->getParam('mc_eid') ){
//	            $this->getCookie()->set('magemonkey_email_id', $request->getParam('mc_eid'), $thirty_days);
//	        }
//		}
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
        $storeId = Mage::app()->getStore()->getId();
        $order = $observer->getEvent()->getOrder();
        $customerEmail = $order->getCustomerEmail();
        $collection = Mage::getModel('monkey/lastorder')->getCollection()
            ->addFieldToFilter('email', array('eq' => $customerEmail));
        if(count($collection) > 0){
            //When saving the new date is automatically placed.
            $item = $collection->getFirstItem();
            $item->save();
        }else{
            Mage::getModel('monkey/lastorder')
                ->setEmail($customerEmail)
                ->save();
        }
        if ((($this->_getCampaignCookie() &&
                    $this->_getEmailCookie()) || Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::ECOMMERCE360_ACTIVE, $storeId) == 2) &&
            $this->isActive()
        ) {
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
        if (!$api) {
            return false;
        }

        $subtotal = $this->_order->getBaseSubtotal();
        $discount = (float)$this->_order->getBaseDiscountAmount();
        if ($discount != 0) {
            $subtotal = $subtotal + ($discount);
        }
        $this->_info = array(
            'id' => $this->_order->getIncrementId(),
            'total' => $subtotal,
            'shipping' => $this->_order->getBaseShippingAmount(),
            'tax' => $this->_order->getBaseTaxAmount(),
            'store_id' => $this->_order->getStoreId(),
            'store_name' => $this->_order->getStoreName(),
            'order_date' => $this->_order->getCreatedAt(),
            'plugin_id' => 1215,
            'items' => array()
        );




        $emailCookie = $this->_getEmailCookie();
        $campaignCookie = $this->_getCampaignCookie();

        $this->setItemstoSend($this->_order->getStoreId());
        $rs = false;
        if ($emailCookie && $campaignCookie) {
            $this->_info ['email_id'] = $emailCookie;
            $this->_info ['campaign_id'] = $campaignCookie;
            if (Mage::getStoreConfig('monkey/general/checkout_async')) {
                $collection = Mage::getModel('monkey/asyncorders')->getCollection();
                $alreadyOnDb = false;
                foreach ($collection as $order) {
                    $info = unserialize($order->getInfo());
                    if ($info['order_id'] == $this->_order->getId()) {
                        $alreadyOnDb = true;
                    }
                }
                if (!$alreadyOnDb) {
                    $sync = Mage::getModel('monkey/asyncorders');
                    $this->_info['order_id'] = $this->_order->getId();
                    $sync->setInfo(serialize($this->_info))
                        ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
                        ->setProcessed(0)
                        ->save();
                    $rs = true;
                } else {
                    $rs = 'Order already sent or ready to get sent soon';
                }
            } else {
                //Send order to MailChimp
                $rs = $api->campaignEcommOrderAdd($this->_info);
            }
        } else {
            $this->_info ['email'] = $this->_order->getCustomerEmail();
            if (Mage::getStoreConfig('monkey/general/checkout_async')) {
                $collection = Mage::getModel('monkey/asyncorders')->getCollection();
                $alreadyOnDb = false;
                foreach ($collection as $order) {
                    $info = unserialize($order->getInfo());
                    if ($info['order_id'] == $this->_order->getId()) {
                        $alreadyOnDb = true;
                    }
                }
                if (!$alreadyOnDb) {
                    $sync = Mage::getModel('monkey/asyncorders');
                    $this->_info['order_id'] = $this->_order->getId();
                    $sync->setInfo(serialize($this->_info))
                        ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
                        ->setProcessed(0)
                        ->save();
                    $rs = true;
                } else {
                    $rs = 'Order already sent or ready to get sent soon';
                }
            } else {
                $rs = $api->ecommOrderAdd($this->_info);
            }
        }

        if ($rs === TRUE) {
            $this->_logCall();
            return true;
        } else {
            return $rs;
        }

    }

    /**
     * Process order items to send to MailChimp
     *
     * @access private
     * @return Ebizmarts_MageMonkey_Model_Ecommerce360
     */
    private function setItemstoSend($storeId)
    {
        foreach ($this->_order->getAllItems() as $item) {
            $mcitem = array();
            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            if (in_array($product->getTypeId(), $this->_productsToSkip) && $product->getPriceType() == 0) {
                if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $this->_auxPrice = $item->getBasePrice();
                }
                continue;
            }

            $mcitem['product_id'] = $product->getEntityId();
            $mcitem['sku'] = $product->getSku();
            $mcitem['product_name'] = $product->getName();
            $attributesToSend = explode(',', Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::ECOMMERCE360_ATTRIBUTES, $storeId));
            $attributes = $product->getAttributes();
            $productAttributes = '';
            $pipe = false;
            foreach ($attributes as $attribute) {
                if ($pipe) {
                    $productAttributes .= '|';
                }
                if (in_array($attribute->getAttributeCode(), $attributesToSend) && is_string($attribute->getFrontend()->getValue($product)) && trim($attribute->getFrontend()->getValue($product)) != '') {
                    $productAttributes .= $attribute->getAttributeCode() . ':' . $attribute->getFrontend()->getValue($product);
                    $pipe = true;
                } else {
                    $pipe = false;
                }
            }
            if ($productAttributes) {
                $mcitem['product_name'] .= '[' . $productAttributes . ']';
            }

            $names = array();
            $cat_ids = $product->getCategoryIds();

            if (is_array($cat_ids) && count($cat_ids) > 0) {
                $category = Mage::getModel('catalog/category')->load($cat_ids[0]);
                $mcitem['category_id'] = $cat_ids[0];
                $names[] = $category->getName();
                while ($category->getParentId() && $category->getParentId() != 1) {
                    $category = Mage::getModel('catalog/category')->load($category->getParentId());
                    $names[] = $category->getName();
                }
            }
            if (!isset($mcitem['category_id'])) {
                $mcitem['category_id'] = 0;
            }
            $mcitem['category_name'] = (count($names)) ? implode(" - ", array_reverse($names)) : 'None';
            $mcitem['qty'] = $item->getQtyOrdered();
            $mcitem['cost'] = ($this->_auxPrice > 0) ? $this->_auxPrice : $item->getBasePrice();
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
            ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
            ->setStoreId($this->_order->getStoreId())
            ->save();
    }

    /** Send order to MailChimp Automatically by Order Status
     *
     *
     */
    public function autoExportJobs($storeId)
    {
        $allow_sent = false;
        //Get status options selected in the Configuration
        $states = explode(',', Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::ECOMMERCE360_ORDER_STATUS, $storeId));
        $max = Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::ECOMMERCE360_ORDER_MAX, $storeId);
        $count = 0;
        foreach ($states as $state) {
            if ($max == $count) {
                break;
            }
            $ecommerceTable = Mage::getSingleton('core/resource')->getTableName('monkey/ecommerce');
            if ($state != 'all_status') {
                $orders = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('main_table.store_id', array('eq' => $storeId));
//                $orders->getSelect()->joinLeft(array('ecommerce' => Mage::getSingleton('core/resource')->getTableName('monkey/ecommerce')), 'main_table.entity_id = ecommerce.order_id', 'main_table.*')->where('ecommerce.order_id is null AND main_table.status = \'' . $state . '\'')
//                    ->limit($max - $count);
                $orders->getSelect()->where('main_table.status = \'' . $state . '\' ' .
                    'AND main_table.entity_id NOT IN ' .
                    "(SELECT ecommerce.order_id FROM {$ecommerceTable} AS ecommerce WHERE ecommerce.store_id = {$storeId})")
                    ->limit($max - $count);
            } else {
                $orders = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('main_table.store_id', array('eq' => $storeId));
//                $orders->getSelect()->joinLeft(array('ecommerce' => Mage::getSingleton('core/resource')->getTableName('monkey/ecommerce')), 'main_table.entity_id = ecommerce.order_id', 'main_table.*')->where('ecommerce.order_id is null')
//                    ->limit($max - $count);
                $orders->getSelect()->where('main_table.entity_id NOT IN ' .
                    "(SELECT ecommerce.order_id FROM {$ecommerceTable} AS ecommerce WHERE ecommerce.store_id = {$storeId})")
                    ->limit($max - $count);
            }
            $count += count($orders);
            foreach ($orders as $order) {

                $this->_order = $order;
                $ordersToSend = Mage::getModel('monkey/asyncorders')->getCollection()
                    ->addFieldToFilter('processed', array('eq' => 0));
                foreach ($ordersToSend as $orderToSend) {
                    $info = (array)unserialize($orderToSend->getInfo());
                    if ($this->_order->getIncrementId() == $info['id']) {
                        continue;
                    }
                }

                $api = Mage::getSingleton('monkey/api', array('store' => $this->_order->getStoreId()));
                if (!$api) {
                    return false;
                }

                $subtotal = $this->_order->getBaseSubtotal();
                $discount = (float)$this->_order->getBaseDiscountAmount();
                if ($discount != 0) {
                    $subtotal = $subtotal + ($discount);
                }

                $this->_info = array(
                    'id' => $this->_order->getIncrementId(),
                    'total' => $subtotal,
                    'shipping' => $this->_order->getBaseShippingAmount(),
                    'tax' => $this->_order->getBaseTaxAmount(),
                    'store_id' => $this->_order->getStoreId(),
                    'store_name' => $this->_order->getStoreName(),
                    'order_date' => $this->_order->getCreatedAt(),
                    'plugin_id' => 1215,
                    'items' => array()
                );

                $email = $this->_order->getCustomerEmail();
                $campaign = $this->_order->getEbizmartsMagemonkeyCampaignId();
                $this->setItemstoSend($storeId);
                $rs = false;
                if ($email && $campaign) {
                    $this->_info ['email_id'] = $email;
                    $this->_info ['campaign_id'] = $campaign;

                    if (Mage::getStoreConfig('monkey/general/checkout_async', Mage::app()->getStore()->getId())) {
                        $sync = Mage::getModel('monkey/asyncorders');
                        $this->_info['order_id'] = $this->_order->getId();
                        $sync->setInfo(serialize($this->_info))
                            ->setCreatedAt($this->_order->getCreatedAt())//Mage::getModel('core/date')->gmtDate())
                            ->setProcessed(0)
                            ->save();
                        $rs['complete'] = true;
                    } else {
                        //Send order to MailChimp
                        $rs = $api->campaignEcommOrderAdd($this->_info);
                    }
                } else {
                    $this->_info ['email'] = $email;
                    if (Mage::getStoreConfig('monkey/general/checkout_async', Mage::app()->getStore()->getId())) {
                        $sync = Mage::getModel('monkey/asyncorders');
                        $this->_info['order_id'] = $this->_order->getId();
                        $sync->setInfo(serialize($this->_info))
                            ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
                            ->setProcessed(0)
                            ->save();
                        $rs['complete'] = true;
                    } else {
                        $rs = $api->ecommOrderAdd($this->_info);
                    }
                }
                if (isset($rs['complete']) && $rs['complete'] == TRUE && !Mage::getStoreConfig('monkey/general/checkout_async', Mage::app()->getStore()->getId())) {
                    $order = Mage::getModel('monkey/ecommerce')
                        ->setOrderIncrementId($this->_info['id'])
                        ->setOrderId($this->_info['order_id'])
                        ->setMcEmailId($this->_info ['email'])
                        ->setCreatedAt($this->_order->getCreatedAt())
                        ->setStoreId($this->_info['store_id']);
                    if (isset($this->_info['campaign_id']) && $this->_info['campaign_id']) {
                        $order->setMcCampaignId($this->_info['campaign_id']);
                    }
                    $order->save();
                    //$this->_logCall();
                }
            }
        }
    }
}
