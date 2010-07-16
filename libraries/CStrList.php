<?php
class CStrList implements Iterator, ArrayAccess, Countable
{
	private $str_list;
	private $delimiter;
	private $list_array=array();
	function __construct($list,$delimiter=',')
	{

		$this->delimiter=$delimiter;
		if(is_string($list))
		{
			$this->str_list=$list;
			$this->str_to_array();
		}else if(is_array($list)) $this->list_array=$list;
	}
	
	function __toString()
	{
		$this->array_to_str();
		return $this->str_list;
	}
	
	function toString()
	{
		return $this->__toString();
	}
	
	private function str_to_array()
	{
		if(!empty($this->str_list)) 
		{
			$this->list_array=explode($this->delimiter,$this->str_list);
		}
		else $this->list_array=array();
	}
	
	private function array_to_str()
	{
		$this->str_list=implode($this->delimiter,$this->list_array);
	}
	
	public function get_array()
	{
		$this->str_to_array();
		return $this->list_array;
	}
	
	
	function __get($name)
	{
		switch($name)
		{
			case 'list_array' : $this->str_to_array(); return $this->list_array;
			case 'number'	: return count($this);
		}
		return NULL;
	}
	
	public function rewind() 
	{
	  reset($this->list_array);
	}

	public function current() 
	{
	  return current($this->list_array);
	}
	
	public function key() 
	{
	  return key($this->list_array);
	}
	
	public function next() 
	{
	  return next($this->list_array);
	}
	
	public function valid() 
	{
	  return ($this->current() !== false);
	}

	public function key_exists($key)
	{
		return key_exists($key,$this->list_array);
	}
	
	public function array_keys()
	{
		return array_keys($this->list_array);
	}
	
	public function array_values()
	{
		return array_values($this->list_array);
	}
	
	
	public function offsetExists($n)
	{
		if(isset($this->list_array[$n])) return true;
		else return false;
	}
	
	public function offsetGet($n)
	{
		if(isset($this->list_array[$n])) return $this->list_array[$n];
		else return NULL;
	}
	
	public function offsetSet($name,$value)
	{
		if(!in_array($value,$this->list_array)) $this->list_array[$name]=$value;
	}
	
	public function offsetUnset($name)
	{
		unset($this->list_array[$name]);
	}
	
	public function count()
	{
		return count($this->list_array);
	}
	
	public function delete_item($value)
	{
		foreach($this->list_array as $k =>$v)
			if($v==$value) 
			{
				unset($this->list_array[$k]);
				return true;
			}
		return false;
	}
	
	public function merge($arr,$uniq=true)
	{
		$this->list_array=array_merge($this->list_array,$arr);
		if($uniq===true) $this->list_array=array_unique($this->list_array);
	}
	
	
}
?>