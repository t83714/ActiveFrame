<?php
function loadXmlChart($chartSWF, $strURL, $chartId, $cWidth, $cHeight, $debugMode=0, $registerWithJS=0,$var_name='') 
{
	parse_str($_SERVER['QUERY_STRING'],$req);
	if(isset($req['path'])) unset($req['path']);
	$bChanged=false;
	foreach($_GET AS $key => $value) if(!array_key_exists($key,$req) && $key!='path') { $req[$key]=$value; $bChanged=true; }
	$request_data=http_build_query($req);
	if(empty($var_name)) $var_name=$chartId.'_obj';
	//strURL = appendUrlRequest(strURL)
	$render_chart= 
	'<div id=\'chart_div_'.$chartId.'\'></div><script type="text/javascript">
	var strURL=\''.$APP_ENV['baseUrl'].'?path='.$strURL.'\';
	strURL = strURL + "&currTime=" + getTimeForURL();'.
	"strURL+='&$request_data';".
	'strURL = escape(strURL);
	var '.$var_name.' = new FusionCharts("'.$APP_ENV['baseUrl'].'charts/'.$chartSWF.'.swf", "'.$chartId.'", "'.$cWidth.'", "'.$cHeight.'", "'.$debugMode.'", "'.$registerWithJS.'");
	'.$var_name.'.setDataURL(strURL);
	'.$var_name.'.render("chart_div_'.$chartId.'");';
	$render_chart.='</script>';
	return $render_chart;
}

function loadChartWidget($title,$chartSWF, $strURL, $chartId, $cWidth, $cHeight, $debugMode=false, $registerWithJS=false,$inline=false,$withToolsBar=false)
{
	$chartInfo=loadXmlChart($chartSWF, $strURL, $chartId, $cWidth, $cHeight, $debugMode, $registerWithJS);
	$inline=($inline)?'style="display:inline;"':'';
	$var_name=$chartId.'_obj';
	$data['var_name']=$var_name;
	$data['withToolsBar']=$withToolsBar;
	$data['title']=$title;
	$data['chartId']=$chartId;
	$data['cWidth']=$cWidth;
	$data['cHeight']=$cHeight;
	$data['inline']=$inline;
	$data['chartInfo']=$chartInfo;
	return loadview('common_assets/dashboard_charts_common',$data,true);
}

function render_chart_by_xml($xml,$type,$w,$h,$debug=0,$registerWithJS=0)
{
	$uiq=md5(uniqid(rand(), true));
	$xml=str_replace(array("\n","\r"),'',$xml);
	$chart="<div id='chart_$uiq'>You need to enable javascript to see the dashboard</div>";
	$chart.="<script type='text/javascript'>
	var chart_obj_$uiq=new FusionCharts('charts/$type.swf', 'chart_id_$uiq', '$w', '$h','$debug', '$registerWithJS');
	chart_obj_$uiq.setDataXML(\"$xml\");
	chart_obj_$uiq.render('chart_$uiq');
	</script>";
	return $chart;
}

?>