<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ep_field_controller
 * 
 * ----------------------------------------------------------------------------------------------
 * @package	EE2 
 * @subpackage	ThirdParty
 * @author	Andrea Fiore / Malcolm Elsworth 
 * @link	http://electricputty.co.uk 
 * @copyright	Copyright (c) 2011 Electric Putty Ltd.
 * 	
 */

class Ep_field_controller 
{

	private $settings = NULL;
	private $fields_to_process = array();


	/**
	* Instantiate the BetterWorkflow Logger class in the client
	*/
	function Ep_field_controller($settings = array())
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
		
		// -------------------------------------------
		// Load and instantiate the logger
		// -------------------------------------------
		require_once reduce_double_slashes(PATH_THIRD . '/ep_better_workflow/libraries/ep_bwf_logger.php');
		$this->action_logger = new Ep_workflow_logger($this->settings['advanced']['log_events']);

		// -------------------------------------------
		// Load and instantiate the fieldtypes
		// -------------------------------------------
		require_once reduce_double_slashes(PATH_THIRD . '/ep_better_workflow/fieldtypes/ep_relationships.php');
		require_once reduce_double_slashes(PATH_THIRD . '/ep_better_workflow/fieldtypes/ep_dates.php');
		require_once reduce_double_slashes(PATH_THIRD . '/ep_better_workflow/fieldtypes/ep_files.php');
		require_once reduce_double_slashes(PATH_THIRD . '/ep_better_workflow/fieldtypes/ep_zenbu.php');
		require_once reduce_double_slashes(PATH_THIRD . '/ep_better_workflow/fieldtypes/ep_matrix.php');
		
		$this->ep_relationships = new Ep_relationships();
		$this->ep_dates		= new Ep_dates();
		$this->ep_files		= new Ep_files($this->settings);
		$this->ep_zenbu		= new Ep_zenbu($this->settings);
		$this->ep_matrix 	= new Ep_matrix();
		
			
		// Define all the different field types we need to run processes on
		$this->fields_to_process[0] = array(array('relationship'), $this->ep_relationships);
		$this->fields_to_process[1] = array(array('date','eevent_helper'), $this->ep_dates);
		

