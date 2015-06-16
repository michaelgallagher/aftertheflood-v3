<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ep_dates
 *
 * ----------------------------------------------------------------------------------------------
 * @package	EE2 
 * @subpackage	ThirdParty
 * @author	Malcolm Elsworth 
 * @link	http://electricputty.co.uk 
 * @copyright	Copyright (c) 2011 Electric Putty Ltd.
 *
 */

class Ep_dates {



	function Ep_dates()
	{		
		$this->EE =& get_instance();
	}



	public function process_draft_data($ft, $post_data, $draft_action)
	{
		switch ($draft_action)
		{
			case 'create':
			case 'update':
				$post_data = $this->_make_sure_its_unix($post_data);
				break;				
		}
		return $post_data;
	}



	private function _make_sure_its_unix($date_str)
	{
		if(strpos($date_str, "-") === false)
		{
			return $date_str;
		}
		else
		{
			if(strlen($date_str) == 10) $date_str = $date_str.' 04:00:00 AM';
			return $this->EE->localize->string_to_timestamp($date_str);
		}
	}
}