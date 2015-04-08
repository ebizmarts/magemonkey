<?php

/**
 * MailChimp lists source file
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Model_System_Config_Source_Attributes
{

    /**
     * Lists for API key will be stored here
     *
     * @access protected
     * @var array Email lists for given API key
     */
    protected $_attributes = null;

    /**
     * Load lists and store on class property
     *
     * @return void
     */
    public function __construct()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->getItems();

        $this->_attributes = $attributes;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = array();

        if (is_array($this->_attributes)) {

            foreach ($this->_attributes as $attribute) {
                $attributes [] = array('value' => $attribute->getAttributecode(), 'label' => $attribute->getAttributecode());
            }

        } else {
            $attributes [] = array('value' => '', 'label' => Mage::helper('monkey')->__('--- No data ---'));
        }

        return $attributes;
    }

}
