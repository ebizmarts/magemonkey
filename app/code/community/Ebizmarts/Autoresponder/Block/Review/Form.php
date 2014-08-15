<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_Autoresponder_Block_Review_Form extends Mage_Review_Block_Form
{
    public function getAction()
    {
        $productId = Mage::app()->getRequest()->getParam('id', false);
        $token = Mage::app()->getRequest()->getParam('token', false);
        return Mage::getUrl('review/product/post', array('id' => $productId, 'token' => $token));
    }

}