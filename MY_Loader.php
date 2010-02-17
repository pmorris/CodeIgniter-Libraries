<?php

/**
 * @author Philip Morris
 * @copyright 2008
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class MY_Loader extends CI_Loader
{
	function __construct()
	{
		parent::__construct();
	}
	
	function page( $view, $vars=array(), $return=false )
	{
		// application specific variables
		$CI =& get_instance();
		/*$layout_vars = array(
			'_sysmsg'	=> $CI->session->flashdata('sysmsg'),
			'_user'		=> unserialize($CI->session->userdata('user')),
			'_breadcrumbs'	=> $CI->breadcrumbs->get()
		);
		
		//merge passed vars w/ layout_vars
		$vars = array_merge($vars,$layout_vars); */
		
		$vars = $this->_add_layout_vars($vars);
		
		// load the layout
		$views_path = isset($CI->views_path) ? $CI->views_path : '';
		$vars['body'] = $this->view($views_path.'/'.$view, $vars, TRUE);
		$layout = isset($this->layout) ? $this->layout : 'default';
		
		return $this->view("_layouts/$layout", $vars, $return );
	}
	
	
	function view2($view, $vars = array(), $return = FALSE)
	{
		$vars = $this->_add_layout_vars($vars);
		return $this->view($view,$vars,$return);
	}
	
	private function _add_layout_vars($vars)
	{
		$CI =& get_instance();
		$layout_vars = array(
			'_sysmsg'			=> array_key_exists('sysmsg',$vars) ? $vars['sysmsg'] : $CI->session->flashdata('sysmsg'),
			'_sysmsg_type'		=> array_key_exists('sysmsg_type',$vars) ? $vars['sysmsg_type'] : $CI->session->flashdata('sysmsg_type'),
			'_user'				=> property_exists($CI,'user') ? $CI->user : '',
			'_breadcrumbs'		=> $CI->nav_history->get_breadcrumb(),
			'_header' 			=> $CI->application == 'plugin' ? $CI->get_header() : '',
			'_application'		=> $CI->application
		);
		
		if( $CI->application == 'plugin' )
		{
			$layout_vars['_header']			= $CI->get_header();
			$layout_vars['_agent']			= $CI->agent;			
			$layout_vars['mls']				= $CI->mls;
			$layout_vars['crm']				= $CI->crm;
		}
		
		// system message
		if( array_key_exists('sysmsg',$vars))
		{
			$layout_vars['_sysmsg'] 		= $vars['sysmsg'];
			$layout_vars['_sysmsg_type'] 	= $vars['sysmsg_type'];
		}
		elseif( $CI->session->flashdata('sysmsg') != '')
		{
			$layout_vars['_sysmsg'] 		= $CI->session->flashdata('sysmsg');
			$layout_vars['_sysmsg_type'] 	= $CI->session->flashdata('sysmsg_type');
		}
		elseif( property_exists( $CI,'sysmsg') )
		{
			$layout_vars['_sysmsg'] 		= $CI->sysmsg->message;
			$layout_vars['_sysmsg_type'] 	= $CI->sysmsg->type;
		}
		
		return array_merge($vars,$layout_vars);
	}	

}

/* End of file someclass.php */
/* Location: ./application/libraries/sampleclass.php */