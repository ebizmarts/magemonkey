<?php

/**
 * Transactional Email Service manager controller
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Mandrill
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_Mandrill_Adminhtml_Mandrill_UsersController extends Mage_Adminhtml_Controller_Action {

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction() {
		$this->_title($this->__('Mandrill'));

        $this->loadLayout();
        $this->_setActiveMenu('newsletter/magemonkey');
        return $this;
    }

	/**
	 * Mandrill verified emails grid
	 */
	public function sendersAction() {
		$this->_initAction();
		$this->_title($this->__('Verified Senders'));
        $this->renderLayout();
	}

}