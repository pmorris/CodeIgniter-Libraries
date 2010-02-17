<?php

/**
 * @author Philip Morris
 * @copyright 2008
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class MY_Session extends CI_Session
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Preserve all flashdata
	 * 
	 */
	public function keep_all_flashdata()
	{
		$ud = $this->userdata;
		if( ! is_array($ud) || sizeof($ud) == 0 )
			return;
			
		// interate through all userdata and work on those starting with 'flash:old:'
		foreach( $ud as $key => $value )
		{
			if( substr($key,0,10) == 'flash:old:')
			{
				$key2 = substr($key,10);
				$this->set_userdata('flash:new:'.$key2,$value);
			}
		}
		return;
	}
		
}

/* End of file MY_Session.php */
/* Location: ./application/libraries/MY_Session.php */