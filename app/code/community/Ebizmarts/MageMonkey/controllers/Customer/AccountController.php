<?php

/**
 * MailChimp Customer Account controller
 */
class Ebizmarts_MageMonkey_Customer_AccountController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		//TODO: Check that customer is logged in

		//Multiple lists, Interest groups

		$this->getLayout();

		$this->renderLayout();
	}

}