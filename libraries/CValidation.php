<?php  
if(!defined('IN_APP')) {
	exit('Access Denied');
}
// ------------------------------------------------------------------------

/**
 * Validation Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Validation
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/libraries/validation.html
 
 Changes Made for embeding into smartFrame
 */
class CValidation {
	
	var $error_string		= '';
	var $_error_array		= array();
	var $_rules				= array();
	var $_fields			= array();
	var $_error_messages	= array();
	var $_current_field  	= '';
	var $_safe_form_data 	= FALSE;
	var $_error_prefix		= '<p>';
	var $_error_suffix		= '</p>';
	var $_alias;
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Fields
	 *
	 * This function takes an array of field names as input
	 * and generates class variables with the same name, which will
	 * either be blank or contain the $_POST value corresponding to it
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	function set_fields($data = '', $field = '')
	{	
		if ($data == '')
		{
			if (count($this->_fields) == 0)
			{
				return FALSE;
			}
		}
		else
		{
			if ( ! is_array($data))
			{
				$data = array($data => $field);
			}
			
			if (count($data) > 0)
			{
				$this->_fields = $data;
			}
		}		
			
		foreach($this->_fields as $key => $val)
		{		
			$this->$key = ( ! isset($_POST[$key]) OR is_array($_POST[$key])) ? '' : $this->prep_for_form($_POST[$key]);
			
			$error = $key.'_error';
			if ( ! isset($this->$error))
			{
				$this->$error = '';
			}
		}		
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Rules
	 *
	 * This function takes an array of field names and validation
	 * rules as input ad simply stores is for use later.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @return	void
	 */
	function set_rules($data, $rules = '')
	{
		if ( ! is_array($data))
		{
			if ($rules == '')
				return;
				
			$data[$data] = $rules;
		}
	
		foreach ($data as $key => $val)
		{
			$this->_rules[$key] = $val;
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Error Message
	 *
	 * Lets users set their own error messages on the fly.  Note:  The key
	 * name has to match the  function name that it corresponds to.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function set_message($lang, $val = '')
	{
		if ( ! is_array($lang))
		{
			$lang = array($lang => $val);
		}
	
		$this->_error_messages = array_merge($this->_error_messages, $lang);
	}
	
	
		/**
	 * Set alias for data field
	 *
	 * Lets users set their own error messages on the fly.  Note:  The key
	 * name has to match the  function name that it corresponds to.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function set_alias($alias)
	{
		if ( ! is_array($alias)) return;
				
		$this->_alias= $alias;

	}
	
	
	// --------------------------------------------------------------------
	
	/**
	 * Set The Error Delimiter
	 *
	 * Permits a prefix/suffix to be added to each error message
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	void
	 */	
	function set_error_delimiters($prefix = '<p>', $suffix = '</p>')
	{
		$this->_error_prefix = $prefix;
		$this->_error_suffix = $suffix;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Run the Validator
	 *
	 * This function does all the work.
	 *
	 * @access	public
	 * @return	bool
	 */		
	function run()
	{
		global $APP_ENV; //---load APP_ENV
		// Do we even have any data to process?  Mm?
		if (count($_POST) == 0 OR count($this->_rules) == 0)
		{
			return FALSE;
		}
	
		//// Load the language file containing error messages
		//$this->CI->lang->load('validation');
							
		// Cycle through the rules and test for errors
		foreach ($this->_rules as $field => $rules)
		{
			//Explode out the rules!
			$ex = explode('|', $rules);

			// Is the field required?  If not, if the field is blank  we'll move on to the next test
			if ( ! in_array('required', $ex, TRUE) AND strpos($rules, 'callback_') === FALSE)
			{
				if ( ! isset($_POST[$field]) OR $_POST[$field] == '')
				{
					continue;
				}
			}
			
			/*
			 * Are we dealing with an "isset" rule?
			 *
			 * Before going further, we'll see if one of the rules
			 * is to check whether the item is set (typically this
			 * applies only to checkboxes).  If so, we'll
			 * test for it here since there's not reason to go
			 * further
			 */
			if ( ! isset($_POST[$field]))
			{			
				if (in_array('isset', $ex, TRUE) OR in_array('required', $ex))
				{
					if ( ! isset($this->_error_messages['isset'])) $line=$APP_ENV['globalLanguageVar']['validation_isset'];
					else
					{
						$line = $this->_error_messages['isset'];
					}

					$field = ( ! isset($this->_fields[$field])) ? $field : $this->_fields[$field];
					if(isset($this->_alias)) $this->_error_array[] = sprintf($line, $this->_alias[$field]);
					else $this->_error_array[] = sprintf($line, $field);

				}
						
				continue;
			}
	
			/*
			 * Set the current field
			 *
			 * The various prepping functions need to know the
			 * current field name so they can do this:
			 *
			 * $_POST[$this->_current_field] == 'bla bla';
			 */
			$this->_current_field = $field;

			// Cycle through the rules!
			foreach ($ex As $rule)
			{
				// Is the rule a callback?			
				$callback = FALSE;
				if (substr($rule, 0, 9) == 'callback_')
				{
					$rule = substr($rule, 9);
					$callback = TRUE;
				}
				
				// Strip the parameter (if exists) from the rule
				// Rules can contain a parameter: max_length[5]
				$param = FALSE;
				if (preg_match("/(.*?)\[(.*?)\]/", $rule, $match))
				{
					$rule	= $match[1];
					$param	= $match[2];
				}
				
				// Call the function that corresponds to the rule
				if ($callback === TRUE)
				{
					if ( ! method_exists($APP_ENV['controller'], $rule))
					{ 		
						continue;
					}
					
					$result = $APP_ENV['controller']->$rule($_POST[$field], $param);	
					
					// If the field isn't required and we just processed a callback we'll move on...
					if ( ! in_array('required', $ex, TRUE) AND $result !== FALSE)
					{
						continue 2;
					}
					
				}
				else
				{				
					if ( ! method_exists($this, $rule))
					{
						/*
						 * Run the native PHP function if called for
						 *
						 * If our own wrapper function doesn't exist we see
						 * if a native PHP function does. Users can use
						 * any native PHP function call that has one param.
						 */
						if (function_exists($rule))
						{
							$_POST[$field] = $rule($_POST[$field]);
							$this->$field = $_POST[$field];
						}
											
						continue;
					}
					
					$result = $this->$rule($_POST[$field], $param);
				}
								
				// Did the rule test negatively?  If so, grab the error.
				if ($result === FALSE)
				{
					if ( ! isset($this->_error_messages[$rule])) $line =$APP_ENV['globalLanguageVar']['validation_'.$rule];
					else
					{
						$line = $this->_error_messages[$rule];
					}				

					// Build the error message
					$mfield = ( ! isset($this->_fields[$field])) ? $field : $this->_fields[$field];
					$mparam = ( ! isset($this->_fields[$param])) ? $param : $this->_fields[$param];
					
					if(isset($this->_alias)) $mfield=$this->_alias[$mfield];
					
					$message = sprintf($line, $mfield, $mparam);
					
					// Set the error variable.  Example: $this->username_error
					$error = $field.'_error';
					$this->$error = $this->_error_prefix.$message.$this->_error_suffix;

					// Add the error to the error array
					$this->_error_array[] = $message;				
					continue 2;
				}				
			}
			
		}
		
		$total_errors = count($this->_error_array);

		/*
		 * Recompile the class variables
		 *
		 * If any prepping functions were called the $_POST data
		 * might now be different then the corresponding class
		 * variables so we'll set them anew.
		 */	
		if ($total_errors > 0)
		{
			$this->_safe_form_data = TRUE;
		}
		
		$this->set_fields();

		// Did we end up with any errors?
		if ($total_errors == 0)
		{
			return TRUE;
		}
		
		// Generate the error string
		foreach ($this->_error_array as $val)
		{
			$this->error_string .= $this->_error_prefix.$val.$this->_error_suffix."\n";
		}

		return FALSE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Required
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function required($str)
	{
		if ( ! is_array($str))
		{
			return (trim($str) == '') ? FALSE : TRUE;
		}
		else
		{
			return ( ! empty($str));
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Match one field to another
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function matches($str, $field)
	{
		if ( ! isset($_POST[$field]))
		{
			return FALSE;
		}
		
		return ($str !== $_POST[$field]) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Minimum Length
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function min_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return FALSE;
		}
	
		return (strlen($str) < $val) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Max Length
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function max_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return FALSE;
		}
	
		return (strlen($str) > $val) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Exact Length
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function exact_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return FALSE;
		}
	
		return (strlen($str) != $val) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Valid Email
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function valid_email($str)
	{
		return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Validate IP Address
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function valid_ip($ip)
	{
		$tempIpArray=explode('.',$ip,4);
		if(!is_array($tempIpArray) || count($tempArray<4)) return false;
		else{
			if($tempIpArray[0]<255 && $tempIpAddress[0]>=0 && $tempIpArray[1]<255 && $tempIpAddress[1]>=0 && $tempIpArray[2]<255 && $tempIpAddress[2]>=0) return implode('.',$tempIpAddress);
			else return false;
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Alpha
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */		
	function alpha($str)
	{
		return ( ! preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Alpha-numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function alpha_numeric($str)
	{
		return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function alpha_dash($str)
	{
		return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Numeric
	 *
	 * @access	public
	 * @param	int
	 * @return	bool
	 */	
	function numeric($str)
	{
		return ( ! ereg("^[0-9\.]+$", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Is Numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function is_numeric($str)
	{
		return ( ! is_numeric($str)) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Select
	 *
	 * Enables pull-down lists to be set to the value the user
	 * selected in the event of an error
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */	
	function set_select($field = '', $value = '')
	{
		if ($field == '' OR $value == '' OR  ! isset($_POST[$field]))
		{
			return '';
		}
			
		if ($_POST[$field] == $value)
		{
			return ' selected="selected"';
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Radio
	 *
	 * Enables radio buttons to be set to the value the user
	 * selected in the event of an error
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */	
	function set_radio($field = '', $value = '')
	{
		if ($field == '' OR $value == '' OR  ! isset($_POST[$field]))
		{
			return '';
		}
			
		if ($_POST[$field] == $value)
		{
			return ' checked="checked"';
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Checkbox
	 *
	 * Enables checkboxes to be set to the value the user
	 * selected in the event of an error
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */	
	function set_checkbox($field = '', $value = '')
	{
		if ($field == '' OR $value == '' OR  ! isset($_POST[$field]))
		{
			return '';
		}
			
		if ($_POST[$field] == $value)
		{
			return ' checked="checked"';
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Prep data for form
	 *
	 * This function allows HTML to be safely shown in a form.
	 * Special characters are converted.
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function prep_for_form($str = '')
	{
		if ($this->_safe_form_data == FALSE OR $str == '')
		{
			return $str;
		}

		return str_replace(array("'", '"', '<', '>'), array("&#39;", "&quot;", '&lt;', '&gt;'), stripslashes($str));
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Prep URL
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */	
	function prep_url($str = '')
	{
		if ($str == 'http://' OR $str == '')
		{
			$_POST[$this->_current_field] = '';
			return;
		}
		
		if (substr($str, 0, 7) != 'http://' && substr($str, 0, 8) != 'https://')
		{
			$str = 'http://'.$str;
		}
		
		$_POST[$this->_current_field] = $str;
	}
	

	// --------------------------------------------------------------------
	
	/**
	 * Convert PHP tags to entities
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */	
	function encode_php_tags($str)
	{
		$_POST[$this->_current_field] = str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
	}

}
// END Validation Class
?>