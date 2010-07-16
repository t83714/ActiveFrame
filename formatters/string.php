<?php
function formatter_cutstrWithHints($str,$length)
{
	if(strlen($str) <= $length) {
		return $str;
	}	
	$shortStr=cutstr($str,$length);
	$output="<span onmouseover='ddrivetip(\"$str\",\"#E6EEF7\");' onmouseout='hideddrivetip()'>$shortStr</span>";
	return $output;
}

function formatter_sprintf($var,$format_str){return sprintf($format_str,$var);}
?>