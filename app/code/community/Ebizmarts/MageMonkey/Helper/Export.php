<?php

class Ebizmarts_MageMonkey_Helper_Export extends Mage_Core_Helper_Abstract
{

	public function parseMembers($response, $mergeVars = NULL)
	{
		if($mergeVars){
			var_dump($mergeVars);
		}

		$response = explode("\n", $response);
		$i = 0;
		$header = array();
        foreach($response as $buffer){

		    if (trim($buffer) != ''){
		      $obj = json_decode($buffer);
		      if ($i == 0){
		        //Header row
		        $header = $obj;
		        var_dump( $header );
		      } else {
		        //echo, write to a file, queue a job, etc.
		        var_dump( $header, $obj );
		      }
		      $i++;
		    }

        }
	}

}