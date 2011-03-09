<?php
class CDBException extends CAppException
{
	const ERR_CONSTRAINT=1451;
	const ERR_OVER_LIMIT=1114;
	
	public $sql='';
	
	public function __construct($message,$code=0,$sql='')
	{
		parent::__construct($message,$code);
		$this->sql=$sql;
	}
	public function handleException()
	{
		ob_clean();
		if($this->code==1114) exit($this->get_over_limit_page());
		else exit($this->get_error_page());
	}
	
	private function get_over_limit_page()
	{
		return <<<OVERLIMITPAGEEOD
		<html>
<head>
<title>Max Onlines Reached</title>
</head>
<body bgcolor="#FFFFFF">
<table cellpadding="0" cellspacing="0" border="0" width="600" align="center" height="85%">
  <tr align="center" valign="middle">
    <td>
    <table cellpadding="10" cellspacing="0" border="0" width="80%" align="center" style="font-family: Verdana, Tahoma; color: #666666; font-size: 9px">
    <tr>
      <td valign="middle" align="center" bgcolor="#EBEBEB">
        <br /><b style="font-size: 10px">Application onlines reached the upper limit</b>
        <br /><br /><br />Sorry, the number of online visitors has reached the upper limit.
        <br />Please wait for someone else going offline or visit system in idle hours.
        <br /><br />
      </td>
    </tr>
    </table>
    </td>
  </tr>
</table>
</body>
</html>
OVERLIMITPAGEEOD;
		
	}
	
	private function get_error_page()
	{
			$timestamp = time();
			if($this->message) {
				$errmsg = "<b>Application Database Error info</b>\n\n<br/>";
			}
			$errmsg .= "<b>Time</b>: ".gmdate("Y-n-j g:ia", $timestamp + ($_SERVER['timeoffset'] * 3600))."\n<br/>";
			if($this->sql) {
				$errmsg .= "<b>SQL</b>: ".htmlspecialchars($this->sql)."\n<br/>";
			}
			$errmsg .= "<b>Error</b>:  {$this->message}\n<br/>";
			$errmsg .= "<b>Errno.</b>:  {$this->code}\n<br/>";
			//$errmsg .= "<b>File: </b>:  {$this->file}\n<br/>";
			//$errmsg .= "<b>Line: </b>:  {$this->line}\n<br/>";
			return $errmsg;
	}
	
}

?>