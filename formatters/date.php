<?php
function formatter_time2date($time,$format){return date($format,$time);}
function formatter_date2time($date){if(empty($date)) return 0;$date_array=explode('/',$date);return mktime(0,0,0,$date_array[1],$date_array[0],$date_array[2]);}
function formatter_au_date_to_us($date_str) 
{
	$date_array=explode('/',$date_str);
	$date_array=array_reverse($date_array,true);
	return implode('-',$date_array);
}
function formatter_us_date_to_au($date_str) 
{
	$date_array=explode('-',$date_str);
	$date_array=array_reverse($date_array,true);
	return implode('/',$date_array);
}
?>