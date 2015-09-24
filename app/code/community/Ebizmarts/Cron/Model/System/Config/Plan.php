<?php
/**
 * Author: info@ebizmarts.com
 * Date: 9/16/15
 * Time: 1:46 PM
 * File: Plan.php
 * Module: magemonkey
 */
class Ebizmarts_Cron_Model_System_Config_Plan
{
    public function toOptionArray()
    {
        $options = array();
        $plans =Mage::getModel('ebizmarts_cron/proxy_api')->getPlans();

        foreach($plans as $plan) {
            $amount = $plan->amount/100;
            $symbol = Mage::app()->getLocale()->currency(strtoupper($plan->currency))->getSymbol();
            $options[] = array('value' => $plan->id, 'label' => $plan->name." ($symbol $amount)");
        }
        return $options;
    }
}