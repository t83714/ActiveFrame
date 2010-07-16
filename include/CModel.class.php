<?php
/**
 * @author Jacky Jiang
 * @version 0.1.2
 */
class CModel implements ArrayAccess
{
	protected $db;
	
	function __construct()
	{
		global $APP_ENV;
		$this->db=$APP_ENV['db'];
	}
	
	public function offsetExists($name)
	{
		return method_exists($this,$name);
	}
	
	public function offsetGet($name)
	{
		return call_user_func(array($this,$name));
	}
	
	public function offsetSet($name,$value)
	{
		return;
	}
	
	public function offsetUnset($name)
	{
		return;
	}
	
}
?>