<?php

class Ebizmarts_AbandonedCart_Model_System_Config_Maxemails
{
    public function toOptionArray()
    {
        $options = array();
        for ($i = 0; $i < Ebizmarts_AbandonedCart_Model_Config::MAXTIMES_NUM; $i++) {
            $options[] = array('value' => $i, 'label' => $i + 1);
        }
        return $options;
    }
}