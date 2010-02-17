<?php

/**
 * @author Phil Morris
 * @copyright 2009
 * @version 0.1.9a
 */

class RailsMapper extends DataMapper
{	
	var $belongs_to = array();
	var $has_many_through = array();
	
	var $join_fields = array();
	
	function __construct()
	{
		parent::__construct();
		$this->_register_join_fields();
	}
	
    
    public function __get($name)
    {
    	// // the conditions below should be replace by using the _get_relationship_type method
    	
    	// belongs to relationship
    	if(in_array($name,$this->belongs_to))
    	{
    		return $this->_belongs_to($name);
    	}
    	elseif( array_key_exists($name,$this->belongs_to) && is_array($this->belongs_to[$name]) )
    	{
    		return $this->_belongs_to($name,$this->belongs_to[$name]);
    	}
    	
    	//has many relationship
    	elseif(in_array($name,$this->has_many))
    	{
    		return $this->_has_many($name);
    	}
    	elseif(array_key_exists($name,$this->has_many) && is_array($this->has_many[$name]))
    	{
    		return $this->_has_many($name,$this->has_many[$name]); // for has_many :through
    	}
    	
    	// has many through relationship
    	elseif(array_key_exists($name,$this->has_many_through))
    	{
    		return $this->_has_many_through($name);
    	}
    	
    	// has one relationship
    	elseif(in_array($name,$this->has_one))
    	{
    		return $this->_has_one($name);
    	}
    	elseif( array_key_exists($name,$this->has_one) && is_array($this->has_one[$name]) )
    	{
    		return $this->_has_one($name,$this->has_one[$name]);
    	}
    	
    	// alias for class method accessor
    	elseif( method_exists($this,$name))
    	{
    		#echo $this;
    		return $this->$name();
    	}
    	else
    	{
    		return parent::__get($name);
    	}
    }
    
    /*public function __call($name, $args)
    {
    	#echo "calling: $name\n";
    	if(in_array($name,$this->belongs_to))
    	{
    		return $this->_belongs_to($name);
    	}
    	else
    		return parent::__call($name,$args);
    }*/
    
    public function _get_relationship_type($name)
    {
    	// belongs to relationship
		if(in_array($name,$this->belongs_to))
    		return 'belongs_to';
    		
    	//has many relationship
    	elseif(in_array($name,$this->has_many))
    		return 'has_many';
    		
    	elseif(array_key_exists($name,$this->has_many) && is_array($this->has_many[$name])) 
    		return 'has_many'; // for has_many :through
    		
    	// has one relationship
    	elseif(in_array($name,$this->has_one))
    		return 'has_one';
    }
    
    private function _belongs_to($object,$options=array())
    {
    	$field = array_key_exists('foreign_key',$options) ? $options['foreign_key'] : $object . '_id'; // the foreign key in the local table
    	#$class = ucfirst($object);
    	
    	$class = array_key_exists('class_name',$options) ? $options['class_name'] : ucfirst($object);
    	if( $object == 'source')	{	echo $class.' ';	}
    	// the class must exist
		if( ! class_exists($class) )
		{
			log_message('error', "RailsMapper (belongs_to) :: The `$class` class does not exist. ($object)");
    		return false;
    	}
    	
    	// the foreign key must contain a value
    	if( empty($this->$field) )
    	{
    		log_message('error', "RailsMapper (belongs_to) :: The `$field` of the " .  get_class($this) . " is empty. ($object)");
    		return false;
    	}
    	
    	$a = new $class;
    	$a->where(array('id'=>$this->$field));
    	
		// additional conditions
    	if( array_key_exists('conditions',$options))
    		$a->where($options['conditions']);
    	$a->get(1);
    	
    	
    	// Save the object as a local member so that we won't have to query for it a 2nd time
    	$this->$object = $a;
    	return $a;
    }
    
