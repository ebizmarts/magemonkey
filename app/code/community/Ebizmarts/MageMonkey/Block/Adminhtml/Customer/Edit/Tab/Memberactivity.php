<?php

/**
 * Customer activiy information block retrieved from MailChimp to show under Customer
 * Edit section
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Block_Adminhtml_Customer_Edit_Tab_Memberactivity
    extends Ebizmarts_MageMonkey_Block_Adminhtml_Memberactivity_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Columns, that should be removed from grid
     *
     * @var array
     */
    protected $_columnsToRemove = array('visitor_session_id', 'protocol', 'customer_id');

    public function __construct()
    {
        parent::__construct();
        $this->setFilterVisibility(FALSE);
        $this->setSaveParametersInSession(FALSE);
    }

    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'orders';
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('MailChimp List Member Activity');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('MailChimp List Member Activity');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');
        return (bool)$customer->getId();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
    	//TODO: Hide if MageMonkey is disabled
        return false;
    }
}