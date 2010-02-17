<?php

/**
 * @author Philip Morris
 * @copyright 2008
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class MY_Email extends CI_Email
{
	function __construct($config = array())
	{
		// call the parent's constructor
		$this->CI_Email($config);
	}
	
	/**
	 * Initialize the Email Data
	 *
	 * @access	public
	 * @return	void
	 */
	function clear($clear_attachments = FALSE)
	{
		// patch for bug # 7826
		// http://codeigniter.com/bug_tracker/bug/7826/
		if ($this->_get_protocol() == "smtp")
		{
		        $this->_cc_array = array();
		        $this->_bcc_array = array();
		}
		parent::clear($clear_attachments);	
	}
}

/* End of file MY_Email.php */
/* Location: ./application/libraries/MY_Email.php */