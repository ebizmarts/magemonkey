<?php

/**
 * Transactional email Mandrill grid
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Mandrill
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Mandrill_Block_Adminhtml_Users_Senders_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('mandrill_valid_emails');
        $this->setUseAjax(false);
        $this->setSaveParametersInSession(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection()
    {
//		$helper  = Mage::helper('ebizmarts_mandrill');
//        $mail    = $helper->api()->setApiKey($helper->getApiKey());
//		$emails  = $mail->usersSenders();
        $storeId = Mage::app()->getStore()->getId();
        $api = new Mandrill_Message(Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::APIKEY, $storeId));
        $emails = $api->users->senders();
        Mage::log($emails);
        if ($emails !== FALSE) {
//			$_emails = array();
//			foreach($emails as $email){
//
//                $email = new Varien_Object((array)$email);
//				$_emails []= array(
//									'email'        => $email->getAddress(),
//                                    'sent'         => $email->getSent(),
//                                    'rejects'      => $email->getRejects(),
//                                    'complaints'   => $email->getComplaints(),
//                                    'unsubs'       => $email->getUnsubs(),
//                                    'opens'        => $email->getUniqueOpens(),
//                                    'clicks'       => $email->getUniqueClicks(),
//                                    'hard_bounces' => $email->getHardBounces(),
//                                    'soft_bounces' => $email->getSoftBounces(),
//									'created_at'   => $email->getCreatedAt(),
//								  );
//			}
            $collection = Mage::getModel('ebizmarts_mandrill/customcollection', array($emails));
        } else {
            $collection = Mage::getModel('ebizmarts_mandrill/customcollection', array(array()));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('email', array(
            'header' => Mage::helper('ebizmarts_mandrill')->__('Email Address'),
            'index' => 'address',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('sent', array(
            'header' => Mage::helper('ebizmarts_mandrill')->__('# of messages sent'),
            'index' => 'sent',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('rejects', array(
            'header' => Mage::helper('ebizmarts_mandrill')->__('# of rejected messages'),
            'index' => 'rejects',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('complaints', array(
            'header' => Mage::helper('ebizmarts_mandrill')->__('# of spam complaints'),
            'index' => 'complaints',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('unsubs', array(
            'header' => Mage::helper('ebizmarts_mandrill')->__('# of unsubscribe requests'),
            'index' => 'unsubs',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('opens', array(
            'header' => Mage::helper('ebizmarts_mandrill')->__('# of unique opens'),
            'index' => 'opens',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('clicks', array(
            'header' => Mage::helper('ebizmarts_mandrill')->__('# of times unique tracked URLs have been clicked'),
            'index' => 'clicks',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('hard_bounces', array(
            'header' => Mage::helper('ebizmarts_mandrill')->__('# of hard bounces'),
            'index' => 'hard_bounces',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('soft_bounces', array(
            'header' => Mage::helper('ebizmarts_mandrill')->__('# of soft bounces'),
            'index' => 'soft_bounces',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('created_at', array(
            'header' => Mage::helper('ebizmarts_mandrill')->__('Created At'),
            'index' => 'created_at',
            'filter' => false,
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}