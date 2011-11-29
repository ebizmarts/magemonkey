<?php

class Ebizmarts_MageMonkey_Model_Custom_Collection
	extends Varien_Data_Collection
{

	protected $_toload = NULL;

	public function __construct(array $data)
	{
		$data = current($data);

		if( empty($data) ){
			return parent::__construct();
		}

		$this->_toload = $data;

		return parent::__construct();
	}
	public function load($printQuery = false, $logQuery = false)
	{
		if($this->isLoaded() || is_null($this->_toload)){
			return $this;
		}

        foreach ($this->_toload as $row) {
            $item = new Varien_Object;
            $item->addData($row);
            $this->addItem($item);
        }

        $this->_setIsLoaded();

		return $this;
	}
}