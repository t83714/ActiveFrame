<?php
class CAppException extends Exception 
{
	public function __construct($message,$code=0)
	{
		parent::__construct($message,$code);
	}
	
	public function clearAppEnv()
	{

	}
	
	public function translate_const($v)
	{
		$class_ref=new ReflectionClass(get_class($this));
		$cs=$class_ref->getConstants();
		$name=array_search($v,$cs,true);
		if($name===false) return "Unknown";
		else return ucwords(strtolower(str_replace('_',' ',$name)));
	}
	
	public function handleException()
	{
		ob_clean();
		$timestamp = time();
		if($this->message) {
			$errmsg = "<b>Application Error info</b>: \n\n<br/>";
		}
		$errmsg .= "<b>Time</b>: ".gmdate("Y-n-j g:ia", $timestamp + ($_SERVER['timeoffset'] * 3600))."\n<br/>";
		$errmsg .= "<b>Detail.</b>:  {$this->message}<br/>";
		$errmsg .= "<b>Errno.</b>:  {$this->code}<br/>";
		//$errmsg .= "<b>File: </b>:  {$this->file}<br/>";
		//$errmsg .= "<b>Line: </b>:  {$this->line}<br/>";
		echo $errmsg;
	}
	
	
	
}
?>