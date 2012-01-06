<?php

/**
 * Bulksync import form, lets you schedule jobs to be processed by Magento's cron
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_Bulksync_Import_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('import_settings', array(
            'legend'    => Mage::helper('monkey')->__('Import Configuration')
        ));

		$lists = Mage::getSingleton('monkey/system_config_source_list')->toOptionArray();
        $fieldset->addField('list', 'multiselect', array(
            'label'     => Mage::helper('monkey')->__('Choose Lists'),
            'title'     => Mage::helper('monkey')->__('Choose Lists'),
            'name'      => 'list',
            'values'   => $lists,
            'class' => 'required-entry',
            'required' => true,
        ));

		$statuses = Mage::getSingleton('monkey/system_config_source_status')->toOptionArray();
        $fieldset->addField('import_types', 'multiselect', array(
            'label'     => Mage::helper('monkey')->__('Statuses to import'),
            'title'     => Mage::helper('monkey')->__('Statuses to import'),
            'name'      => 'import_types',
            'values'   => $statuses,
            'class' => 'required-entry',
            'required' => true,
        ));

        $fieldset->addField('create_customers', 'checkbox', array(
            'label'     => Mage::helper('monkey')->__('Create customer accounts'),
            'title'     => Mage::helper('monkey')->__('Create customer accounts'),
            'name'      => 'create_customers',
            'value'     => '1'
        ));

        $fieldset->addField('since', 'date', array(
            'label'     => Mage::helper('monkey')->__('Retrieve data since'),
            'title'     => Mage::helper('monkey')->__('Retrieve data since'),
            'name'      => 'since',
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Varien_Date::DATE_INTERNAL_FORMAT
        ));

        $fieldset->addField('direction', 'hidden', array(
            'name'     => 'direction',
            'value'    => 'import',
        ));

		//Set default FORM values
		$formValues = array(
							'import_types' => array('subscribed')
						   );
		$form->addValues($formValues);

        $form->setUseContainer(true);
        $this->setForm($form);

        $this->setChild('form_after', Mage::getModel('core/layout')->createBlock('monkey/adminhtml_bulksync_queueImport', 'importqueue'));

		return parent::_prepareForm();

    }

}