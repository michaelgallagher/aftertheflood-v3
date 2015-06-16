<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ep_relationships
 *
 * ----------------------------------------------------------------------------------------------
 * @package	EE2 
 * @subpackage	ThirdParty
 * @author	Malcolm Elsworth 
 * @link	http://electricputty.co.uk 
 * @copyright	Copyright (c) 2011 Electric Putty Ltd.
 *
 */

class Ep_relationships {


	private $_table = 'relationships';
	
	var $entry_id;
	var $field_id;


	// Method invoked by relationships_query hook
	public function query_hook($field_name, $entry_ids, $depths, $sql)
	{
		// Reset the current Active Record call
		ee()->db->_reset_select();
		
		// Clean up the query
		$sql = str_replace('`', '', $sql);

		// Split it on the ORDER BY clause
		$sql_arr = preg_split('/ORDER BY/', $sql);
		
		// Set up a default BWF where clause
		$sql_where = 'L0.parent_is_bwf_draft = 0';
		
		// If we are previewing a BWF Draft
		if (isset(ee()->session->cache['ep_better_workflow']['is_draft']) && ee()->session->cache['ep_better_workflow']['is_draft'])
		{
			// And this is a single entry view and we're after children relationships only
			if (count($entry_ids) == 1 AND $field_name == '__root__')
			{
				// Debugging
				#echo var_dump($depths);
				#echo '<hr />';
				#echo var_dump($field_name);

				$sql_where = 'L0.parent_is_bwf_draft = 1';
			}
		}
		
		// Add the extra BWF where clause
		$sql = $sql_arr[0] . ' AND ' .$sql_where . ' ORDER BY ' .$sql_arr[1];
		
		return ee()->db->query($sql)->result_array();
	}


	public function process_draft_data($ft, $post_data, $draft_action)
	{
		$this->field_id = $ft->field_id;
		$this->entry_id = $ft->settings['entry_id'];

		switch ($draft_action)
		{
			case 'create':
				$this->_insert_new_rels($post_data);
				break;
			
			case 'update':
				$this->_delete_old_rels();
				$this->_insert_new_rels($post_data);
				break;
				
			case 'discard':
				$this->_delete_old_rels();
				break;
			
			case 'publish':
				// Do nothing, let the native code take care of this update
				break;
				
		}
		
		return $post_data;
	}


	// Delete all existing draft relationships
	private function _delete_old_rels()
	{
		ee()->db
			->where('parent_id', $this->entry_id)
			->where('field_id', $this->field_id)
			->where('parent_is_bwf_draft', 1)
			->delete($this->_table);
	}


	private function _insert_new_rels($post_data)
	{
		$sort = isset($post_data['sort']) ? $post_data['sort'] : array();
		$data = isset($post_data['data']) ? $post_data['data'] : array();

		$sort = array_filter($sort);
		$order = array_values($sort);

		//var_dump($post_data);
		//var_dump($order);
		//var_dump($data);
		//echo('Draft action: ' . $draft_action);
		//echo('Field ID:' . $field_id);
		//echo('Entry ID:' . $entry_id);
		//echo('Table:' . $_table);
		//die();

		// Build up rels array
		$ships = array();

		foreach ($data as $i => $child_id)
		{
			if ( ! $child_id)
			{
				continue;
			}
			$ships[] = array(
				'parent_id'				=> $this->entry_id,
				'child_id'				=> $child_id,
				'field_id'				=> $this->field_id,
				'order'					=> isset($order[$i]) ? $order[$i] : 0,
				'parent_is_bwf_draft'	=> 1
			);
		}

		// If child_id is empty, they are deleting a single relationship
		if (count($ships))
		{
			ee()->db->insert_batch($this->_table, $ships);
		}
	}
	
}