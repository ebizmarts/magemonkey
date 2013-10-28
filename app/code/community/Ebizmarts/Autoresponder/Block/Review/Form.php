<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 10/24/13
 * Time   : 7:34 PM
 * File   : Form.php
 * Module : Ebizmarts_Magemonkey
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