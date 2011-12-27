<?php

class Ebizmarts_MageMonkey_Block_Customer_Account_Lists extends Mage_Core_Block_Template
{

	protected $_lists  = array();
	protected $_info  = array();
	protected $_myLists = array();
	protected $_form;
	protected $_api;

	public function getApi()
	{
		if(is_null($this->_api)){
			$this->_api = Mage::getSingleton('monkey/api');
		}
		return $this->_api;
	}

	public function getLists()
	{
		$additionalLists = $this->helper('monkey')->config('additional_lists');

		if($additionalLists){

			$api     = $this->getApi();

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

	protected function _htmlGroupName($list, $group = NULL, $multiple = FALSE)
	{
		$htmlName = "list[{$list['id']}]";

		if(!is_null($group)){
			$htmlName .= "[{$group['id']}]";
		}

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

	protected function _memberInfo($listId)
	{
		if( FALSE === array_key_exists($listId, $this->_info) ){
			$this->_info[$listId] = $this->getApi()->listMemberInfo($listId, $this->_getEmail());
		}

		return $this->_info[$listId];
	}

	public function renderGroup($group, $list)
	{

		$fieldType = $group['form_field'];

		$memberInfo = $this->_memberInfo($list['id']);

		$myGroups = array();
		if($memberInfo['success'] == 1){
			$groupings = $memberInfo['data'][0]['merges']['GROUPINGS'];

			foreach($groupings as $_group){
				if(!empty($_group['groups'])){

					if($fieldType == 'checkboxes'){
						$myGroups[$_group['id']] = explode(', ', $_group['groups']);
					}elseif($fieldType == 'radio'){
						$myGroups[$_group['id']] = array($_group['groups']);
					}else{
						$myGroups[$_group['id']] = $_group['groups'];
					}

				}
			}
		}

		switch ($fieldType) {
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

		//Check/select values
		if(isset($myGroups[$group['id']])){
			$object->setValue($myGroups[$group['id']]);
		}

		if($fieldType == 'checkboxes' || $fieldType == 'dropdown'){

			$options = array();

			if($fieldType == 'dropdown'){
				$options[''] = '';
			}

			foreach($group['groups'] as $g){
				$options [$g['name']] = $g['name'];
			}
			$object->addElementValues($options);
			$object->setName( $this->_htmlGroupName($list, $group, ($fieldType == 'checkboxes' ? TRUE : FALSE)) );
			$object->setHtmlId('interest-group');

			$html = $object->getElementHtml();

		}elseif($fieldType == 'radio'){

			$options = array();
			foreach($group['groups'] as $g){
				$options [] = new Varien_Object(array('value' => $g['name'], 'label' => $g['name']));
			}

			$object->setName($this->_htmlGroupName($list, $group));
			$object->setHtmlId('interest-group');
			$object->addElementValues($options);

			$html = $object->getElementHtml();
		}

		if($fieldType != 'checkboxes'){
			$html = "<div class=\"groups-list\">{$html}</div>";
		}

		return $html;

	}

	protected function _getEmail()
	{
		return $this->helper('customer')->getCustomer()->getEmail();
	}

	public function listLabel($list)
	{
		$myLists = $this->getSubscribedLists();

		$checkbox = new Varien_Data_Form_Element_Checkbox;
		$checkbox->setForm($this->getForm());
		$checkbox->setHtmlId('list-' . $list['id']);
		$checkbox->setChecked((bool)(is_array($myLists) && in_array($list['id'], $myLists)));
		$checkbox->setTitle( ($checkbox->getChecked() ? $this->__('Click to unsubscribe from this list.') : $this->__('Click to subscribe to this list.')) );
		$checkbox->setLabel($list['name']);

		$hname = $this->_htmlGroupName($list);
		$checkbox->setName($hname . '[subscribed]');

		$checkbox->setValue($list['id']);


		return $checkbox->getLabelHtml() . $checkbox->getElementHtml();
	}
}