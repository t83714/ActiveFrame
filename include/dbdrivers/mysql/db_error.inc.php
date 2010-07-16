<?php


if(!defined('IN_APP')) {
	exit('Access Denied');
}

$timestamp = time();
$errmsg = '';

$dberror = $this->error();
$dberrno = $this->errno();

if($dberrno == 1114) {
ob_clean();
?>
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
<?

	function_exists('appexit') ? appexit() : exit();

} else if($dberrno == 1062){
	throw new RuntimeException($dberror,1062);
}else{
ob_clean();
	if($message) {
		$errmsg = "<b>Application Database Error info</b>: $message\n\n";
	}
	$errmsg .= "<b>Time</b>: ".gmdate("Y-n-j g:ia", $timestamp + ($_SERVER['timeoffset'] * 3600))."\n";
	$errmsg .= "<b>Script</b>: ".$_SERVER['PHP_SELF']."\n\n";
	if($sql) {
		$errmsg .= "<b>SQL</b>: ".htmlspecialchars($sql)."\n";
	}
	$errmsg .= "<b>Error</b>:  $dberror\n";
	$errmsg .= "<b>Errno.</b>:  $dberrno";

	echo "</table></table></table></table></table>\n";
	echo "<p style=\"font-family: Verdana, Tahoma; font-size: 11px; background: #FFFFFF;\">";
	echo nl2br(str_replace($GLOBALS['tablepre'], '[Table]', $errmsg));


	echo '</p>';

	function_exists('appexit') ? appexit() : exit();

}

?>