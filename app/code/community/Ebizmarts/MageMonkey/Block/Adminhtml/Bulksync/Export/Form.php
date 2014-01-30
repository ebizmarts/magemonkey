<?php

/**
 * Bulksync export form, lets you schedule jobs to be processed by Magento's cron
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Block_Adminhtml_Bulksync_Export_Form extends Mage_Adminhtml_Block_Widget_Form
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

        $fieldset   = $form->addFieldset('export_settings', array(
            'legend'    => Mage::helper('monkey')->__('Export Configuration')
        ));

		$dataSource = Mage::getSingleton('monkey/system_config_source_bulksyncDatasource')->toOptionArray();
        $fieldset->addField('data_source_entity', 'select', array(
            'label'     => Mage::helper('monkey')->__('Data Source'),
            'title'     => Mage::helper('monkey')->__('Data Source'),
            'name'      => 'data_source_entity',
            'values'   => $dataSource,
            'class' => 'required-entry',
            'required' => true,
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

        $storeSwitcher = $fieldset->addField('store_id', 'select', array(
            'name'      => 'store_id',
            'label'     => Mage::helper('monkey')->__('Store'),
            'title'     => Mage::helper('monkey')->__('Store'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));

        $fieldset->addField('direction', 'hidden', array(
            'name'     => 'direction',
            'value'    => 'export',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        $this->setChild('form_after', Mage::getModel('core/layout')->createBlock('monkey/adminhtml_bulksync_queueExport', 'exportqueue'));

        return parent::_prepareForm();
    }

}
