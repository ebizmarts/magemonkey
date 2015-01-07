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

        $log->setHttpParams(json_encode($this->_httpParams()));

        $log->setCallMethod($this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName());

        $log->setRemoteAddr(Mage::helper('core/http')->getRemoteAddr(false));

        $bodyRaw = json_decode($this->getRequest()->getRawBody());
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
     * return array
     */
    protected function _httpParams() {
        $bodyRaw    = json_decode($this->getRequest()->getRawBody());
        $rawBodyEnc = array($bodyRaw);
        $allParams  = $this->getRequest()->getParams();

        return array_merge($rawBodyEnc, $allParams);
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

    public function dashboardAction() {
        if( !$this->getRequest()->isPost() ) {
            $this->_setClientError(405, 4052);
            return;
        }

        $this->_setFilters();

        $periodFilter = $this->getRequest()->getParam('period');

        $acStats   = $this->_abandonedcartstats($periodFilter);
        $mageStats = $this->_magentostats($periodFilter);

        $statsRet  = array_merge($acStats, $mageStats);

        $this->_setSuccess(200, $statsRet);
        return;

    }

    /**
     * Abandoned Carts statistics.
     */
    public function abandonedcartstatsAction() {

        /*
        Available filters:
        24h - Last 24 hours
        7d - Last 7 days
        30d - Last 30 days
        60d - Last 60 days
        90d - Last 90 days
        lifetime - Lifetime
        */

        if( !$this->getRequest()->isPost() ) {
            $this->_setClientError(405, 4052);
            return;
        }

        //Filters.
        $this->_setFilters();

        $periodFilter = $this->getRequest()->getParam('period');

        $statsRet = $this->_abandonedcartstats($periodFilter);

        $this->_setSuccess(200, $statsRet);
        return;

    }

    protected function _abandonedcartstats($periodFilter) {
        if(is_array($periodFilter)) {

            $statsRet = array();

            foreach($periodFilter as $_period)
                array_push($statsRet, $this->abandonedcartstats($_period));

        }
        else
            $statsRet = array($this->abandonedcartstats());

        return $statsRet;
    }

    private function abandonedcartstats($periodParam = null) {

        if( !is_null($periodParam) )
            $this->getRequest()->setParam('period', $periodParam);


        $block = new Ebizmarts_AbandonedCart_Block_Adminhtml_Dashboard_Totals;
        $block->setLayout( (new Mage_Core_Model_Layout()) );

        $totals = $block->abandonedCartTotals();

        $stats = new stdClass();

        foreach($totals as $key => $value) {
            $stats->{$key} = $value;
        }

        $stats->base_currency = Mage::helper('monkeyapi')->defaultCurrency();
        $stats->period = $this->getRequest()->getParam('period');
        $stats->type   = 'abandonedcartstats';

        return $stats;
    }

    /**
     * Return Magento statistics.
     */
    public function magentostatsAction() {

        /*
        Available filters:
        24h - Last 24 hours
        7d - Last 7 days
        1m - Current Month
        1y - YTD
        2y - 2YTD
        */

        if( !$this->getRequest()->isPost() ) {
            $this->_setClientError(405, 4051);
            return;
        }

        //Filters.
        $this->_setFilters();

        $periodFilter = $this->getRequest()->getParam('period');

        $statsRet = $this->_magentostats($periodFilter);

        $this->_setSuccess(200, $statsRet);
        return;

    }

    protected function _magentostats($periodFilter) {
        if(is_array($periodFilter)) {

            $statsRet = array();

            foreach($periodFilter as $_period)
                array_push($statsRet, $this->magentostats($_period));

        }
        else
            $statsRet = array($this->magentostats());

        return $statsRet;
    }

    private function magentostats($periodParam = null) {
        $isFilter = is_null($this->getRequest()->getParam('store', null)) ? 0 : 1;

        $collection = Mage::getResourceModel('reports/order_collection')->calculateSales($isFilter)->load();
        $sales      = $collection->getFirstItem();

        $collectionTotals = Mage::getResourceModel('reports/order_collection');

        $period = is_null($periodParam) ? $this->getRequest()->getParam('period') : $periodParam;
        if($period == 'lifetime') {
            //$rcoll = Mage::getResourceModel('reports/order_collection');

            $_firstOrder = Mage::getResourceModel('sales/order_grid_collection')
                            ->setOrder('created_at', 'ASC')
                            ->setPageSize(1)
                            ->load()
                            ->getFirstItem();

            $customStart = Mage::app()->getLocale()->date($_firstOrder->getCreatedAt());
            $customStart->setHour(0);
            $customStart->setMinute(0);
            $customStart->setSecond(0);
            $customStart->setTimezone('Etc/UTC');

            $lifetimeRange   = $collectionTotals->getDateRange('custom', $customStart, 0, true);
            list($from, $to) = $lifetimeRange;

            $collectionTotals->checkIsLive('2y');

            if ($collectionTotals->isLive())
                $fieldToFilter = 'created_at';
            else
                $fieldToFilter = 'period';

            $collectionTotals->addFieldToFilter($fieldToFilter, array(
                'from'  => $from->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
                'to'    => $to->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
            ));

        }
        else
            $collectionTotals->addCreateAtPeriodFilter($period);

        $collectionTotals->calculateTotals($isFilter)->load();
        $totals = $collectionTotals->getFirstItem();

        return array(
            'base_currency'          => Mage::helper('monkeyapi')->defaultCurrency(),
            'lifetime_sales'         => is_null($sales->getLifetime()) ? "0.00" : Mage::helper('monkeyapi')->formatFloat($sales->getLifetime()),
            'average_sales'          => is_null($sales->getAverage()) ? "0.00" : Mage::helper('monkeyapi')->formatFloat($sales->getAverage()),
            'lifetime_customers_qty' => Mage::getResourceModel('customer/customer_collection')->getSize(),//@ToDo: period_customers_qty
            'period_orders_qty'      => ($totals->getQuantity() * 1),
            'period_revenue'         => Mage::helper('monkeyapi')->formatFloat($totals->getRevenue()),
            'period_tax'             => Mage::helper('monkeyapi')->formatFloat($totals->getTax()),
            'period_shipping'        => Mage::helper('monkeyapi')->formatFloat($totals->getShipping()),
            'period'                 => $period,
            'type'                   => 'magentostats'
        );
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

            $_wInfo = array(
              'store_id'         => (int)$_w->getStoreId(),
              'store_name'       => $_w->getGroupTitle(),
              'store_view_name'  => $_w->getStoreTitle(),
              'store_is_default' => (int)((int)$_w->getDefaultGroupId() == (int)$_w->getStoreId()),
            );

            if(!array_key_exists($_w->getWebsiteId(), $websiteRet)) {
                $websiteRet[$_w->getWebsiteId()] = array(
                    'website_id'         => (int) $_w->getWebsiteId(),
                    'website_name'       => $_w->getName(),
                    'website_code'       => $_w->getCode(),
                    'website_is_default' => (int) $_w->getIsDefault(),
                    );
                $websiteRet[$_w->getWebsiteId()]['stores'] = array();
            }

            $websiteRet[$_w->getWebsiteId()]['stores'][] = $_wInfo;

        }

        $websiteInfo = array(
          'magento_edition'    => $edition,
          'magento_version'    => Mage::getVersion(),
          'magento_websites'   => array_values($websiteRet),
          'magemonkey_version' => (string) Mage::getConfig()->getNode('modules/Ebizmarts_MageMonkey/version')
        );

        $this->_setSuccess(200, $websiteInfo);
        return;
    }

    public function ordersAction() {
        $this->_forward('index', 'api_orders', null, $this->_httpParams());
        return;
    }

    public function customersAction() {
        $this->_forward('index', 'api_customers', null, $this->_httpParams());
        return;
    }

    protected function _jsonPayload() {
        $payload = $this->getRequest()->getRawBody();

        $data = json_decode($payload);

        if( !is_object($data) or empty($payload) ) {
            $data = false;
        }

        return $data;
    }

    protected function _setSuccess($httpCode, $content) {
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json', true)
            ->setHttpResponseCode($httpCode)
            ->setBody(json_encode($content));
    }

    protected function _setClientError($httpCode, $code) {
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json', true)
            ->setHttpResponseCode($httpCode)
            ->setBody($this->_error($code));
    }

    protected function _error($code) {

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

    private function _setFilters() {
        $post = $this->_jsonPayload();

        //Filters.
        $filterPeriod = isset($post->period) ? $post->period : '24h';
        $this->getRequest()->setParam('period', $filterPeriod);

        $filterStoreID = isset($post->store) ? $post->store : null;
        $this->getRequest()->setParam('store', $filterStoreID);
    }

}