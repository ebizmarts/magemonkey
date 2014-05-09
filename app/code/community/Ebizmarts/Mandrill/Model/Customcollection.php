<?php

/**
 * Custom collection class for nondb data
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Mandrill
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_Mandrill_Model_Customcollection extends Varien_Data_Collection {

	/**
	 * Contains generic data to load on load() method
	 *
	 * @var mixed
	 */
	protected $_toload = NULL;

	/**
	 * Initialize data to be loaded afterwards
	 *
	 * @param array $data
	 * @return Varien_Data_Collection
	 */
	public function __construct(array $data) {
		$data = current($data);

		if( empty($data) ){
			return parent::__construct();
		}

		$this->_toload = $data;

		return parent::__construct();
	}

	/**
	 * Load data into object
	 *
	 * @param bool $printQuery
	 * @param bool $logQuery
	 * @return Ebizmarts_MageMonkey_Model_Custom_Collection
	 */
	public function load($printQuery = false, $logQuery = false) {
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