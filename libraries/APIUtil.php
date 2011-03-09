<?php
class APIUtil
{
	
	public static function report_error($msg,$code=500,$title="Server Cannot Process Your Request")
	{
		header("HTTP/1.0 $code $title");
		echo $msg;
		exit();
	}
	
	public static function var_dump($var)
	{
		header("HTTP/1.0 500 Var Info");
		var_dump($var);
		exit();
	}
	
	public static function output($response)
	{
		echo self::encode_response($response,$_GET['format'],$_GET['callback']);
		exit();
	}
	
	public static function encode_response($response, $type='json',$callback=null)
	{
		if($type=='xml') return self::encode_response_xml($response);
		else if($type=='jsonp') return self::encode_response_jsonp($response,$callback);
		else return self::encode_response_json($response);
	}
	
	private static function encode_response_json($response)
	{
		return json_encode($response);
	}
	
	private static function encode_response_jsonp($response,$callback=null)
	{
		$data=json_encode($response);
		if(!$callback) echo "window.jsonp_callback_data=$callback;";
		return "$callback($callback);";
	}
	
	
	private static function xml_array($xml,$v)
	{
		if(empty($v)) return;
		foreach($v as $key => $value) self::xml_item($xml,$value,$key);
	}
	
	private static function xml_object($xml,$v)
	{
		$v=get_object_vars($v);
		self::xml_array($xml,$v);
	}
	
	private static function xml_item($xml,$v,$name="item")
	{
		if(!is_string($name)) $name='item';
		$xml->startElement($name);
		if(is_array($v)) self::xml_array($xml,$v);
		else if(is_object($v)) self::xml_object($xml,$v);
		else $xml->text((string)$v);
		$xml->endElement();
	}
	
	
	private static function encode_response_xml($response)
	{
		ob_start();
		$xmlWriter = new XMLWriter();
		$xmlWriter->openUri('php://output');
		$xmlWriter->setIndent(true);
		
		if(!$xmlWriter) throw new Exception('Failed to create XML writer!');
		
	  $xmlWriter->startDocument('1.0','UTF-8');
	  
	  self::xml_item($xmlWriter,$response,'response');
	  
	  $xml=ob_get_clean(); 
  	ob_end_clean();
  	return $xml;
	}
	
}

?>