    private function _has_many($objects,$options=array())
    {
    	$class = array_key_exists('class_name',$options) ? $options['class_name'] : ucfirst(singular($objects));
    	
    	// patch for backword compatability with versions prior to 0.1.7
    	if( array_key_exists('class',$options)) { $class=$options['class']; } 
    	
    
    	
		$field = $fk = array_key_exists('foreign_key',$options) ? $options['foreign_key'] : strtolower(get_class($this)) . '_id';
		#$field = $fk = strtolower(get_class($this)) . '_id'; //the foreign key in the referenced table
    	
    	
		// the class must exist
		if( ! class_exists($class) )
    	{
    		log_message('error', "RailsMapper (has_many) :: The `$class` class does not exist. ($objects)");
    		return false;
    	}
    	
    	// the primary key must contain a value
    	if( empty($this->id) )
    	{
    		log_message('error', "RailsMapper (has_many) :: The `id` of the `" . get_class($this) . "` is empty. ($objects)");
    		return false;
    	}
    	
    	// instanciance the new object of data type to be returned
    	$a = new $class;

    	
    	if( array_key_exists('through',$options))
    	{
    		// has_many :through relationship
			$join_table = $this->has_many[$objects]['through'];
			$join_fk = singular($objects) . '_id';
			
			$sql = "SELECT a.*
					FROM {$a->table} a
					JOIN {$join_table} b ON a.id = b.{$join_fk}
					WHERE b.{$fk} = {$this->id}";
			$a = $a->query($sql)->all;
    	}
    	else
		{
    		// simple has meny relationship
    		// the field must be a method of the class
	    	if( ! property_exists($a,$field))
	    	{
	    		log_message('error', "RailsMapper (has_many) :: The `$field` does not exist in `$class`. ($objects)");
	    		return false;
	    	}
	    	
	    	// additional conditions
	    	if( array_key_exists('conditions',$options))
	    		$a->where($options['conditions']);
	    	$a->where(array($field=>$this->id));
	    	
	    	// resultset order
	    	if( array_key_exists('order',$options))
	    		$a->order_by($options['order']);
	    	$a = $a->get()->all;
    	}
    	
    	
    	// Save the objects as a local member so that we won't have to query for it a 2nd time
    	$this->$objects = $a;
    	
    	return $a;
    }
    
    private function _has_one($object,$options=array())
    {
    	#$field = array_key_exists('foreign_key',$options) ? $options['foreign_key'] : $object . '_id'; // the foreign key in the local table
    	
    	$class = array_key_exists('class_name',$options) ? $options['class_name'] : ucfirst(strtolower($object));
    	
    	$a = new $class();
    	
    	// potential foreign keys names within the object being referenced
    	$fk1 = strtolower(get_class($this)) . '_id';
    	$fk2 = strtolower(get_parent_class($this)) . '_id'; // check the parent class also
    	
    	// check to see if the property (db column exists in the $this table)
    	if( property_exists($a,$fk1) )
    	{	// $fk exists in $a
    		#echo "<br/>$fk1 exists in $a";
    		$fk = $fk1;
    	}
    	elseif( property_exists($a,$fk2) )
    	{	// $fk2 exists in $a
    		$fk = $fk2;
    	}
    	else
    	{	// foreign key not found within $a
    		return false;
    	}
    	
    	$a->where( $fk, $this->id);
    	
    	// additional conditions
    	if( array_key_exists('conditions',$options))
    		$a->where($options['conditions']);
    	
    	$a->get(1);
    	
    	#$where = array( $fk => $this->id);
    	#$a->get_where($where,1);
    	
    	// Save the objects as a local member so that we won't have to query for it a 2nd time
    	$this->$object = $a;
    	
    	return $a;
    }
    
    /**
     * registers any fields to be joined in queries by the model
     */
    private function _register_join_fields()
    {
    	if( sizeof($this->join_fields) > 0 )
    	{
    		foreach($this->join_fields as $field)
    		{
    			if( ! in_array($field,$this->fields) )
    				array_push($this->fields,$field);
    		}
    	}
    }
    
    /**
	 * Join
	 *
	 * Generates the JOIN portion of the query
	 *
	 * @access	public
	 * @param	string
	 * @param	string	the join condition
	 * @param	string	the type of join
	 * @return	object
	 */
    public function join($table, $cond, $type = '')
    {
    	// prevents conflicts when joining tables
    	$this->select("{$this->table}.*");
    	$this->db->join($table,$cond,$type);
    }
    
    /**
     * Send multiple join requests
     * 
     * @param array $joins
     * @return boolean status of success
     */
    public function join_multiple($joins)
    {
    	if( ! is_array($joins))
    	{
    		log_message('error', "RailsMapper (join_multiple) :: An array must be passed as the first parameter.");
    		return FALSE;
    	}
    		
    	if( sizeof($joins))
    	{
    		foreach( $joins as $k => $v )
    		{
    			// array keys 0 and 1 are required, array key 2 is optional, any other keys are ignored
    			if( ! array_key_exists(0,$v) || ! array_key_exists(1,$v) )
    			{	
    				log_message('error', "RailsMapper (join_multiple) :: Array at offset {$k} is missing key 0 or 1.");
    				return FALSE;
    			}
    			$this->join($v[0],$v[1],array_key_exists(2,$v) ? $v[2] : '');
    		}
    	}
    	return TRUE;
    }
    
