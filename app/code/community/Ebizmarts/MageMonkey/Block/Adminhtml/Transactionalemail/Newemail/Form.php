<?php

/**
 * Add new email to Transactional Email service FORM
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_Transactionalemail_Newemail_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/validateEmail'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('newemail_data', array(
            'legend' => Mage::helper('monkey')->__('New email')
        ));

        $fieldset->addField('email_address', 'text', array(
            'label' => Mage::helper('monkey')->__('Email address'),
            'title' => Mage::helper('monkey')->__('Email address'),
            'name' => 'email_address',
            'class' => 'validate-email',
            'required' => true,
        ));

        $fieldset->addField('service', 'hidden', array(
            'name' => 'service',
            'value' => $this->getRequest()->getParam('service'),
        ));
        $fieldset->addField('store', 'hidden', array(
            'name' => 'store',
            'value' => $this->getRequest()->getParam('store', 0),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();

    }

}
