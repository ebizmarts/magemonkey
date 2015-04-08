<?php

/**
 * Signup standalone block to be used on different places
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Block_Lists extends Mage_Core_Block_Template
{

    protected $_lists = array();
    protected $_info = array();
    protected $_myLists = array();
    protected $_generalList = array();
    protected $_form;
    protected $_api;

    /**
     * Get API instance (singleton)
     *
     * @return Ebizmarts_MageMonkey_Model_Api
     */
    public function getApi()
    {
        if (is_null($this->_api)) {
            $this->_api = Mage::getSingleton('monkey/api');
        }
        return $this->_api;
    }

    public function getSaveUrl()
    {
        return $this->getUrl('monkey/signup/saveadditional');
    }

    /**
     * Check if GENERAL list can be shown on ALL LISTS template
     *
     * @return bool
     */
    public function getShowGeneral()
    {
        $action = $this->getRequest()->getActionName();
        $controller = $this->getRequest()->getControllerName();

        return (bool)!($action == 'savePayment' && $controller == 'onepage');
    }

    public function notInMyAccount()
    {
        $action = $this->getRequest()->getActionName();
        $controller = $this->getRequest()->getControllerName();
        $module = $this->getRequest()->getModuleName();

        return (bool)!($action == 'index' && $controller == 'customer_account' && $module == 'monkey');
    }

    /**
     * Show form items or not if customer is logged in and / or is on register page
     *
     * @return bool
     */
    public function getCanShowButton()
    {
        $ary = array(
            '/customer/account/create/',
            '/checkout/onepage/savePayment/',
            '/onestepcheckout/',
        );
        $requestString = $this->getRequest()->getRequestString();

        return !in_array($requestString, $ary);
    }

    /**
     * Get default list data from MC
     *
     * @return array
     */
    public function getGeneralList()
    {
        $list = $this->helper('monkey')->config('list');

        if ($list) {
            if (empty($this->_generalList)) {

                $api = $this->getApi();
                $listData = $api->lists(array('list_id' => $list));

                if (empty($this->_myLists)) {
                    $this->_myLists = $api->listsForEmail($this->_getEmail());
                }

                if ($listData['total'] > 0) {
                    $showRealName = $this->helper('monkey')->config('showreallistname');
                    if ($showRealName) {
                        $listName = $listData['data'][0]['name'];
                    } else {
                        $listName = $this->__('General Subscription');
                    }
                    $ig = $api->listInterestGroupings($listData['data'][0]['id']);
                    $this->_generalList = array(
                        'id' => $listData['data'][0]['id'],
                        'name' => $listName,
                        'interest_groupings' => $this->helper('monkey')->filterShowGroupings($ig),
                    );
                }
            }
        }

        return $this->_generalList;
    }

    /**
     * Get additional lists data from MC
     *
     * @return array
     */
    public function getLists()
    {
        $additionalLists = $this->helper('monkey')->config('additional_lists');

        if ($additionalLists) {

            if (empty($this->_lists)) {
                $api = $this->getApi();

                if (empty($this->_myLists)) {
                    $this->_myLists = $api->listsForEmail($this->_getEmail());
                }

                $lists = $api->lists(array('list_id' => $additionalLists));

                if ($lists['total'] > 0) {
                    foreach ($lists['data'] as $list) {
                        $this->_lists [] = array(
                            'id' => $list['id'],
                            'name' => $list['name'],
                            'interest_groupings' => $this->helper('monkey')->filterShowGroupings($api->listInterestGroupings($list['id'])),
                        );

                    }
                }
            }

        }

        return $this->_lists;
    }

    /**
     * Getter for class property
     *
     * @return array
     */
    public function getSubscribedLists()
    {
        if (!is_array($this->_myLists)) {
            return array();
        }
        return $this->_myLists;
    }

    /**
     * Utility to generate HTML name for element
     * @param string $list
     * @param string $group
     * @param bool $multiple
     * @return string
     */
    protected function _htmlGroupName($list, $group = NULL, $multiple = FALSE)
    {
        $htmlName = "list[{$list['id']}]";

        if (!is_null($group)) {
            $htmlName .= "[{$group['id']}]";
        }

        if (TRUE === $multiple) {
            $htmlName .= '[]';
        }

        return $htmlName;
    }

    /**
     * Form getter/instantiation
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        if ($this->_form instanceof Varien_Data_Form) {
            return $this->_form;
        }
        $form = new Varien_Data_Form();
        return $form;
    }

    /**
     * Get MC member information for an specific list
     *
     * @param string $listId ID of list in MC
     * @return array Member info on list
     */
    protected function _memberInfo($listId)
    {
        if (FALSE === array_key_exists($listId, $this->_info)) {
            $this->_info[$listId] = $this->getApi()->listMemberInfo($listId, $this->_getEmail());
        }

        return $this->_info[$listId];
    }

    /**
     * Render interest grouping with its groups
     *
     * @param array $group Group data from MC
     * @param array $list List data from MC
     * @return string HTML code
     */
    public function renderGroup($group, $list, $checked = -1)
    {
        $fieldType = $group['form_field'];

        $email = $this->_getEmail();
        if ($email) {
            $memberInfo = $this->_memberInfo($list['id']);
        } else {
            $memberInfo['success'] = 0;
        }

        $myGroups = array();
        //if checked = 1 all groups checked elseif checked == -1 registered groups checked elseif checked == 0 none checked
        if ($checked == 1) {
            foreach ($group['groups'] as $g) {
                $myGroups[$group['id']][] = $g['name'];
            }
        } elseif ($checked == -1) {
            if ($memberInfo['success'] == 1) {
                if (isset($memberInfo['data'][0]['merges']['GROUPINGS'])) {
                    $groupings = $memberInfo['data'][0]['merges']['GROUPINGS'];
                }
                $alreadyOnDb = Mage::getSingleton('monkey/asyncsubscribers')->getCollection()
                    ->addFieldToFilter('lists', $list['id'])
                    ->addFieldToFilter('email', $email)
                    ->addFieldToFilter('processed', 0);
                if (count($alreadyOnDb) > 0) {
                    foreach ($alreadyOnDb as $listToAdd) {
                        $mapFields = unserialize($listToAdd->getMapfields());
                        $groupings = $mapFields['GROUPINGS'];
                    }
                }
                if (isset($groupings)) {
                    foreach ($groupings as $_group) {
                        if (!empty($_group['groups'])) {
                            if ($fieldType == 'checkboxes') {

                                $currentGroup = str_replace('\\,', '%C%', $_group['groups']);
                                $currentGroupArray = explode(', ', $currentGroup);

                                $myGroups[$_group['id']] = str_replace('%C%', ',', $currentGroupArray);

                            } elseif ($fieldType == 'radio') {

                                if (strpos($_group['groups'], ',')) {
                                    $currentGroup = str_replace('\\,', '%C%', $_group['groups']);
                                    $currentGroupArray = explode(', ', $currentGroup);
                                    $collapsed = str_replace('%C%', ',', $currentGroupArray);

                                    if (is_array($collapsed) && isset($collapsed[0])) {
                                        $myGroups[$_group['id']] = array($collapsed[0]);
                                    } else {
                                        $myGroups[$_group['id']] = array($collapsed);
                                    }
                                } else {
                                    $myGroups[$_group['id']] = array($_group['groups']);
                                }

                            } else {
                                if (strpos($_group['groups'], ',')) {
                                    $currentGroup = str_replace('\\,', '%C%', $_group['groups']);
                                    $currentGroupArray = explode(', ', $currentGroup);
                                    $collapsed = str_replace('%C%', ',', $currentGroupArray);

                                    if (is_array($collapsed) && isset($collapsed[0])) {
                                        $myGroups[$_group['id']] = array($collapsed[0]);
                                    } else {
                                        $myGroups[$_group['id']] = array($collapsed);
                                    }
                                } else {
                                    $myGroups[$_group['id']] = array($_group['groups']);
                                }

                            }

                        }
                    }
                }
            }
        }

        switch ($fieldType) {
            case 'radio':
                $class = 'Varien_Data_Form_Element_Radios';
                break;
            case 'checkboxes':
                $class = 'Varien_Data_Form_Element_Checkboxes';
                break;
            case 'dropdown':
                $class = 'Varien_Data_Form_Element_Select';
                break;
            case 'hidden':
                $class = 'Varien_Data_Form_Element_Hidden';
                break;
            default:
                $class = 'Varien_Data_Form_Element_Text';
                break;
        }

        $object = new $class;
        $object->setForm($this->getForm());

        //Check/select values
        if (isset($myGroups[$group['id']]) && !$checked == 0 || $checked == 1) {
            $object->setValue($myGroups[$group['id']]);
        } else {
            $object->setValue(array());
        }

        if ($fieldType == 'checkboxes' || $fieldType == 'dropdown') {

            $options = array();

            if ($fieldType == 'dropdown') {
                $options[''] = '';
            }

            foreach ($group['groups'] as $g) {
                if ($this->helper('monkey')->config('list') == $list['id']) {
                    if ($this->_groupAllowed($g['name'])) {
                        $options [$g['name']] = $g['name'];
                    }
                } else {
                    $options [$g['name']] = $g['name'];
                }
            }

            if (method_exists('Varien_Data_Form_Element_Checkboxes', 'addElementValues')) {
                $object->addElementValues($options);
            } else {
                $object->setValues($options);
            }

            $object->setName($this->_htmlGroupName($list, $group, ($fieldType == 'checkboxes' ? TRUE : FALSE)));
            $object->setHtmlId('interest-group');

            $html = $object->getElementHtml();

        } elseif ($fieldType == 'radio' || $fieldType == 'hidden') {

            $options = array();
            foreach ($group['groups'] as $g) {
                if ($this->helper('monkey')->config('list') == $list['id']) {
                    if ($this->_groupAllowed($g['name'])) {
                        $options [] = new Varien_Object(array('value' => $g['name'], 'label' => $g['name']));
                    }
                } else {
                    $options [] = new Varien_Object(array('value' => $g['name'], 'label' => $g['name']));
                }
            }

            $object->setName($this->_htmlGroupName($list, $group));
            $object->setHtmlId('interest-group');

            if (method_exists('Varien_Data_Form_Element_Checkboxes', 'addElementValues')) {
                $object->addElementValues($options);
            } else {
                $object->setValues($options);
            }

            $html = $object->getElementHtml();
        }

        if ($fieldType != 'checkboxes') {
            $html = "<div class=\"groups-list\">{$html}</div>";
        }

        return $html;

    }

    /**
     * Return element id for group to be added to the post on checkout
     *
     * @param $group
     * @param bool $default
     * @return null|string
     */
    public function getGroupId($group, $default = FALSE)
    {
        $ret = "interest-group_" . $group['name'];
        if ($default) {
            if (!$this->_groupAllowed($group['name'])) {
                $ret = null;
            }
        }
        return $ret;
    }

    /**
     * Return if group is selected in MailChimp configuration.
     *
     * @param $groupName
     * @return bool
     */
    protected function _groupAllowed($groupName)
    {
        $allowedGroups = $this->helper('monkey')->config('cutomergroup');
        $allowedGroups = explode(',', $allowedGroups);
        $ret = false;
        if (isset($allowedGroups)) {
            foreach ($allowedGroups as $group) {
                $group = explode('_', $group);
                if (isset($group[1]) && $group[1] == $groupName) {
                    $ret = true;
                }
            }
        }
        return $ret;
    }

    /**
     * Retrieve email from Customer object in session
     *
     * @return string Email address
     */
    protected function _getEmail()
    {
        return $this->helper('customer')->getCustomer()->getEmail();
    }

    /**
     * Return HTML code for list <label> with checkbox, checked if subscribed, otherwise not
     *
     * @param array $list List data from MC
     * @return string HTML code
     */
    public function listLabel($list)
    {
        $myLists = $this->getSubscribedLists();

        //if is on database it gets checked
        $alreadyOnList = Mage::getSingleton('monkey/asyncsubscribers')->getCollection()
            ->addFieldToFilter('lists', $list['id'])
            ->addFieldToFilter('email', $this->_getEmail())
            ->addFieldToFilter('processed', 0);
        if (count($alreadyOnList) > 0) {
            $myLists[] = $list['id'];
        }

        $checkbox = new Varien_Data_Form_Element_Checkbox;
        $checkbox->setForm($this->getForm());
        $checkbox->setHtmlId('list-' . $list['id']);
        $checkbox->setChecked((bool)(is_array($myLists) && in_array($list['id'], $myLists)));
        $checkbox->setTitle(($checkbox->getChecked() ? $this->__('Click to unsubscribe from this list.') : $this->__('Click to subscribe to this list.')));
        $checkbox->setLabel($list['name']);

        $hname = $this->_htmlGroupName($list);
        $checkbox->setName($hname . '[subscribed]');

        $checkbox->setValue($list['id']);
        $checkbox->setClass('monkey-list-subscriber');


        return $checkbox->getLabelHtml() . $checkbox->getElementHtml();
    }

    public function getCanModify()
    {
        return Mage::getStoreConfig('monkey/general/changecustomergroup');
    }

    public function getForce()
    {
        $force = Mage::getStoreConfig('monkey/general/checkout_subscribe');
        if ($force == 3 || $force == 4) {
            return true;
        }
        return false;
    }
}
