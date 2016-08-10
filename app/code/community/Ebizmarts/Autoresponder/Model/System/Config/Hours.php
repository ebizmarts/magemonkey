<?php

class Ebizmarts_Autoresponder_Model_System_Config_Hours
{
    public function toOptionArray()
    {
        $options = array();
        for ($i = 0; $i < 24; $i++) {
            $options[] = array('value' => $i, 'label' => $i);
        }
        return $options;
    }
}