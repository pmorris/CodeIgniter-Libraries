<?php

/**
 * @author Philip Morris
 * @copyright 2008
 */

class MY_Output extends CI_Output
{
	/**
	 * Merges the queries from a database object to the main database -- typically for the profiler
	 * @param class		a database object
	 * @param boolean	force execution if the profiler is not enabled?
	 */     	
	function add_queries($database,$force=FALSE)
	{
		$CI =& get_instance();
		
		if(( !property_exists($CI,'output') || !property_exists($CI->output,'enable_profiler') && $CI->output->enable_profiler !== TRUE) && !$force)
			return; 	
		
		if(property_exists($database,'queries') && property_exists($database,'query_times')
			&& is_array($database->queries) && is_array($database->query_times)
			&& sizeof($database->queries) == sizeof($database->query_times) 
		) {
			$CI->db->query_times = array_merge($CI->db->query_times,$database->query_times);
			$CI->db->queries = array_merge($CI->db->queries,$database->queries);		
		}
	}
}


?>