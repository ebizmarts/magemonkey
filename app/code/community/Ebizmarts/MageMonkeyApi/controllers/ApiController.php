<?php

class Ebizmarts_MageMonkeyApi_ApiController extends Mage_Core_Controller_Front_Action {

    private $_start;
    private $_end;

    /**
     * Predispatch: Check for valid API KEY parameter.
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function preDispatch() {

        $this->_start = microtime(true);

        parent::preDispatch();

        if ( !$this->getRequest()->isDispatched() )
            return;

        $action = $this->getRequest()->getActionName();
        $openActions = array(
            'activate',
        );
        $pattern = '/^(' . implode('|', $openActions) . ')/i';

        if ( !preg_match($pattern, $action) ) {

            //Check for valid api key/uuid combination.
            $postData = $this->_jsonPayload();

            $app = Mage::getResourceModel('monkeyapi/application_collection')
                ->setApiKeyFilter($postData->api_key)
                ->setUuidFilter($postData->uuid)
                ->setOnlyEnabledApiKeyFilter()
                ->setActiveDeviceFilter()
                ->setPageSize(1)
                ->getFirstItem();

            if(!$app->getId()) {
                $this->_setClientError(400, 4004);
                $this->setFlag('', 'no-dispatch', true);
                return;
            }
            else {
               $app
                   ->setLastCallTs( Mage::getModel('core/date')->gmtTimestamp() )->save();
            }

        }

    }

    /**
     * Postdispatch: should set last visited url
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function postDispatch() {
        parent::postDispatch();

        $log = Mage::getModel('monkeyapi/log');

        $log->setHttpUserAgent(Mage::helper('core/http')->getHttpUserAgent(true));

        $bodyRaw    = json_decode($this->getRequest()->getRawBody());
        $rawBodyEnc = array($bodyRaw);
        $allParams  = $this->getRequest()->getParams();

        $log->setHttpParams(json_encode(array_merge($rawBodyEnc, $allParams)));

        $log->setCallMethod($this->getRequest()->getActionName());

        $log->setRemoteAddr(Mage::helper('core/http')->getRemoteAddr(false));

        if($bodyRaw !== false && is_object($bodyRaw))
            $log->setUuid($bodyRaw->uuid);

        //Should be always a stringyfied JSON
        $responseBody = $this->getResponse()->getBody();
        if(is_string($responseBody))
            $log->setResponseParams($responseBody);

        $log->setResponseHeaders(json_encode($this->getResponse()->getHeaders()));

        $log->setResponseCode($this->getResponse()->getHttpResponseCode());

        $log->save();

        $this->_end = microtime(true);

        $log->setCallTime($this->_end-$this->_start)->save();

        return $this;
    }

    /**
     * Activate device action.
     */
    public function activateAction() {
        if($this->getRequest()->isPost()) {

            $postData = $this->_jsonPayload();

            if( false === $postData ) {
                $this->_setClientError(400, 4001);
                return;
            }

            if( !isset($postData->key) ) {
                $this->_setClientError(400, 4002);
                return;
            }

            if( !isset($postData->uuid) ) {
                $this->_setClientError(400, 4005);
                return;
            }

            $activationKey = $postData->key;

            $app = Mage::getResourceModel('monkeyapi/application_collection')->setKeyFilter($activationKey)
            ->setPageSize(1)
            ->getFirstItem();

            if( !$app->getId() or (1 === (int)$app->getActivated()) ) {
                $this->_setClientError(400, 4003);
                return;
            }

            $app
                ->setUuid($postData->uuid)
                ->setLastCallTs(Mage::getModel('core/date')->gmtTimestamp())
                ->setApplicationName($postData->app_info->description)
                ->setDeviceInfo( json_encode($postData->device_info) )
                ->setAppInfo( json_encode($postData->app_info) )
                ->setActivated(1)->save();

            $this->_setSuccess(200, array('api_key' => $app->getApplicationRequestKey()));
            return;

        }
        else {
            $this->_setClientError(405, 4051);
            return;
        }
    }

