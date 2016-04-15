<?php

/**
 * Transactional Email Service manager controller
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Mandrill
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Mandrill_Adminhtml_Mandrill_UsersController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->_title($this->__('Mandrill'));

        $this->loadLayout();
        $this->_setActiveMenu('system');
        return $this;
    }

    /**
     * Mandrill verified emails grid
     */
    public function sendersAction()
    {
        $this->_initAction();
        $this->_title($this->__('Verified Senders'));
        $this->renderLayout();
    }

    protected function _isAllowed() {
        switch ($this->getRequest()->getActionName()) {
            case 'senders':
                $acl = 'system/email_template/mandrill/users_senders';
                break;
        }
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }

}