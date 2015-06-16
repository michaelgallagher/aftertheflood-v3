<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ep_matrix
 *
 * ----------------------------------------------------------------------------------------------
 * @package	EE2 
 * @subpackage	ThirdParty
 * @author	Malcolm Elsworth 
 * @link	http://electricputty.co.uk 
 * @copyright	Copyright (c) 2011 Electric Putty Ltd.
 *
 */

class Ep_matrix {



	private $settings = NULL;



	function Ep_matrix($settings=array())
	{		
		$this->EE =& get_instance();
	}



	function update_row_ids($original_data, $entry_id, $field_id)
	{
		$updated_data = array();
		$looper = 0;
		
		$sql = "SELECT row_id FROM exp_matrix_data WHERE entry_id = '".$entry_id."' AND field_id = '".$field_id."' AND is_draft = 1 ORDER BY row_order ASC";
		$rows = $this->EE->db->query($sql);
		if ($rows->num_rows() > 0)
		{
			foreach ($rows->result_array() as $row)
			{
				$row_name = 'row_id_'.$row['row_id'];
				
				$updated_data['row_order'][$looper] = $row_name;
				$updated_data[$row_name] = $original_data[$original_data['row_order'][$looper]];
				
				$looper++;
			}
		}
		
		return $updated_data;
	}

}