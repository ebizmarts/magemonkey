<?php

/**
 * Bulksync export grid container
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_Bulksync_QueueExport extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_bulksync_queue';
        $this->_blockGroup = 'monkey';
        $this->_headerText = Mage::helper('monkey')->__('Export Queue');

        parent::__construct();

        $this->_removeButton('add');
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid',
            $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_controller . 'Export_grid',
                $this->_controller . '.grid')->setSaveParametersInSession(true));
        return Mage_Adminhtml_Block_Widget_Container::_prepareLayout();
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            return false;
        }
        return true;
    }

}