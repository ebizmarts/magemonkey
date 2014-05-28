<?php

/**
 * Grid column renderer for lists
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Block_Adminhtml_Renderer_Lists extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
    	$value = $this->_getValue($row);

		$lists = Mage::getSingleton('monkey/api')
							->lists(array('list_id' => implode(', ', $row->lists())));

		$listsNames = array();

		if(is_array($lists)){
			foreach($lists['data'] as $list){
				$listsNames []= $list['name'];
			}
		}

        return implode(', ', $listsNames);
    }
}
