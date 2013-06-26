<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/25/13
 * Time   : 3:22 PM
 * File   : AutoresponderController.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_AutoresponderController extends Mage_Core_Controller_Front_Action
{
    public function unsubscribeAction(){
        $params = $this->getRequest()->getParams();
        if(isset($params['email'])&&isset($params['list'])) {
            $collection = Mage::getModel('ebizmarts_autoresponder/unsubscribe')->getCollection();
            $collection->addFieldtoFilter('main_table.email',array('eq'=>$params['email']));
            if($collection->getSize() == 0) {
                $unsubscribe = Mage::getModel('ebizmarts_autoresponder/unsubscribe');
                $unsubscribe->setEmail($params['email'])
                            ->setList($params['list']);
                $unsubscribe->save();
            }
        }
        $this->loadLayout();
        $this->renderLayout();
    }
}