		// Load the third party model
		$this->EE->load->model('ep_third_party');
	}



	function process_draft_data($data, $draft_action)
	{
		// Loggin
		$this->action_logger->add_to_log("ep_field_controller: process_draft_data(): Draft_action: ".$draft_action);

		// We only want to loop through the revision_post part of the data array
		$revision_post_data = $data['revision_post'];
		
		// loop through all the items in the data array
		foreach($revision_post_data as $key => $value)
		{
			// We're only interested in custom fields
			if(preg_match('/^field_id/',$key))
			{
				// Convert field name to id
				$field_id = substr($key, 9);
				
				// Get the field data
				$field_data = $this->EE->ep_third_party->get_field_type($field_id);
				
				// Get the field type
				$field_type = $field_data->row('field_type');
				
				// Try and instantiate the fieldtype class
				$ft = $this->_init_fieldtype_obj($field_type);
				
				// Were we successful?
				if(is_object($ft))
				{
					$this->action_logger->add_to_log("ep_field_controller: process_draft_data(): Field type: " . $field_type);
				
					// Get the field settings
					$fieldtype_settings = $this->_get_fieldtype_settings($field_data, $data);
					
					// Add the channel and entry IDs to the settings data
					$fieldtype_settings['entry_id'] = $data['entry_id'];
					$fieldtype_settings['channel_id'] = $data['channel_id'];

					// Pass some values to the field type's settings array
					$ft->settings = $fieldtype_settings;
				
					// Make sure our fieldType Object knows its field ID
					if(property_exists($ft, 'field_id')) $ft->field_id = $field_id;
					
					// Set a flag so we know if we are processing this one ourselves or handing of off
					$process_internally = false;
					
					
					// Loop through the field types we need to run processes on
					foreach($this->fields_to_process as $the_field)
					{
						// Is this a field type we need to process? (e.g. the new 2.6+ Relationships field)
						if(in_array($field_type, $the_field[0]))
						{
							// If yes, update the flag so we know we are taking care of this on
							$process_internally = true;
							
							$this->action_logger->add_to_log("ep_field_controller: process_draft_data(): Call internal field method for field type " . $field_type);
							$this->action_logger->add_to_log("ep_field_controller: process_draft_data(): Pre processed value for field type " . $field_type);
							
							// Next call the process_draft_data() method on the BWF field-type object
							$revision_post_data[$key] = $the_field[1]->process_draft_data($ft, $value, $draft_action);
							
							$this->action_logger->add_to_log("ep_field_controller: process_draft_data(): Response from internal field method for field type " . $field_type);
						}
					}
					
					
					if (!$process_internally) {
										
						// If not, switch on draft action and see if the field type has the desired method
						switch ($draft_action)
						{
							// Are we creating or updating a draft
							case 'create':
							case 'update':
							if (method_exists($ft, 'draft_save'))
							{
								
								// Before we do anything, check that we have meaningful data
								$value = $this->_check_field_data($field_type, $value, $data['entry_id'], $field_id);
								
								// If the fieldtype has a BWF specific draft_save() method, use this
								$ft->draft_save($value, $draft_action);
								
								// Check to see if we need to update our data array to keep in sync with DB changes
								$revision_post_data[$key] = $this->_update_field_data($field_type, $value, $data['entry_id'], $field_id);
							}
							else
							{
								// If not, check to see if the fieldtype has the ep_better_workflow_use_save_method property
								if(property_exists($ft, 'ep_better_workflow_use_save_method'))
								{
									$revision_post_data[$key] = $ft->save($value);
									$this->action_logger->add_to_log("Native Save() method called on: " . $field_type);
								}
							}	
							break;

							// Are we turning a 'draft' into an 'entry'
							case 'publish':
							if (method_exists($ft, 'draft_publish'))
							{
								$ft->draft_publish();
							}
							break;
						
							case 'discard':
							if (method_exists($ft, 'draft_discard'))
							{
								$ft->draft_discard();	
							}
							break;
						}
					}
				}
			}
		}
		
		// Rebuild the data array
		$data['revision_post'] = $revision_post_data;

		// Send back the 'updated' data array
		return $data;
	}



	private function _init_fieldtype_obj($field_type)
	{
		$class = ucfirst(strtolower($field_type)).'_ft';
		$this->EE->api_channel_fields->include_handler($field_type);
		
		if (class_exists($class))
		{
			// Instantiate the field type
			return new $class();
		}
		else
		{
			return null;
		}
	}



	private function _get_fieldtype_settings($field_data, $draft_data)
	{

		$_dst_enabled = ($this->EE->session->userdata('daylight_savings') == 'y' ? TRUE : FALSE);

		$field_settings = array();

		foreach ($field_data->result_array() as $row)
		{
			$field_fmt	= $row['field_fmt'];
			$field_dt 	= '';
			$field_data	= '';
			$dst_enabled	= '';
						
			$field_data 	= (isset($draft_data['field_id_'.$row['field_id']])) ? $draft_data['field_id_'.$row['field_id']] : $field_data;				
			$field_dt	= (isset($draft_data['field_dt_'.$row['field_id']])) ? $draft_data['field_dt_'.$row['field_id']] : 'y';
			$field_fmt	= (isset($draft_data['field_ft_'.$row['field_id']])) ? $draft_data['field_ft_'.$row['field_id']] : $field_fmt;						

			$settings = array(
				'field_instructions'	=> trim($row['field_instructions']),
				'field_text_direction'	=> ($row['field_text_direction'] == 'rtl') ? 'rtl' : 'ltr',
				'field_fmt'		=> $field_fmt,
				'field_dt'		=> $field_dt,
				'field_data'		=> $field_data,
				'field_name'		=> 'field_id_'.$row['field_id'],
				'dst_enabled'		=> $_dst_enabled
			);
			
			$ft_settings = array();

			if (isset($row['field_settings']) && strlen($row['field_settings']))
			{
				$ft_settings = unserialize(base64_decode($row['field_settings']));
			}
						
			$settings = array_merge($row, $settings, $ft_settings);
		}

		return $settings;
	}


	// This checks the data to ensure we have something meaningful before we send it to the fieldtypes draft methods
	private function _check_field_data($field_type, $data, $entry_id, $field_id)
	{
		switch($field_type)
		{
			case 'matrix':

				// Loggin
				$this->action_logger->add_to_log("ep_third_party_fieldtypes: _invalid_draft_data(): This is a Matrix field so check we have a row_order key in this array, if not empty it");
				
				if(!array_key_exists('row_order', $data)) $data = "";
		}
		return $data;
	}


	// After we have called the BWF specific methods in some field-types we need to update our
	// data array so we are in sync.
	private function _update_field_data($field_type, $data, $entry_id, $field_id)
	{
		switch($field_type)
		{
			case 'matrix':
			
				// Loggin
				$this->action_logger->add_to_log("ep_third_party_fieldtypes: _update_data_array_to_match_new_data(): This is a Matrix field so I'm going fix my row IDs");
			
				return $this->ep_matrix->update_row_ids($data, $entry_id, $field_id);
				break;
		}
		return $data;
	}



	// -----------------------------------------------------------------------------------------------------------
	// DELEGATION IMPLEMENTATIONS
	// All calls to field-type specific models are routed through here
	// -----------------------------------------------------------------------------------------------------------
	
	// Delegate the execution of the file field methods to the BWF Files controller
	function clean_up_native_file_field_data($data)
	{
		return $this->ep_files->clean_up_native_file_field_data($data);
	}
	
	function copy_safecracker_file_data_to_revision_post($data)
	{
		return $this->ep_files->copy_safecracker_file_data_to_revision_post($data);
	}


	// Delegate the execution of the relationships_query hook to the BWF Relationships controller
	function relationships_query($field_name, $entry_ids, $depths, $sql)
	{
		return $this->ep_relationships->query_hook($field_name, $entry_ids, $depths, $sql);
	}


	// Delegate the execution of the Zenbu's hooks to the BWF Zenbu controller
	function zenbu_modify_title_display($output, $entry_array, $row)
	{
		return $this->ep_zenbu->modify_title_display($output, $entry_array, $row);
	}

	function zenbu_modify_status_display($output, $entry_array, $row, $statuses)
	{
		return $this->ep_zenbu->modify_status_display($output, $entry_array, $row, $statuses);
	}

	function zenbu_filter_by_status($channel_id, $status, $rule, $where)
	{
		return $this->ep_zenbu->filter_by_status($channel_id, $status, $rule, $where);
	}

}