<?php

/**
 * Ecommerce360 API sent orders
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_Ecommerceapi_Renderer_Items extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $lines = $row->getLines();

        $skus = array();
        foreach($lines as $item){
            $skus []= $item['product_sku'] . ' [' . $item['qty'] . ']';
        }

        return implode(', ', $skus);
    }

}
