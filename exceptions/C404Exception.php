<?php
class C404Exception extends CAppException
{
	public $moudle='';
	public $type='';
	
	const CONTROLLER=1;
	const VIEW=2;
	const MODEL=3;
	const LIB=4;
	const EXCEPTION=4;
	const HELPER=5;
	const SNIPPET=6;
	const FORMATTER=7;
	const CONFIG=8;
	const INVALID_URL=9;
	const METHOD=10;
	
	public function __construct($moudle='',$type='')
	{
		parent::__construct("Could not locate the resource you request!",404);
		$this->moudle=$moudle;
		$this->type=$type;
	}
	
	private function get_type_string()
	{
		switch($this->type)
		{
			case self::CONTROLLER : return 'Controller';
			case self::VIEW : return 'View';
			case self::MODEL : return 'Model';
			case self::LIB : return 'Lib';
			case self::EXCEPTION : return 'Exception Definition';
			case self::HELPER : return 'Helper';
			case self::SNIPPET : return 'Snippet';
			case self::FORMATTER : return 'Formatter';
			case self::CONFIG : return 'Configuration';
			case self::METHOD : return 'Constroller Action';
			case self::INVALID_URL : return 'Invalid URL';
			default : return 'Unknown Module Type';
		}
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
		if($this->moudle) $errmsg .= "<b>Moudle.</b>:  {$this->moudle}<br/>";
		$errmsg .= "<b>Type.</b>:  ".$this->get_type_string()."<br/>";
		//$errmsg .= "<b>File: </b>:  {$this->file}<br/>";
		//$errmsg .= "<b>Line: </b>:  {$this->line}<br/>";
		echo $errmsg;
	}
	
}

?>