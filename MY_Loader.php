<?php

/**
 * @author Philip Morris
 * @copyright 2008
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class MY_Loader extends CI_Loader {
	function __construct() {
		parent::__construct();
	}
	
        /**
         * Load a view within the specified layout
         * Functions as an extension of CI_Loader::view()
         * 
         * @param string the path to the view, within the controller's views_path (property)
         * @param array Associative array of variables to make available to the view and layout
         * @param bool Should the view/layout contents be returned, instead of written to output buffer
         * @return mixed Boolean true, or string output based on the $return parameter
         */
	function page( $view, $vars=array(), $return=false ) {
		// application specific variables
		$CI =& get_instance();
		
                // merge in standard system variables
		$vars = $this->_add_layout_vars($vars);
		
		// load the layout
		$views_path = isset($CI->views_path) ? $CI->views_path : '';
		$vars['body'] = $this->view($views_path.'/'.$view, $vars, TRUE);
		$layout = isset($this->layout) ? $this->layout : 'default';
		
		return $this->view("_layouts/$layout", $vars, $return );
	}
	
	/**
         * Merges the standards layout system variables to the $vars passed to CI_Loader::view()
         *
         * @deprecated
         * @param string The view
         * @param array An associative array or variables to be passed to the view
         * @param bool Should the view/layout contents be returned, instead of written to output buffer
         * @return mixed Boolean true, or string output based on the $return parameter
         */
	function view2($view, $vars = array(), $return = FALSE) {
		$vars = $this->_add_layout_vars($vars);
		return $this->view($view,$vars,$return);
	}
	
        /**
         * Merge application/runtime variables into the variables for view rendering
         *
         * @param array An associative array of variables
         * @return array The $vars param merged with the common variables used within the application
         */
	private function _add_layout_vars($vars) {
		$CI =& get_instance();
		$layout_vars = array(
			'_sysmsg'          => array_key_exists('sysmsg',$vars) ? $vars['sysmsg'] : $CI->session->flashdata('sysmsg'),
			'_sysmsg_type'     => array_key_exists('sysmsg_type',$vars) ? $vars['sysmsg_type'] : $CI->session->flashdata('sysmsg_type'),
			'_user'            => property_exists($CI,'user') ? $CI->user : '',
			'_breadcrumbs'	   => $CI->nav_history->get_breadcrumb(),
			'_header'          => $CI->application == 'plugin' ? $CI->get_header() : '',
			'_application'	   => $CI->application
		);
		
		if( $CI->application == 'plugin' ) {
			$layout_vars['_header']	= $CI->get_header();
			$layout_vars['_agent']  = $CI->agent;			
			$layout_vars['mls']     = $CI->mls;
			$layout_vars['crm']     = $CI->crm;
		}
		
		// system message
		if( array_key_exists('sysmsg',$vars)) {
			$layout_vars['_sysmsg']      = $vars['sysmsg'];
			$layout_vars['_sysmsg_type'] = $vars['sysmsg_type'];
		} elseif( $CI->session->flashdata('sysmsg') != '') {
			$layout_vars['_sysmsg']      = $CI->session->flashdata('sysmsg');
			$layout_vars['_sysmsg_type'] = $CI->session->flashdata('sysmsg_type');
		} elseif( property_exists( $CI,'sysmsg') ) {
			$layout_vars['_sysmsg']      = $CI->sysmsg->message;
			$layout_vars['_sysmsg_type'] = $CI->sysmsg->type;
		}
		
		return array_merge($vars,$layout_vars);
	}	

}

/* End of file MY_Loader.php */
/* Location: ./application/libraries/MY_Loader.php */