    /**
	 * Join
	 *
	 * Generates the JOIN portion of the query using the rules defined by the relationship
	 *
	 * @access	public
	 * @param	string
	 * @param	string	the type of join
	 * @return	string	name of the table joined
	 */
    public function join_class($model, $type = '')
    {
    	$class = ucfirst(strtolower($model));
    	$model = strtolower($model);
    	
    	// verify relationship exists
    	switch( $this->_get_relationship_type($model) )
    	{
    		case 'belongs_to':
    			$object = new $class();
    			$table = $object->table;
    			$cond = "{$this->table}.{$model}_id = {$object->table}.id";
    			break;
    		case 'has_many':
    		case 'has_one':
    			echo 'join_class() attempted for an unsupported relationship type ()';
    		default:
    			echo 'join_class() attempted for an unknown class. Try using join() instead.';
    	}
    	
    	
    	if( isset($cond) )
    	{
    		$this->join($table,$cond,$type);
    		return $table;
    	}
    	
    }
    
    /*public function get()
    {
    	// prevents conflicts when joining tables
    	
    	
    	return $this->get();
    }*/
    
    /**
	 * Where
	 *
	 * Called by where() or or_where().
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @param	bool
	 * @return	object
	 */
	function _where($key, $value = NULL, $type = 'AND ', $escape = NULL)
	{
		if ( ! is_array($key))
		{
			$key = array($this->table . '.' . $key => $value);
		}

		// Check if this is a related object
		if ( ! empty($this->parent))
		{
			foreach ($key as $k => $v)
			{
				$key[$this->table . '.' . $k] = $v;
				unset($key[$k]);
			}
		}

		$this->db->_where($key, $value, $type, $escape);

		// For method chaining
		return $this;
	}
    
    
    // --------------------------------------------------------------------

	/**
	 * Clear
	 *
	 * Clears the current object.
	 *
	 * @access	public
	 * @return	void
	 */
	function clear()
	{
		// Clear the all list
		$this->all = array();

		// Clear errors
		$this->error = new stdClass();
		$this->error->all = array();
		$this->error->string = '';

		// Clear this objects properties and set blank error messages in case they are accessed
		foreach ($this->fields as $field)
		{
			$this->{$field} = NULL;
			$this->error->{$field} = '';
		}

		// Clear this objects "has many" related objects
		foreach ($this->has_many as $key => $value)
		{	
			if( is_array($value) )
				unset($this->{$key});
			else
				unset($this->{$value});
		}

		// Clear this objects "has one" related objects
		foreach ($this->has_one as $key => $value)
		{
			if( is_array($value) )
				unset($this->{$key});
			else
				unset($this->{$value});
		}

		// Clear the query related list
		$this->query_related = array();

		// Clear and refresh stored values
		$this->stored = new stdClass();

		$this->_refresh_stored_values();
	}
  
  
  
  
  	public function to_yaml()
  	{
  		$o = $this->_yaml_clean($this);
  		
  		return '<pre>' . get_class($this) . ' ' . str_replace( 'stdClass ','', print_r($o,1)) . '</pre>';
  		
  	}
  	private function _yaml_clean($obj,$recurse=TRUE)
	{
		$o = new StdClass;

		foreach(get_object_vars($obj) as $k=>$v)
		{
			if( in_array($k,$obj->fields))
				$o->$k = $obj->$k;
		}

		foreach( array_merge( property('belongs_to',$obj,array() )) as $relationship )
		{
			if( is_a($obj->$relationship,$relationship))
				$o->$relationship = $this->_yaml_clean($obj->$relationship,false);
			else
				$o->$relationship = '';
		}
		
		foreach( array_merge( property('has_many',$obj,array() )) as $relationship )
		{
			
			if( is_array($obj->$relationship))
			{
				$a = $obj->$relationship;
				#$class = get_class($a[1]);
				$class = get_class(current($a));
				
				$a = array();
				if( $recurse )
				{
					foreach( $obj->$relationship as $oo )
						array_push($a,$this->_yaml_clean($oo,false));
					$o->$relationship = $a;
				}
				else
				{
					$o->$relationship = sizeof($obj->$relationship) . " $class Objects";
				}	
				
			}
			else
				$o->$relationship = '';
		}
		
		return $o;		
	}  
}

?>