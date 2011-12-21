<?php

class Ebizmarts_MageMonkey_Block_Customer_Account_Lists extends Mage_Core_Block_Template
{

	protected $_lists  = array();
	protected $_myLists = array();
	protected $_form;

	public function getLists()
	{
		$additionalLists = $this->helper('monkey')->config('additional_lists');

		if($additionalLists){

			$api     = Mage::getSingleton('monkey/api');

			$this->_myLists = $api->listsForEmail($this->_getEmail());

			$lists   = $api->lists(array('list_id' => $additionalLists));

			if($lists['total'] > 0){
				foreach($lists['data'] as $list){
					$this->_lists []= array(
											'id'   => $list['id'],
											'name' => $list['name'],
											'interest_groupings' => $api->listInterestGroupings($list['id']),
										   );

				}
			}

		}

		return $this->_lists;
	}

	public function getSubscribedLists()
	{
		return $this->_myLists;
	}

	protected function _htmlGroupName($id, $multiple = FALSE)
	{
		$htmlName = "group[{$id}]";

		if(TRUE === $multiple){
			$htmlName .= '[]';
		}

		return $htmlName;
	}

    /**
     * Form getter/instantiation
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        if ($this->_form instanceof Varien_Data_Form) {
            return $this->_form;
        }
        $form = new Varien_Data_Form();
        return $form;
    }

	public function renderGroup($group)
	{
		switch ($group['form_field']) {
			case 'radio':
				$class = 'Varien_Data_Form_Element_Radios';
				break;
			case 'checkboxes':
				$class = 'Varien_Data_Form_Element_Checkboxes';
				break;
			case 'dropdown':
				$class = 'Varien_Data_Form_Element_Select';
				break;
		}

		$object = new $class;
		$object->setForm($this->getForm());

		if($group['form_field'] == 'checkboxes' || $group['form_field'] == 'dropdown'){

			$options = array();
			foreach($group['groups'] as $g){
				$options [$g['bit']] = $g['name'];
			}
			$object->addElementValues($options);
			$object->setName( $this->_htmlGroupName($group['id'], ($group['form_field'] == 'checkboxes' ? TRUE : FALSE)) );

		}elseif($group['form_field'] == 'radio'){

			$options = array();
			foreach($group['groups'] as $g){
				$options [] = new Varien_Object(array('value' => $g['bit'], 'label' => $g['name']));
			}
			$object->setValue(array());
			$object->setName($this->_htmlGroupName($group['id']));
			$object->setHtmlId('interest-group');
			$object->addElementValues($options);
		}

		return $object->getElementHtml();

	}

	protected function _getEmail()
	{
		return $this->helper('customer')->getCustomer()->getEmail();
	}
}