<?php

/**
 * @author Philip Morris
 * @copyright 2009
 */

class MY_Exceptions extends CI_Exceptions
{
	function __construct()
	{
		parent::__construct();
	}
	
	function show_php_error($severity, $message, $filepath, $line)
	{
		$severity = ( ! isset($this->levels[$severity])) ? $severity : $this->levels[$severity];
	
		$filepath = str_replace("\\", "/", $filepath);
		
		// For safety reasons we do not show the full file path
		if (FALSE !== strpos($filepath, '/'))
		{
			$x = explode('/', $filepath);
			$filepath = $x[count($x)-2].'/'.end($x);
		}
		
		// BEGIN DATABASE LOGGING
		$CI =& get_instance();
		if( is_object($CI) && (!property_exists($CI,'debugmode') || $CI->debugmode != TRUE ))
		{
			$e = new Error();
			$e->source		= '';
			$e->severity 	= $severity;
			$e->message		= $message;
			$e->filepath	= $filepath;
			$e->line		= $line;
			$e->application	= property('application',$CI,'');
			
			if( property_exists($CI,'user') )
				$e->user_id		= $CI->user->id;
			$e->save(); 
			$error_id 		= $e->id;
		}
		// END DATABASE LOGGING
		
		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();	
		}
		ob_start();
		include(APPPATH.'errors/error_php'.EXT);
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}
	
}

?>