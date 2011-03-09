<?php
class PopUpManager
{
	public static $continue=null;
	public static $title=null;
	
	/*public static function show_message($msg,$title=null,$continue=null)
	{
		loadLib('URLUtil');
		if(!$continue) $continue=self::$continue;
		if(!$title) $title=self::$title;
		
		if($continue) $url=$continue;
		else $url=$_SERVER['REQUEST_URI'];
		$params=array();
		$params['msg']=$msg;
		if($title) $params['msg_title']=$title;
		$url=URLUtil::url_with_extra($url,$params);
		if(URLUtil::is_same_location($url,$_SERVER['REQUEST_URI'])){
			$_GET['msg']=$msg;
			if($title) $_GET['msg_title']=$title;
			return;
		}
		redirect($url,true);
	}*/
	
	public static function show_message($msg,$title=null,$continue=null)
	{
		loadLib('URLUtil');
		if(!$continue) $continue=self::$continue;
		if(!$title) $title=self::$title;
		
		if($continue) $url=$continue;
		else $url=$_SERVER['REQUEST_URI'];
		$params=array();
		$params['msg']=$msg;
		if($title) $params['msg_title']=$title;
		$url=URLUtil::url_with_extra($url,$params);
		if(URLUtil::is_same_location($url,$_SERVER['REQUEST_URI'])){
			$_GET['msg']=$msg;
			if($title) $_GET['msg_title']=$title;
			return;
		}
		redirect($url,true);
	}
	
	public static function alert($msg='',$onclose=null)
	{
		loadLib('URLUtil');
		//$msg=json_encode($msg);
		$uid=mt_rand(0,9999);
		loadview('common_assets/header_popup');
		echo "<div id=\"message_box_ctrl_$uid\" title=\"Alert:\" display=\"none\"><p>
		$msg
		</p></div>
		<script type=\"text/javascript\">
		jQuery(function() {
			jQuery(\"#message_box_ctrl_$uid\").dialog({
				modal: true,
				buttons: {
					Ok: function() {
						jQuery(this).dialog( \"close\");
						$onclose;
					}
				}
			});
		});
		</script>";
		loadview('common_assets/footer_popup');
		exit();
	}
	
}
?>