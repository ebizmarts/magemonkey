<?php
/**
 * Author: info@ebizmarts.com
 * Date: 9/24/15
 * Time: 1:32 PM
 * File: CronController.php
 * Module: magemonkey
 */

class Ebizmarts_Cron_CronController extends Mage_Core_Controller_Front_Action
{
    /**
     * @throws Zend_Controller_Response_Exception
     */
    public function indexAction()
    {
        $body = $this->getRequest()->getRawBody();
        $params = json_decode($body);
        $responseCode = 0;
        $data = array();
        if(isset($params->token))
        {
            $token = Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::TOKEN);
            if($token!=$params->token)
            {
                $data = array('error'=>'token don\'t match');
                $responseCode = 401;
            }
            else
            {
                $responseCode = 200;
                if(isset($params->run))
                {
                    foreach($params->run as $process)
                    {
                        switch($process->code)
                        {
                            // abandonedcar crons
                            case 'abandoned':
                                $data[$process->code] = Mage::getModel('ebizmarts_abandonedcart/cron')->abandoned();
                                break;
                            case 'cleanAbandonedCartExpiredCoupons':
                                $data[$process->code] = Mage::getModel('ebizmarts_abandonedcart/cron')->cleanAbandonedCartExpiredCoupons();
                                break;
                            case 'sendPopupCoupon':
                                $data[$process->code] = Mage::getModel('ebizmarts_abandonedcart/cron')->sendPopupCoupon();
                                break;
                            // magemonkey crons
                            case 'bulksyncExportSubscribers':
                                $data[$process->code] = Mage::getModel('monkey/cron')->bulksyncExportSubscribers();
                                break;
                            case 'bulksyncImportSubscribers':
                                $data[$process->code] = Mage::getModel('monkey/cron')->bulksyncImportSubscribers();
                                break;
                            case 'autoExportSubscribers':
                                $data[$process->code] = Mage::getModel('monkey/cron')->autoExportSubscribers();
                                break;
                            case 'sendordersAsync':
                                $data[$process->code] = Mage::getModel('monkey/cron')->sendordersAsync();
                                break;
                            case 'cleanordersAsync':
                                $data[$process->code] = Mage::getModel('monkey/cron')->cleanordersAsync();
                                break;
                            case 'sendSubscribersAsync':
                                $data[$process->code] = Mage::getModel('monkey/cron')->sendSubscribersAsync();
                                break;
                            case 'cleanSubscribersAsync':
                                $data[$process->code] = Mage::getModel('monkey/cron')->cleanSubscribersAsync();
                                break;
                            // autoresponder crons
                            case 'processNewOrders':
                                $data[$process->code] = Mage::getModel('ebizmarts_autoresponder/cron')->processNewOrders();
                                break;
                            case 'processRelated':
                                $data[$process->code] = Mage::getModel('ebizmarts_autoresponder/cron')->processRelated();
                                break;
                            case 'processReview':
                                $data[$process->code] = Mage::getModel('ebizmarts_autoresponder/cron')->processReview();
                                break;
                            case 'processBirthday':
                                $data[$process->code] = Mage::getModel('ebizmarts_autoresponder/cron')->processBirthday();
                                break;
                            case 'processNoActivity':
                                $data[$process->code] = Mage::getModel('ebizmarts_autoresponder/cron')->processNoActivity();
                                break;
                            case 'processWishlist':
                                $data[$process->code] = Mage::getModel('ebizmarts_autoresponder/cron')->processWishlist();
                                break;
                            case 'processVisited':
                                $data[$process->code] = Mage::getModel('ebizmarts_autoresponder/cron')->processVisited();
                                break;
                            case 'processBackToStock':
                                $data[$process->code] = Mage::getModel('ebizmarts_autoresponder/cron')->processBackToStock();
                                break;
                            case 'cleanAutoresponderExpiredCoupons':
                                $data[$process->code] = Mage::getModel('ebizmarts_autoresponder/cron')->cleanAutoresponderExpiredCoupons();
                                break;
                        }
                    }
                }
                else
                {
                    $data = array('error'=>'no run parameter');
                    $responseCode = 400;
                }
            }
        }
        else {
            $data = array('error' => 'no token was given');
            $responseCode = 400;
        }
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json', true)
            ->setHttpResponseCode($responseCode)
            ->setBody(json_encode($data));
    }
}