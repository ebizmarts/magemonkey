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
        return $rc->plan;
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
        $rc = json_decode($this->run($command,$data));
        return $rc;
    }
    public function changeCard($data)
    {
        $command = "changecard";
        $rc = json_decode($this->run($command,$data));
        return $rc;
    }
    public function getCustomer($id)
    {
        $command = 'getcustomer/'.$id;
        $rc = json_decode($this->run($command));
        return $rc;
    }
    public function changePlan($data)
    {
        $command = 'changeplan';
        $rc = json_decode($this->run($command,$data));
        return $rc;
    }
    public function cancelPlan($id)
    {
        $command = 'cancelplan/'.$id;
        $rc = json_decode($this->run($command));
        return $rc;
    }
    public function restoreMerchant($id,$url)
    {
        $command = 'restoremerchant';
        $data = array('customer'=>$id,'url'=>$url);
        $rc = json_decode($this->run($command,$data));
        return $rc;
    }
    protected function run($command,$data = null)
    {
        $endPoint = Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::END_POINT).$command;
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $endPoint);
        curl_setopt($curlSession, CURLOPT_HEADER, 1);
        //curl_setopt($curlSession, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($curlSession, CURLOPT_POST, 0);
        if($data) {
            curl_setopt($curlSession, CURLOPT_POSTFIELDS, json_encode($data));
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
        return $body;
    }
}