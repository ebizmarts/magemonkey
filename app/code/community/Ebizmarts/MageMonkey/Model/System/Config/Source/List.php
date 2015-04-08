<?php

/**
 * MailChimp lists source file
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Model_System_Config_Source_List
{

    /**
     * Lists for API key will be stored here
     *
     * @access protected
     * @var array Email lists for given API key
     */
    protected $_lists = null;

    /**
     * Load lists and store on class property
     *
     * @return void
     */
    public function __construct()
    {
        $max = Mage::helper('monkey')->config('maxlistsamount');
        if (!is_numeric($max)) {
            $max = null;
        }
        if (is_null($this->_lists)) {
            $this->_lists = Mage::getSingleton('monkey/api')
                ->lists(null, null, $max);
        }
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $lists = array();

        if (is_array($this->_lists)) {

            foreach ($this->_lists['data'] as $list) {
                $lists [] = array('value' => $list['id'], 'label' => $list['name'] . ' (' . $list['stats']['member_count'] . ' ' . Mage::helper('monkey')->__('members') . ')');
            }

        } else {
            $lists [] = array('value' => '', 'label' => Mage::helper('monkey')->__('--- No data ---'));
        }

        return $lists;
    }

}
