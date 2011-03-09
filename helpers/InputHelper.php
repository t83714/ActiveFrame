<?php
class InputHelper
{

	public static function set_value($id,$name,$value=null)
	{
		$name=trim($name);
		if(empty($name)) return '';
		if($value===null && !isset($_POST[$name]) ) return '';
		if($value===null) $value=$_POST[$name];
		$value_str=json_encode($value);
		return "<script type=\"text/javascript\">$('$id').value=$value_str;</script>";
	}
	
}
?>