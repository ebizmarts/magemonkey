<?php

/**
 * Mage Monkey default helper
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Utility to check if admin is logged in
     *
     * @return bool
     */
    public function isAdmin()
    {
        return Mage::getSingleton('admin/session')->isLoggedIn();
    }

    /**
     * Check if Magento is EE
     *
     * @return bool
     */
    public function isEnterprise()
    {
        return is_object(Mage::getConfig()->getNode('global/models/enterprise_enterprise'));
    }


    /**
     * Whether Admin Notifications should be displayed or not in backend Admin
     *
     * @return bool
     */
    public function isAdminNotificationEnabled()
    {
        return $this->config('adminhtml_notification');
    }

    /**
     * Return Webhooks security key for given store
     *
     * @param mixed $store Store object, or Id, or code
     * @param string $listId Optional listid to retrieve store code from it
     * @return string
     */
    public function getWebhooksKey($store = null, $listId = null)
    {
        if (!is_null($listId)) {
            $store = $this->getStoreByList($listId, TRUE);
        }

        $crypt = md5((string)Mage::getConfig()->getNode('global/crypt/key'));
        $key = substr($crypt, 0, (strlen($crypt) / 2));

        // Prevent most cases to attach default in webhook url
        if (!$store || $store == 'default') $store = '';

        return ($key . $store);
    }

    public function filterShowGroupings($interestGroupings)
    {
        if (is_array($interestGroupings)) {

            $customGroupings = (array)Mage::getConfig()->getNode('default/monkey/custom_groupings');
            foreach ($interestGroupings as $key => $group) {

                if (TRUE === in_array($group['name'], $customGroupings)) {
                    unset($interestGroupings[$key]);
                }

            }
        }

        return $interestGroupings;
    }

    /**
     * Check if CustomerGroup grouping already exists on MC
     *
     * @param array $groupings
     * @return bool
     */
    public function customerGroupGroupingExists($interestGroupings)
    {
        $exists = FALSE;
        if (is_array($interestGroupings)) {
            foreach ($interestGroupings as $group) {
                if ($group['name'] == $this->getCustomerGroupingName()) {
                    $exists = TRUE;
                    break;
                }
            }
        }

        return $exists;
    }

    /**
     * Return customer groping name to be used when creating a grouping to store
     * Magento customer groups
     *
     * @return string
     */
    public function getCustomerGroupingName()
    {
        return (string)Mage::getConfig()->getNode('default/monkey/custom_groupings/customer_grouping_name');
    }

    /**
     * Get module User-Agent to use on API requests
     *
     * @return string
     */
    public function getUserAgent()
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;

        $aux = (array_key_exists('Enterprise_Enterprise', $modulesArray)) ? 'EE' : 'CE';
        $v = (string)Mage::getConfig()->getNode('modules/Ebizmarts_MageMonkey/version');
        $version = strpos(Mage::getVersion(), '-') ? substr(Mage::getVersion(), 0, strpos(Mage::getVersion(), '-')) : Mage::getVersion();
        return (string)'MageMonkey' . $v . '/Mage' . $aux . $version;
    }

    /**
     * Return Mandrill API key
     *
     * @param string $store
     * @return string Api Key
     */
    public function getMandrillApiKey($store = null)
    {
        if (is_null($store)) {
            $key = $this->config('mandrill_apikey');
        } else {
            $curstore = Mage::app()->getStore();
            Mage::app()->setCurrentStore($store);
            $key = $this->config('mandrill_apikey', $store);
            Mage::app()->setCurrentStore($curstore);
        }

        return $key;
    }

    /**
     * Return MC API key for given store, if none is given
     * default key is returned
     *
     * @param string $store
     * @return string Api Key
     */
    public function getApiKey($store = null)
    {
        if (is_null($store)) {
            $key = $this->config('apikey');
        } else {
            $curstore = Mage::app()->getStore();
            Mage::app()->setCurrentStore($store);
            $key = $this->config('apikey', $store);
            Mage::app()->setCurrentStore($curstore);
        }

        return $key;
    }

    /**
     * Logging facility
     *
     * @param mixed $data Message to save to file
     * @param string $filename log filename, default is <Monkey.log>
     * @return Mage_Core_Model_Log_Adapter
     */
    public function log($data, $filename = 'Monkey.log')
    {
        if ($this->config('enable_log') != 0) {
            return Mage::getModel('core/log_adapter', $filename)->log($data);
        }
    }

    /**
     * Get module configuration value
     *
     * @param string $value
     * @param string $store
     * @return mixed Configuration setting
     */
    public function config($value, $store = null)
    {
        $store = is_null($store) ? Mage::app()->getStore() : $store;

        $configscope = Mage::app()->getRequest()->getParam('store');
        $ret = null;
        if ($configscope && ($configscope !== 'undefined') && !is_array($configscope)) {
            if (is_array($configscope) && isset($configscope['code'])) {
                $store = $configscope['code'];
            } else {
                $store = $configscope;
            }
            $storeExists = Mage::getModel('core/store')->load($store);
            if ($storeExists->getId()) {
                $ret = Mage::getStoreConfig("monkey/general/$value", $storeExists->getId());
            }
        }
        if (!$ret) {
            $ret = Mage::getStoreConfig("monkey/general/$value", $store);
        }
        return $ret;
    }

    /**
     * Check if config setting <checkout_subscribe> is enabled
     *
     * @return bool
     */
    public function canCheckoutSubscribe()
    {
        return $this->config('checkout_subscribe');
    }

    /**
     * Check if an email is subscribed on MailChimp
     *
     * @param string $email
     * @param string $listId
     * @return bool
     */
    public function subscribedToList($email, $listId = null)
    {
        $on = FALSE;

        if ($email) {
            $member = Mage::getSingleton('monkey/api')
                ->listMemberInfo($listId, $email);

            if (!is_string($member) && $member['success'] && ($member['data'][0]['status'] == 'subscribed' || $member['data'][0]['status'] == 'pending')) {
                $on = TRUE;
            }
        }

        return $on;
    }

    /**
     * Check if Ecommerce360 integration is enabled
     *
     * @return bool
     */
    public function ecommerce360Active()
    {
        $storeId = Mage::app()->getStore()->getId();
        return (bool)(Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::ECOMMERCE360_ACTIVE, $storeId) != 0);
    }

    /**
     * Check if Transactional Email via MC is enabled
     *
     * @return bool
     */
    public function useTransactionalService()
    {
        return Mage::getStoreConfigFlag("monkey/general/transactional_emails");
    }

    /**
     * Check if Ebizmarts_MageMonkey module is enabled
     *
     * @return bool
     */
    public function canMonkey($stores = null)
    {
        $ret = false;
        if (is_array($stores)) {
            foreach ($stores as $store) {
                if ((bool)((int)$this->config('active', $store) !== 0)) {
                    $ret = true;
                }
            }
        } else {
            $ret = (bool)((int)$this->config('active') !== 0);
        }
        return $ret;
    }

    /**
     * Get default MC listId for given storeId
     *
     * @param string $store
     * @return string $list
     */
    public function getDefaultList($store)
    {
        $curstore = Mage::app()->getStore();
        Mage::app()->setCurrentStore($store);
        $list = $this->config('list', $store);
        Mage::app()->setCurrentStore($curstore);
        return $list;
    }

    /**
     * Get additional Lists by storeId
     *
     * @param string $store
     * @return string $list
     */
    public function getAdditionalList($store)
    {
        $curstore = Mage::app()->getStore();
        Mage::app()->setCurrentStore($store);
        $list = $this->config('additional_lists', $store);
        Mage::app()->setCurrentStore($curstore);
        return $list;
    }

    /**
     * Get which store is associated to given $mcListId
     *
     * @param string $mcListId
     * @param bool $includeDefault Include <default> store or not on result
     * @return string $store
     */
    public function getStoreByList($mcListId, $includeDefault = FALSE)
    {
        $list = Mage::getModel('core/config_data')->getCollection()
            ->addValueFilter($mcListId)->getFirstItem();

        $store = null;
        if ($list->getId()) {

            //$isDefault = (bool)($list->getScope() == 'default');
            $isDefault = (bool)($list->getScope() == Mage::app()->getDefaultStoreView()->getCode());
            if (!$isDefault && !$includeDefault) {
                $store = (string)Mage::app()->getStore($list->getScopeId())->getCode();
            } else {
                $store = $list->getScope();
            }

        }

        return $store;
    }

    /**
     * Check if current request is a Webhooks request
     *
     * @return bool
     */
    public function isWebhookRequest()
    {
        $rq = Mage::app()->getRequest();
        $monkeyRequest = (string)'monkeywebhookindex';
        $thisRequest = (string)($rq->getRequestedRouteName() . $rq->getRequestedControllerName() . $rq->getRequestedActionName());

        return (bool)($monkeyRequest === $thisRequest);
    }

    /**
     * Get config setting <map_fields>
     *
     * @return array|FALSE
     */
    public function getMergeMaps($storeId)
    {
        return unserialize($this->config('map_fields', $storeId));
    }

    /**
     * Get progress bar HTML code
     *
     * @param integer $complete Processed qty so far
     * @param integer $total Total qty to process
     * @return string
     */
    public function progressbar($complete, $total)
    {
        if ($total == 0) {
            return;
        }
        $percentage = round(($complete * 100) / $total, 0);

        $barStyle = '';
        if ($percentage > 0) {
            $barStyle = " style=\"width: $percentage%\"";
        }

        $html = "<div id=\"bar-progress-bar\" class=\"bar-all-rounded\">\n";
        $html .= "<div id=\"bar-progress-bar-percentage\" class=\"bar-all-rounded\"$barStyle>";
        $html .= "$percentage% ($complete of $total)";
        //<progress value="75" max="100">3/4 complete</progress>
        //if ($percentage > 5) {$html .= "$percentage% ($complete of $total)";} else {$html .= "<div class=\"bar-spacer\">&nbsp;</div>";}
        $html .= "</div></div>";

        return $html;
    }

    /**
     * Return Merge Fields mapped to Magento attributes
     *
     * @param object $customer
     * @param bool $includeEmail
     * @param integer $websiteId
     * @return array
     */
    public function getMergeVars($customer, $includeEmail = FALSE, $websiteId = NULL)
    {
        $mergeVars = array();
        $maps = $this->getMergeMaps($customer->getStoreId());

        if (!$maps && !$customer->getListGroups()) {
            return;
        }

        $request = Mage::app()->getRequest();

        //Add Customer data to Subscriber if is Newsletter_Subscriber is Customer
        if (!$customer->getDefaultShipping() && $customer->getEntityId()) {
            $customer->addData(
                Mage::getModel('customer/customer')->load($customer->getEntityId())
                ->setStoreId($customer->getStoreId())
                ->toArray()
            );
        } elseif ($customer->getCustomerId()) {
            $customer->addData(
                Mage::getModel('customer/customer')->load($customer->getCustomerId())
                ->setStoreId($customer->getStoreId())
                ->toArray()
            );
        }

        $mergeVars = $this->_setMaps($maps, $customer, $mergeVars, $websiteId);

        //GUEST
        $guestFirstName = '';
        if (!$customer->getId() && !$request->getPost('firstname')) {
            if ($customer->getSubscriberFirstname()) {
                $guestFirstName = $this->config('guest_name', $customer->getStoreId());
            } elseif ($this->config('guest_name', $customer->getStoreId())) {
                $guestFirstName = $this->config('guest_name', $customer->getStoreId());
            }

            if ($guestFirstName) {
                $mergeVars['FNAME'] = $guestFirstName;
            }
        }
        $guestLastName = '';
        if (!$customer->getId() && !$request->getPost('lastname')) {
            if ($customer->getSubscriberLastname()) {
                $guestLastName = $this->config('guest_lastname', $customer->getStoreId());
            } elseif ($this->config('guest_lastname', $customer->getStoreId())) {
                $guestLastName = $this->config('guest_lastname', $customer->getStoreId());
            }

            if ($guestLastName) {
                $mergeVars['LNAME'] = $guestLastName;
            }
        }
        //GUEST

        if ($includeEmail) {
            $mergeVars['EMAIL'] = $customer->getEmail();
        }

        $groups = $customer->getListGroups();
        $groupings = array();

        if (is_array($groups) && count($groups)) {
            foreach ($groups as $groupId => $grupoptions) {
                if (is_array($grupoptions)) {
                    $grupOptionsEscaped = array();
                    foreach ($grupoptions as $gopt) {
                        $gopt = str_replace(",", "%C%", $gopt);
                        $grupOptionsEscaped[] = $gopt;
                    }
                    $groupings[] = array(
                        'id' => $groupId,
                        'groups' => str_replace('%C%', '\\,', implode(', ', $grupOptionsEscaped))
                    );
                } else {
                    $groupings[] = array(
                        'id' => $groupId,
                        'groups' => str_replace(',', '\\,', $grupoptions)
                    );
                }
            }
        }

        $mergeVars['GROUPINGS'] = $groupings;

        //magemonkey_mergevars_after
        $blank = new Varien_Object;
        Mage::dispatchEvent(
            'magemonkey_mergevars_after',
            array('vars' => $mergeVars, 'customer' => $customer, 'newvars' => $blank)
        );
        if ($blank->hasData()) {
            $mergeVars = array_merge($mergeVars, $blank->toArray());
        }
        //magemonkey_mergevars_after
        return $mergeVars;
    }

    public function getMergeVarsFromOrder($maps, $order, $mergeVars)
    {

        foreach ($maps as $map) {

            $customAtt = $map['magento'];
            $chimpTag = $map['mailchimp'];

            if ($chimpTag && $customAtt) {

                $key = strtoupper($chimpTag);

                switch ($customAtt) {

                    case 'billing_address':
                    case 'shipping_address':
                        $mergeVars = array_merge($mergeVars, $this->_setAddress($customAtt, $mergeVars, $order, $key));
                        break;
                    case 'date_of_purchase':

                            $mergeVars[$key] = $order->getUpdatedAt();

                        break;

                    case 'store_code':
                        $storeId = (string)$order->getData('store_id');
                        $storeCode = Mage::getModel('core/store')->load($storeId)->getCode();
                        if ($storeCode) {
                            $mergeVars[$key] = $storeCode;
                        }
                        break;
                    case 'fname':
                        $mergeVars[$key] = $order->getCustomerFirstname();
                        break;
                    case 'lname':
                        $mergeVars[$key] = $order->getCustomerLastname();
                        break;
                }

            }
        }
        return $mergeVars;
    }
    private function _setMaps($maps,$customer,$mergeVars, $websiteId)
    {
        foreach ($maps as $map) {
            $request = Mage::app()->getRequest();

            $customAtt = $map['magento'];
            $chimpTag = $map['mailchimp'];

            if ($chimpTag && $customAtt) {

                $key = strtoupper($chimpTag);

                switch ($customAtt) {
                    case 'gender':
                        $val = (int)$customer->getData(strtolower($customAtt));
                        if ($val == 1) {
                            $mergeVars[$key] = 'Male';
                        } elseif ($val == 2) {
                            $mergeVars[$key] = 'Female';
                        }
                        break;
                    case 'dob':
                        $dob = (string)$customer->getData(strtolower($customAtt));
                        if ($dob) {
                            $mergeVars[$key] = (substr($dob, 5, 2) . '/' . substr($dob, 8, 2));
                        }
                        break;
                    case 'billing_address':
                    case 'shipping_address':
                        $mergeVars = array_merge($mergeVars, $this->_setAddress($customAtt, $mergeVars, $customer, $key));
                        break;
                    case 'date_of_purchase':

                        $lastOrder = Mage::getModel('monkey/lastorder')
                            ->getCollection()
                            ->addFieldToFilter('email', array('eq' => $customer->getEmail()))
                            ->getFirstItem();
                        if ($lastOrder->getId()) {
                            $mergeVars[$key] = $lastOrder->getDate();
                        }

                        break;
                    case 'ee_customer_balance':

                        $mergeVars[$key] = '';

                        if ($this->isEnterprise() && $customer->getId()) {

                            $_customer = Mage::getModel('customer/customer')->load($customer->getId());
                            if ($_customer->getId()) {
                                if (Mage::app()->getStore()->isAdmin()) {
                                    $websiteId = is_null($websiteId) ? Mage::app()->getStore()->getWebsiteId() : $websiteId;
                                }

                                $balance = Mage::getModel('enterprise_customerbalance/balance')
                                    ->setWebsiteId($websiteId)
                                    ->setCustomerId($_customer->getId())
                                    ->loadByCustomer();

                                $mergeVars[$key] = $balance->getAmount();
                            }

                        }

                        break;
                    case 'group_id':
                        $groupId = (int)$customer->getData(strtolower($customAtt));
                        $customerGroup = Mage::helper('customer')->getGroups()->toOptionHash();
                        if ($groupId == 0) {
                            $mergeVars[$key] = 'NOT LOGGED IN';
                        } else {
                            $mergeVars[$key] = $customerGroup[$groupId];
                        }
                        break;
                    case 'store_code':
                        $storeId = (string)$customer->getData('store_id');
                        $storeCode = Mage::getModel('core/store')->load($storeId)->getCode();
                        if ($storeCode) {
                            $mergeVars[$key] = $storeCode;
                        }
                        break;
                    default:
                        if (($value = (string)$customer->getData(strtolower($customAtt)))
                            OR ($value = (string)$request->getPost(strtolower($customAtt)))
                        ) {
                            $mergeVars[$key] = $value;
                        }

                        break;
                }

            }
        }
        return $mergeVars;
    }
    protected function _setAddress($customAtt,$mergeVars, $object, $key)
    {
        if ($object instanceof Mage_Sales_Model_Order) {
            $addr = explode('_', $customAtt);
            $address = $object->{'get' . ucfirst($addr[0]) . 'Address'}();
        } else {

            $addr = explode('_', $customAtt);
            $address = $object->{'getPrimary' . ucfirst($addr[0]) . 'Address'}();
            if (!$address) {
                if ($object->{'getDefault' . ucfirst($addr[0])}()) {
                    $address = Mage::getModel('customer/address')->load($object->{'getDefault' . ucfirst($addr[0])}());
                }
            }
        }
        if ($address) {
            $mergeVars[$key] = array(
                'addr1' => $address->getStreet(1),
                'addr2' => $address->getStreet(2),
                'city' => $address->getCity(),
                'state' => (!$address->getRegion() ? $address->getCity() : $address->getRegion()),
                'zip' => $address->getPostcode(),
                'country' => $address->getCountryId()
            );
            $telephone = $address->getTelephone();
            if ($telephone) {
                $mergeVars['TELEPHONE'] = $telephone;
            }
            $company = $address->getCompany();
            if ($company) {
                $mergeVars['COMPANY'] = $company;
            }
            $country = $address->getCountryId();
            if ($country) {
                $countryName = Mage::getModel('directory/country')->load($country)->getName();
                $mergeVars['COUNTRY'] = $countryName;
            }
            $zipCode = $address->getPostcode();
            if ($zipCode) {
                $mergeVars['ZIPCODE'] = $zipCode;
            }
        }
        return $mergeVars;
    }
    /**
     * Get Mergevars
     *
     * @param null|Mage_Customer_Model_Customer $object
     * @param bool $includeEmail
     * @return array
     */
    public function mergeVars($object = NULL, $includeEmail = FALSE, $currentList = NULL)
    {
        //Initialize as GUEST customer
        $customer = new Varien_Object;

        $regCustomer = Mage::registry('current_customer');
        $guestCustomer = Mage::registry('mc_guest_customer');

        if (Mage::helper('customer')->isLoggedIn()) {
            $customer = Mage::helper('customer')->getCustomer();
        } elseif ($regCustomer) {
            $customer = $regCustomer;
        } elseif ($guestCustomer) {
            $customer = $guestCustomer;
        } else {
            if (is_null($object)) {
                $customer->setEmail($object->getSubscriberEmail())
                    ->setStoreId($object->getStoreId());
            } else {
                $customer = $object;
            }
        }

        if (is_object($object)) {
            if ($object->getListGroups()) {
                $customer->setListGroups($object->getListGroups());
            }

            if ($object->getMcListId()) {
                $customer->setMcListId($object->getMcListId());
            }
        }

        $mergeVars = Mage::helper('monkey')->getMergeVars($customer, $includeEmail);
        // add groups
        $monkeyPost = Mage::getSingleton('core/session')->getMonkeyPost();
        $request = Mage::app()->getRequest();
        $post = $request->getPost();
        if ($monkeyPost) {
            $post = unserialize($monkeyPost);
        }
        //if post exists && is not admin backend subscription && not footer subscription
        $mergeVars = array_merge($mergeVars, $this->_checkGrouping($post, $currentList, $object));

        return $mergeVars;
    }
    protected function _checkGrouping($post,$currentList, $object)
    {
        $storeId = Mage::app()->getStore()->getId() ? Mage::app()->getStore()->getId() : $object->getStoreId();
        $mergeVars = array();
        $request = Mage::app()->getRequest();
        $adminSubscription = $request->getActionName() == 'save' && $request->getControllerName() == 'customer' && $request->getModuleName() == (string)Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName');
        $footerSubscription = $request->getActionName() == 'new' && $request->getControllerName() == 'subscriber' && $request->getModuleName() == 'newsletter';
        $customerSubscription = $request->getActionName() == 'saveadditional';
        $customerCreateAccountSubscription = $request->getActionName() == 'createpost';
        if ($post && !$adminSubscription || Mage::getSingleton('core/session')->getIsOneStepCheckout()) {
            $defaultList = Mage::helper('monkey')->config('list');
            //if can change customer set the groups set by customer else set the groups on MailChimp config
            $canChangeGroups = Mage::getStoreConfig('monkey/general/changecustomergroup', $storeId);
            if (!$customerCreateAccountSubscription && $currentList && ($currentList != $defaultList || $canChangeGroups && !$footerSubscription) && isset($post['list'][$currentList])) {
                $subscribeGroups = array(0 => array());
                foreach ($post['list'][$currentList] as $toGroups => $value) {
                    if (is_numeric($toGroups)) {
                        $subscribeGroups[0]['id'] = $toGroups;
                        $subscribeGroups[0]['groups'] = implode(', ', array_unique($post['list'][$currentList][$subscribeGroups[0]['id']]));
                    }
                }
                $groups = NULL;
            } elseif ($currentList == $defaultList) {
                $groups = Mage::getStoreConfig('monkey/general/cutomergroup', $storeId);
                $groups = explode(",", $groups);
                if (isset($groups[0]) && $groups[0]) {
                    $subscribeGroups = array();
                    $_prevGroup = null;
                    $checkboxes = array();
                    $currentGroup = '';
                    foreach ($groups as $group) {
                        $item = explode("_", $group);
                        if ($item[0]) {
                            $currentGroup = $item[0];
                            if ($currentGroup == $_prevGroup || $_prevGroup == null) {
                                $checkboxes[] = $item[1];
                                $_prevGroup = $currentGroup;
                            } elseif ($checkboxes && isset($item[1])) {
                                    $subscribeGroups[] = array('id' => $_prevGroup, "groups" => str_replace('%C%', '\\,', implode(', ', $checkboxes)));
                                    $checkboxes = array();
                                    $_prevGroup = $currentGroup;
                                    $checkboxes[] = $item[1];
                            } else {
                                $checkboxes = array();
                                $_prevGroup = null;
                            }
                        }
                    }
                    if ($currentGroup && $checkboxes) {
                        $subscribeGroups[] = array('id' => $currentGroup, "groups" => str_replace('%C%', '\\,', implode(', ', $checkboxes)));
                    }

                }

                $force = Mage::getStoreConfig('monkey/general/checkout_subscribe', $storeId);
                $map = Mage::getStoreConfig('monkey/general/markfield', $storeId);
                if (isset($post['magemonkey_subscribe']) && $map != "") {
                    $listsChecked = explode(',', $post['magemonkey_subscribe']);
                    $hasClicked = in_array($currentList, $listsChecked);
                    if ($hasClicked && $force != 3) {
                        $mergeVars[$map] = "Yes";
                    } else {
                        $mergeVars[$map] = "No";
                    }
                } elseif (Mage::getSingleton('core/session')->getIsOneStepCheckout()) {
                    $post2 = $request->getPost();
                    if (isset($post['subscribe_newsletter']) || isset($post2['subscribe_newsletter'])) {
                        $mergeVars[$map] = "Yes";
                    } elseif (Mage::helper('monkey')->config('checkout_subscribe') > 2) {
                        $mergeVars[$map] = "No";
                    }
                } elseif ($request->getModuleName() == 'checkout') {
                    $mergeVars[$map] = "No";
                }
            } else {
                $map = Mage::getStoreConfig('monkey/general/markfield', $storeId);
                $mergeVars[$map] = "Yes";
            }
            if (isset($subscribeGroups[0]['id']) && $subscribeGroups[0]['id'] != -1) {
                $mergeVars["GROUPINGS"] = $subscribeGroups;
            }
        }
        return $mergeVars;
    }
    /**
     * Register on Magento's registry GUEST customer data for MergeVars for on checkout subscribe
     *
     * @param Mage_Sales_Model_Order $order
     * @return void
     */
    public function registerGuestCustomer($order)
    {

        if (Mage::registry('mc_guest_customer')) {
            return;
        }

        $customer = new Varien_Object;

        $customer->setId('guest' . time());
        $customer->setEmail($order->getBillingAddress()->getEmail());
        $customer->setStoreId($order->getStoreId());
        $customer->setFirstname($order->getBillingAddress()->getFirstname());
        $customer->setLastname($order->getBillingAddress()->getLastname());
        $customer->setPrimaryBillingAddress($order->getBillingAddress());
        $customer->setPrimaryShippingAddress($order->getShippingAddress());

        Mage::register('mc_guest_customer', $customer, TRUE);

    }


    /**
     * Create a Magento's customer account for given data
     *
     * @param array $accountData
     * @param integer $websiteId ID of website to associate customer to
     * @return Mage_Customer_Model_Customer
     */
    public function createCustomerAccount($accountData, $websiteId)
    {
        $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId);

        if (!isset($accountData['firstname']) OR empty($accountData['firstname'])) {
            $accountData['firstname'] = $this->__('Store');
        }
        if (!isset($accountData['lastname']) OR empty($accountData['lastname'])) {
            $accountData['lastname'] = $this->__('Guest');
        }

        $customerForm = Mage::getModel('customer/form');
        $customerForm->setFormCode('customer_account_create')
            ->setEntity($customer)
            ->initDefaultValues();
        // emulate request
        $request = $customerForm->prepareRequest($accountData);
        $customerData = $customerForm->extractData($request);
        $customerForm->restoreData($customerData);

        $customerErrors = $customerForm->validateData($customerData);

        if ($customerErrors) {
            $customerForm->compactData($customerData);

            $pwd = $customer->generatePassword(8);
            $customer->setPassword($pwd);
            try {
                $customer->save();

                if ($customer->isConfirmationRequired()) {
                    $customer->sendNewAccountEmail('confirmation');
                }
                /**
                 * Handle Address related Data
                 */
                $billing = $shipping = null;
                if (isset($accountData['billing_address']) && !empty($accountData['billing_address'])) {
                    $this->_McAddressToMage($accountData, 'billing', $customer);
                }
                if (isset($accountData['shipping_address']) && !empty($accountData['shipping_address'])) {
                    $this->_McAddressToMage($accountData, 'shipping', $customer);
                }
            } catch (Exception $ex) {
                $this->log($ex->getMessage(), 'Monkey.log');
            }
        }

        return $customer;
    }

    /**
     * Parse MailChimp <address> MergeField type to Magento's address object
     *
     * @param array $data MC address data
     * @param string $type billing or shipping
     * @param Mage_Customer_Model_Customer $customer
     * @return array Empty if noy errors, or a list of errors in an Array
     */
    protected function _McAddressToMage(array $data, $type, $customer)
    {
        $addressData = $data["{$type}_address"];
        $address = explode(str_repeat(' ', 2), $addressData);
        list($addr1, $addr2, $city, $state, $zip, $country) = $address;

        $region = Mage::getModel('directory/region')->loadByName($state, $country);

        $mgAddress = array(
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'street' => array($addr1, $addr2),
            'city' => $city,
            'country_id' => $country,
            'region' => $state,
            'region_id' => (!is_null($region->getId()) ? $region->getId() : null),
            'postcode' => $zip,
            'telephone' => 'not_provided',
        );

        /* @var $address Mage_Customer_Model_Address */
        $address = Mage::getModel('customer/address');
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_register_address')
            ->setEntity($address);

        $addrrequest = $addressForm->prepareRequest($mgAddress);
        $addressData = $addressForm->extractData($addrrequest);
        $addressErrors = $addressForm->validateData($addressData);

        $errors = array();
        if ($addressErrors === true) {
            $address->setId(null)
                ->setData("is_default_{$type}", TRUE);
            $addressForm->compactData($addressData);
            $customer->addAddress($address);

            $addressErrors = $address->validate();
            if (is_array($addressErrors)) {
                $errors = array_merge($errors, $addressErrors);
            }
        } else {
            $errors = array_merge($errors, $addressErrors);
        }

        return $errors;
    }

    /**
     * handles subscription to any list on post
     *
     * @param $object
     * @param $db
     * @param $orderId
     */
    public function listsSubscription($object, $db, $orderId = null)
    {
        $monkeyPost = Mage::getSingleton('core/session')->getMonkeyPost();
        $post = unserialize($monkeyPost);
        if (isset($post['magemonkey_force'])) {
            foreach ($post['list'] as $list) {
                $listId = $list['subscribed'];
                $this->subscribeToList($object, $db, $listId, false, $orderId);
            }
        } elseif (isset($post['magemonkey_subscribe']) && $post['magemonkey_subscribe']) {
            $lists = explode(',', $post['magemonkey_subscribe']);
            foreach ($lists as $listId) {
                $this->subscribeToList($object, $db, $listId, false, $orderId);
            }
            //Subscription for One Step Checkout with force subscription
        } elseif (Mage::getSingleton('core/session')->getIsOneStepCheckout() && Mage::helper('monkey')->config('checkout_subscribe') > 2 && !Mage::getSingleton('core/session')->getIsUpdateCustomer()) {
            $this->subscribeToList($object, $db, null, false, $orderId);
        } elseif (!Mage::getSingleton('core/session')->getMonkeyCheckout()) {
            $this->subscribeToList($object, $db, NULL, TRUE, $orderId);
        }

    }

    /**
     * Subscribe to list by listId
     *
     * @param $object
     * @param $db
     * @param null $listId
     * @param $forceSubscribe
     * @param $orderId
     */
    public function subscribeToList($object, $db, $listId = NULL, $forceSubscribe = FALSE, $orderId = null)
    {
        $email = $object->getEmail();
        $storeId = $object->getStoreId();
        if ($object instanceof Mage_Customer_Model_Customer) {
            $subscriber = Mage::getModel('newsletter/subscriber')
                ->setSubscriberEmail($email);
        } else {
            $subscriber = $object;
        }

        $defaultList = Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_LIST, $storeId);
        if (!$listId) {
            $listId = $defaultList;
        }
        $alreadySubscribed = Mage::getSingleton('newsletter/subscriber')->loadByEmail($email);
        $mergeVars = Mage::helper('monkey')->mergeVars($object, FALSE, $listId);
        $isConfirmNeed = FALSE;
        if (!Mage::helper('monkey')->isAdmin() &&
            (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_CONFIRMATION_FLAG, $object->getStoreId()) == 1 && !Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CONFIRMATION_EMAIL, $object->getStoreId()) || $forceSubscribe && Mage::getSingleton('core/session')->getMonkeyCheckout())
        ) {
            $isConfirmNeed = TRUE;
        }
        if ($listId == $defaultList && !Mage::getSingleton('core/session')->getIsHandleSubscriber() && !$forceSubscribe && (!$alreadySubscribed || !$alreadySubscribed->getId())) {
            $subscriber->setStoreId($storeId)->subscribe($email);
            $this->_subscribe($listId, $email, $mergeVars, $isConfirmNeed, $db, $orderId);
        } else {
            $alreadyOnList = Mage::getSingleton('monkey/asyncsubscribers')->getCollection()
                ->addFieldToFilter('lists', $listId)
                ->addFieldToFilter('email', $email)
                ->addFieldToFilter('processed', 0);
            //if not in magemonkey_async_subscribers with processed 0 add list
            if (count($alreadyOnList) == 0) {

                $isOnMailChimp = Mage::helper('monkey')->subscribedToList($email, $listId);
                //if( TRUE === $subscriber->getIsStatusChanged() ){
                if ($isOnMailChimp == 1) {
                    if (Mage::getSingleton('core/session')->getIsOneStepCheckout() || Mage::getSingleton('core/session')->getMonkeyCheckout()) {
                        $this->_subscribe($listId, $email, $mergeVars, 0, 1, $orderId);
                    }
                    return;
                }

                if ($isConfirmNeed) {
                    $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
                }

                $this->_subscribe($listId, $email, $mergeVars, $isConfirmNeed, $db, $orderId);
                $subscriberExists = Mage::getModel('newsletter/subscriber')->loadbyEmail($email);
                if (Mage::getSingleton('core/session')->getMonkeyCheckout() && !$subscriberExists->getId()) {
                    $subscriber->subscribe($email);
                }
            }
        }

    }

    /**
     * Subscribe to list only on MailChimp side
     *
     * @param $listId
     * @param $email
     * @param $mergeVars
     * @param $isConfirmNeed
     * @param $db
     * @param $orderId
     */
    public function _subscribe($listId, $email, $mergeVars, $isConfirmNeed, $db, $orderId = null)
    {
        if ($db) {
            if ($isConfirmNeed) {
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('monkey')->__('Confirmation request will be sent soon.'));
            }
            $subs = Mage::getModel('monkey/asyncsubscribers');
            $subs->setMapfields(serialize($mergeVars))
                ->setEmail($email)
                ->setLists($listId)
                ->setConfirm($isConfirmNeed)
                ->setProcessed(0)
                ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
                ->setOrderId($orderId)
                ->save();
        } else {
            if ($isConfirmNeed) {
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('monkey')->__('Confirmation request has been sent.'));
            }
            Mage::getSingleton('monkey/api')->listSubscribe($listId, $email, $mergeVars, 'html', $isConfirmNeed, TRUE);
        }
    }

    /**
     * Handle subscription on customer account
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param string $guestEmail
     * @return void
     */
    public function handlePost($request, $guestEmail)
    {
        //<state> param is an html serialized field containing the default form state
        //before submission, we need to parse it as a request in order to save it to $odata and process it
//        parse_str($request->getPost('state'), $odata);
        $m = explode('&', $request->getPost('state'));
        $odata = array();
        $list = array();
        foreach ($m as $v) {

            $g = explode('=', $v);
            $u = explode('%5B', $v);
            if ($u[0] == 'list') {
                $suffixListId = $u[1];
                $listId = substr($u[1], 0, (strlen($suffixListId)-3));
                $list[$listId] = array();
                $listIdArray = $list[$listId];
                $tail = explode('%5D', $u[2]);
                $subscribed = $tail[0];
                $listIdArray[$subscribed] = $g[1];
                $list[$listId] = $listIdArray;
                $odata['list'] = $list;
            } else {
                $odata[$g[0]] = $g[1];
            }
        }
        $lists = $request->getPost('list', array());


        $curlists = (TRUE === array_key_exists('list', $odata)) ? $odata['list'] : array();
        $defaultList = $this->getDefaultList(Mage::app()->getStore());

        $api = Mage::getSingleton('monkey/api');
        $loggedIn = Mage::helper('customer')->isLoggedIn();
        if ($loggedIn) {
            $customer = Mage::helper('customer')->getCustomer();
        } else {
            $customer = Mage::registry('mc_guest_customer');
        }
        $email = $guestEmail ? $guestEmail : $customer->getEmail();
        if (!empty($curlists)) {
            //Handle Unsubscribe and groups update actions
            foreach ($curlists as $listId => $list) {

                if (FALSE === array_key_exists($listId, $lists)) {
                    //Unsubscribe Email

                    $item = Mage::getModel('monkey/monkey')->loadByEmail($email);
                    if (!$item->getId()) {
                        $item = Mage::getModel('newsletter/subscriber')
                            ->loadByEmail($email);
                    }
                    if ($item->getSubscriberEmail()) {
                        $item->unsubscribe();
                    }

                    //Unsubscribe Email
                    $alreadyOnDb = Mage::getSingleton('monkey/asyncsubscribers')->getCollection()
                        ->addFieldToFilter('lists', $listId)
                        ->addFieldToFilter('email', $email)
                        ->addFieldToFilter('processed', 0);

                    if (count($alreadyOnDb) > 0) {
                        foreach ($alreadyOnDb as $listToDelete) {
                            $toDelete = Mage::getModel('monkey/asyncsubscribers')->load($listToDelete->getId());
                            $toDelete->delete();
                        }
                        Mage::getSingleton('core/session')
                            ->addSuccess($this->__('You have been removed from Newsletter.'));
                    } else {
                        $api->listUnsubscribe($listId, $email);
                        Mage::getSingleton('core/session')
                            ->addSuccess($this->__('You have been removed from Newsletter.'));
                    }

                } else {

                    $groupings = $lists[$listId];
                    unset($groupings['subscribed']);
                    $customerLists = $api->listMemberInfo($listId, $email);
                    $customerLists = isset($customerLists['data'][0]['merges']['GROUPINGS']) ? $customerLists['data'][0]['merges']['GROUPINGS'] : array();

                    foreach ($customerLists as $clkey => $cl) {
                        if (!isset($groupings[$cl['id']])) {
                            $groupings[$cl['id']][] = '';
                        }
                    }

                    $customer->setMcListId($listId);
                    $customer->setListGroups($groupings);
                    $mergeVars = Mage::helper('monkey')->mergeVars($customer, FALSE, $listId);

                    //Handle groups update
                    $api->listUpdateMember($listId, $email, $mergeVars, 'html', false);
                    Mage::getSingleton('core/session')
                        ->addSuccess($this->__('Your profile has been updated!'));

                }

            }

        }

        //Subscribe to new lists
        if (is_array($lists) && is_array($curlists)) {
            $subscribe = array_diff_key($lists, $curlists);
            if (!empty($subscribe)) {
                foreach ($subscribe as $listId => $slist) {
                    if (!isset($slist['subscribed'])) {
                        continue;
                    }

                    $groupings = $lists[$listId];
                    unset($groupings['subscribed']);
                    if ($defaultList == $listId) {
                        $subscriber = Mage::getModel('newsletter/subscriber');
                        $subscriber->setListGroups($groupings);
                        $subscriber->setMcListId($listId);
                        $subscriber->setMcStoreId(Mage::app()->getStore()->getId());
                        $subscriber->subscribe($email);
                        Mage::getSingleton('core/session')->addSuccess($this->__('Thank you for your subscription.'));
                    } else {
                        $customer->setListGroups($groupings);
                        $customer->setMcListId($listId);
                        $subscriber = Mage::getModel('newsletter/subscriber')
                            ->setSubscriberEmail($email);
                        $this->subscribeToList($subscriber, 0, $listId);

                    }
                }
            }
        }
    }

    public function getThisStore()
    {
        $store = Mage::app()->getStore();

        $configscope = Mage::app()->getRequest()->getParam('store');
        if ($configscope && ($configscope !== 'undefined')) {
            $store = $configscope;
        }
        return $store;
    }

    public function getCanShowCampaignJs()
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        if (Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::ECOMMERCE360_ACTIVE, $storeId) && Mage::helper('monkey')->canMonkey()) {
            return 'ebizmarts/magemonkey/campaignCatcher.js';
        }
    }
}
