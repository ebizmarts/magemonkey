<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_Autoresponder_BacktostockController extends Mage_Core_Controller_Front_Action
{

    public function subscribeAction()
    {
        $params = $this->getRequest()->getParams();
        $redirect = '/';

        if(isset($params['subscriber_email']) && isset($params['product_id'])) {

            $email = $params['subscriber_email'];
            $productId = $params['product_id'];

            $backtostock = Mage::getModel('ebizmarts_autoresponder/backtostock');
            $backtostock
                ->setProductId($productId)
                ->setEmail($email)
            ;
            $backtostock->save();

            Mage::getSingleton('core/session')
                ->addSuccess($this->__('You have been subscribed successfully!'));
        }

        // Decide where the User will be redirected
        $this->_redirectUrl($redirect);
    }

}