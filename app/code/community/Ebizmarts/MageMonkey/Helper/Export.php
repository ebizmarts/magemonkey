<?php

class Ebizmarts_MageMonkey_Helper_Export extends Mage_Core_Helper_Abstract
{

	public function parseMembers($response, $listMergeVars, $store)
	{

		$storeId = Mage::app()->getStore($store)->getId();

		//Explode response, one record per line
		$response = explode("\n", $response);

		//My Merge Vars
		$mergeMaps = Mage::helper('monkey')->getMergeMaps($storeId);

		//Get Header (MergeVars)
		$header = json_decode(array_shift($response));

		//Add var to maps, not included on config
		array_unshift($mergeMaps, array('magento' => 'email', 'mailchimp' => 'EMAIL'));

		$canMerge = array();
		foreach($header as $mergePos => $mergeLabel){
			foreach($listMergeVars as $var){
				if( strcmp($mergeLabel, $var['name']) === 0 ){

					foreach($mergeMaps as $map){
						if(strcmp($var['tag'], $map['mailchimp']) === 0){
							$canMerge [$mergePos]= $map['magento'];
						}
					}

				}
			}
		}

		$membersData = array();

        foreach($response as $member){
		    if (trim($member) != ''){
		      $membersData []= array_combine($canMerge, array_intersect_key(json_decode($member), $canMerge));
		    }
        }

		return $membersData;
	}

}