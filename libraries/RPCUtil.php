<?php
class RPCUtil
{
	private static $is_header_sent=false;
	
	public static function show_message($msg,$title="Server Cannot Process Your Request")
	{
		header("HTTP/1.0 500 $title");
		echo $msg;
		exit();
	}
	
	public static function var_dump($var)
	{
		header("HTTP/1.0 500 Var Info");
		var_dump($var);
		exit();
	}
	
	public static function print_r($var)
	{
		echo "/*";
		var_dump($var);
		echo "*/";
	}
	
	public static function send_header()
	{
		if(self::$is_header_sent) return;
		self::$is_header_sent=true;
		header('Content-type: text/javascript');
	}
	
	/**
	 * arg1: serialized function
	 * arg2~...: arguments for serialized function 
	 */
	public static function unserialize_function()
	{
		$param_num=func_num_args();
		if(!$param_num) return "throw new Exception('Error@RPCUtil::unserialize_function: no arguments are found!');";
		$params=func_get_args();
		$params[0]=trim($params[0]);
		if(empty($params[0])) return "throw new Exception('Error@RPCUtil::unserialize_function: serialized data is empty!');";
		for($i=0;$i<$param_num;$i++) $params[$i]=json_encode($params[$i]);
		return 'RPCUtil.exec('.join(',',$params).');';
	}
	
	public static function exec()
	{
		self::send_header();
		$params=func_get_args();
		echo call_user_func_array(array('self', 'unserialize_function'),$params);
	}
}

?>