<?php
function formatter_special_char($str,$xml_mode=true)
{
	if($xml_mode) return str_replace(array("'",'%',"€",'¡ê','?','£¤','¡é','&','<','>'),array('%26apos;','%25','%E2%82%AC','%A3','%E2%82%A3','%A5','%A2','%26','&lt;','&gt;'),$str);
	else return str_replace(array('\'','<','>'),array('%26apos;','&lt;','&gt;'),$str);
}

?>