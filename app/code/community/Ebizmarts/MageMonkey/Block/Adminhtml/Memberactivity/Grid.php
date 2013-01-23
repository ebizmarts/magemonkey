<?php

/**
 * Member activity data grid
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_Memberactivity_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('token_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
    	$customer = Mage::registry('current_customer');
    	$email    = $customer->getEmail();

    	$api      = Mage::getSingleton('monkey/api', array('apikey' => Mage::helper('monkey')->getApiKey($customer->getStore())));
    	$activity = array();
    	$lists    = $api->listsForEmail($email);

		$activityData = array();
		if(is_array($lists)){
			foreach($lists as $list){
				$activity []= $api->listMemberActivity($list, $email);
			}
			if(!empty($activity)){
				foreach($activity as $act){

					if(empty($act['data'][0])){
						continue;
					}
					$activityData []= $act['data'];
				}
			}
		}
		if(empty($activityData)){
			$activityData[] = array('action' => '', 'timestamp' => '', 'url' => '', 'bounce_type' => '', 'campaign_id' => '');
		}
		if (!is_array(current($activityData))) {
			$activityData = array();
		} else {
			$activityData = current($activityData);
		}

		$collection = Mage::getModel('monkey/custom_collection', $activityData);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('action', array(
            'header'=> Mage::helper('monkey')->__('Action'),
            'index' => 'action',
            'sortable' => false
        ));
        $this->addColumn('url', array(
            'header'=> Mage::helper('monkey')->__('Url'),
            'index' => 'url',
            'sortable' => false
        ));
        $this->addColumn('bounce_type', array(
            'header'=> Mage::helper('monkey')->__('Bounce Type'),
            'index' => 'bounce_type',
            'sortable' => false
        ));
        $this->addColumn('campaign_id', array(
            'header'=> Mage::helper('monkey')->__('Campaign ID'),
            'index' => 'campaign_id',
            'sortable' => false
        ));
        $this->addColumn('timestamp', array(
            'header'=> Mage::helper('monkey')->__('Timestamp'),
            'index' => 'timestamp',
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

}