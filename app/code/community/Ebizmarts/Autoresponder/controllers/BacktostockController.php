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

        if (isset($params['subscriber_email']) && isset($params['product_id'])) {

            $email = $params['subscriber_email'];
            $productId = $params['product_id'];
            $alertId = false;

            $stockAlertCollection = Mage::getModel('ebizmarts_autoresponder/backtostockalert')->getCollection();
            $stockAlertCollection
                ->addFieldToFilter('main_table.product_id', array('eq' => $productId))
                ->addFieldToFilter('main_table.is_active', array('eq' => 1));

            // Check if we already have this Product ID in alert table, otherwise insert a new one
            if ($stockAlertCollection->getSize() == 0) {
                $stockAlert = Mage::getModel('ebizmarts_autoresponder/backtostockalert');
                $stockAlert
                    ->setProductId($productId)
                    ->setIsActive(1);

                $stockAlert->save();

                $alertId = $stockAlert->getAlertId();

                Mage::helper('ebizmarts_autoresponder')->log('New Stock Alert for product #' . $productId . ' was saved.');

            } else {
                // Retrieve existing Stock Alert ID
                $alertId = $stockAlertCollection->getFirstItem()->getAlertId();
            }

            // Create new notification for this subscriber
            $backStock = Mage::getModel('ebizmarts_autoresponder/backtostock');
            $backStock
                ->setAlertId($alertId)
                ->setEmail($email);
            $backStock->save();


            //@TODO add configuration option to ask if Admin wants to redirect to Product's View
            // Redirects to Product's View
            /*
            if($productId) {
                $currentProduct = Mage::getModel('catalog/product')->load($productId);

                if($currentProduct && $currentProduct->getProductUrl()) {
                    $redirect = $currentProduct->getProductUrl();
                }

            }
            */

            Mage::getSingleton('core/session')
                ->addSuccess($this->__('You have been subscribed successfully!'));

        } else {
            // Something went wrong and some of the needed params didn't arrived
            Mage::helper('ebizmarts_autoresponder')->log('ERROR - Cannot extract email or ProductID from params. Ensure that Back to stock form is well rendered and has this two fields "subscriber_email", "product_id".');

            Mage::getSingleton('core/session')
                ->addError($this->__('We cannot subscribe you at this moment. Please, check again later.'));
        }

        // Redirect User
        $this->_redirectUrl($redirect);
    }

}