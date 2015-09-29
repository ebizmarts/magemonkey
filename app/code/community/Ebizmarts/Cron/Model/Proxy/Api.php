<?php
/**
 * Author: info@ebizmarts.com
 * Date: 9/16/15
 * Time: 4:16 PM
 * File: Api.php
 * Module: magemonkey
 */

class Ebizmarts_Cron_Model_Proxy_Api
{
    public function getPlans()
    {
        $command = 'plan';
        $rc = json_decode($this->run($command));
        return $rc;
    }
    public function getPlan($id)
    {
        $command = "plan/$id";
        $rc = json_decode($this->run($command));
        return $rc;
    }
    public function pay($data)
    {
        $command = "pay";
        $rc = $this->run($command,array_merge($data,$this->getParams()));
        return json_decode($rc);
    }
    public function changeCard($data)
    {
        $command = "changeCard";
        $rc = json_decode($this->run($command,$data));
        return $rc;
    }
    public function getCustomer($id)
    {
        $command = 'getCustomer/'.$id;
        $rc = json_decode($this->run($command));
        return $rc;
    }
    public function changePlan($data)
    {
        $command = 'changePlan';
        $rc = json_decode($this->run($command,$data));
        return $rc;
    }
    public function cancelPlan($id)
    {
        $command = 'cancelPlan/'.$id;
        $rc = json_decode($this->run($command));
        return $rc;
    }
    public function restoreMerchant($id,$data)
    {
        $command = 'restoreMerchant';
        $data = array_merge(array('customer'=>$id),$data,$this->getParams());
        $rc = $this->run($command,$data);

        return json_decode($rc);
    }
    protected function run($command,$data = null)
    {
        Mage::log(__METHOD__);
        $json = json_encode($data);
        Mage::log($json);
        $endPoint = Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::END_POINT).$command;
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $endPoint);
        curl_setopt($curlSession, CURLOPT_HEADER, 1);
        curl_setopt($curlSession, CURLOPT_POST, 0);
        if($data) {
            curl_setopt($curlSession, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json))
            );
            curl_setopt($curlSession, CURLOPT_POST, 1);
            curl_setopt($curlSession, CURLOPT_POSTFIELDS, $json);
        }
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlSession, CURLOPT_VERBOSE, 1);
        curl_setopt($curlSession, CURLOPT_HEADER, 1);
        $response = curl_exec($curlSession);
        $header_size = curl_getinfo($curlSession, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($curlSession);
        Mage::log($body);
        return $body;
    }
    protected function getParams()
    {
        $json = array();
        $json['run'] = array();
        $json['run'][] = array('code'=>'abandoned');
        $json['run'][] = array('code'=>'cleanAbandonedCartExpiredCoupons');
        $json['run'][] = array('code'=>'sendPopupCoupon');
        $json['run'][] = array('code'=>'processNewOrders');
        $json['run'][] = array('code'=>'processRelated');
        $json['run'][] = array('code'=>'processReview');
        $json['run'][] = array('code'=>'processBirthday');
        $json['run'][] = array('code'=>'processNoActivity');
        $json['run'][] = array('code'=>'processWishlist');
        $json['run'][] = array('code'=>'processVisited');
        $json['run'][] = array('code'=>'processBackToStock');
        return array('params'=>$json);
    }
}