    /**
     * Abandoned Carts statistics.
     */
    public function abandonedcartstatsAction() {

        if( !$this->getRequest()->isPost() ) {
            $this->_setClientError(405, 4052);
            return;
        }

        $post = $this->_jsonPayload();

        //Filters.
        $filterPeriod = isset($post->period) ? $post->period : 'lifetime';
        $this->getRequest()->setParam('period', $filterPeriod);

        $filterStoreID = isset($post->store) ? $post->store : null;
        $this->getRequest()->setParam('store', $filterStoreID);

        $block = new Ebizmarts_AbandonedCart_Block_Adminhtml_Dashboard_Totals;
        $block->setLayout( (new Mage_Core_Model_Layout()) );

        $totals = $block->abandonedCartTotals();

        $stats = new stdClass();

        $string = Mage::helper('core/string');

        foreach($totals as $_t) {
            $propName = strtolower( implode('_', $string->splitWords($_t['label'])) );

            $propName = preg_replace('/[^A-Za-z0-9_]/', '', $propName);

            $stats->{$propName} = $string->stripTags($_t['value']);
        }

        $this->_setSuccess(200, $stats);
        return;

    }

    /**
     * Return Magento statistics.
     */
    public function magentostatsAction() {

        if( !$this->getRequest()->isPost() ) {
            $this->_setClientError(405, 4051);
            return;
        }

        $collection = Mage::getResourceModel('reports/order_collection')->calculateSales(false)->load();
        $sales      = $collection->getFirstItem();

        $collectionTotals = Mage::getResourceModel('reports/order_collection')->calculateTotals(false)->load();
        $totals = $collectionTotals->getFirstItem();

        $currencyObj = new stdClass();
        $currencyObj->code   = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
        $currencyObj->symbol = Mage::app()->getLocale()->currency($currencyObj->code)->getSymbol();

        $statsRet = array(
            'base_currency'          => $currencyObj,
            'lifetime_sales'         => is_null($sales->getLifetime()) ? "0.00" : $sales->getLifetime(),
            'lifetime_orders_qty'    => ($totals->getQuantity() * 1),
            'lifetime_customers_qty' => Mage::getResourceModel('customer/customer_collection')->getSize(),
        );

        $this->_setSuccess(200, $statsRet);
        return;

    }

    /**
     * Return Magento website information.
     */
    public function websiteinfoAction() {

        if( !$this->getRequest()->isPost() ) {
            $this->_setClientError(405, 4051);
            return;
        }

        //Magento Edition
        $modulesArray = (array) Mage::getConfig()->getNode('modules')->children();
        $edition      = (array_key_exists('Enterprise_Enterprise', $modulesArray)) ? 'EE' : 'CE';

        //Stores
        $websiteRet = array();

        $websiteColl = Mage::getModel('core/website')
            ->getCollection()
            ->joinGroupAndStore();
        foreach($websiteColl as $_w) {
            $websiteRet []= $_w->getName() .'/'. $_w->getGroupTitle() .'/'. $_w->getStoreTitle();
        }

        $websiteInfo = array(
          'magento_edition'    => $edition,
          'magento_version'    => Mage::getVersion(),
          'magento_websites'   => $websiteRet,
          'magemonkey_version' => (string) Mage::getConfig()->getNode('modules/Ebizmarts_MageMonkey/version')
        );

        $this->_setSuccess(200, $websiteInfo);
        return;
    }

    private function _jsonPayload() {
        $payload = $this->getRequest()->getRawBody();

        $data = json_decode($payload);

        if( !is_object($data) or empty($payload) ) {
            $data = false;
        }

        return $data;
    }

    private function _setSuccess($httpCode, $content) {
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json', true)
            ->setHttpResponseCode($httpCode)
            ->setBody(json_encode($content));
    }

    private function _setClientError($httpCode, $code) {
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json', true)
            ->setHttpResponseCode($httpCode)
            ->setBody($this->_error($code));
    }

    private function _error($code) {

        $message = '';

        //@see config.xml
        $errors = Mage::getConfig()->getNode('global/monkeyapi_errorcodes')->children();

        foreach($errors as $_error) {
            if( ((int)$_error->code) == $code) {
                $message = (string)$_error->message;
                break;
            }
        }

        return json_encode(
            array('error_code' => $code,
            'error_message' => $message)
        );

    }


}