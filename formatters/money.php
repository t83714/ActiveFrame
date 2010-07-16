<?php
function formatter_money($money,$decimal=2,$showSign=true)
{
	$mStr=sprintf("%01.".$decimal."f", $money);
	$mStr=strrev($mStr);
	$mStr=preg_replace('/(\d\d\d)/','$1,',$mStr);
	$mStr=strrev($mStr);
	if(strpos($mStr,',')===0) $mStr=substr_replace ($mStr,'',0,1);

	$result=$mStr;
	if($showSign===true) $result='$'.$result;
	return $result;
}

function formatter_money_html($money,$decimal=2,$showSign=true,$len=false)
{
	$mStr=sprintf("%01.".$decimal."f", $money);
	$mStr=strrev($mStr);
	$mStr=preg_replace('/(\d\d\d)/','$1,',$mStr);
	$mStr=strrev($mStr);
	if(strpos($mStr,',')===0) $mStr=substr_replace ($mStr,'',0,1);

	if($len!==false && strlen($mStr)>$len) $result='<div style="font:normal normal normal '.$size.'pt Arial; ">'.$mStr.'</font>';
	else $result=$mStr;
	if($showSign===true) $htmlStr='<table width="100%" class="innerTable"><tr><td width="5%" align="left">$</td><td width="95%" align="right">'.$result.'</td></tr></table>';
	else $htmlStr='<span style="width:98%;"><span align="right">'.$result.'</span></span>';
	$result=$htmlStr;

	return $result;
}

?>