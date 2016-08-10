<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Autoresponder_Model_System_Config_Newordertrigger
{
    protected $_options;

    public function toOptionArray()
    {
        $this->_options = array(
            array('value' => 0, 'label' => 'Days after order'),
            array('value' => 1, 'label' => 'Order status'),
            array('value' => 2, 'label' => 'Days after order status changed to')
        );
        return $this->_options;
    }
}
