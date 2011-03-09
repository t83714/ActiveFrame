<?php
class MenuManager
{
	const system_code='0';//--0 for sales accelerator

	private static $params;

	private static $is_loaded=false;
	
	
	private static $top_tabs=array();
	private static $side_tabs=array();
	
	private static $request_path;
	private static $controller_name;
	private static $request_method;
	private static $role_id;
	//private static $is_show=false;
	
	private static function strip_extra_sp($path)
	{
		return preg_replace('/^\/+/','',$path,1);
	}
	
	public static function get_default_url()
	{
		self::init();
		if(empty(self::$top_tabs)) throw new Exception('Can not find default landing menu item for user.',0);
		$top_tab=self::$top_tabs[0];
		$default_action=self::get_default_action($top_tab['id']);
		$default_action_str=$default_action?'/'.$default_action:'';
		$url='?path='.self::strip_extra_sp($top_tab['path'].'/'.$top_tab['controller_name'].$default_action_str).self::get_params_query_string();
		return $url;
	}
	
	private static function get_default_action($module_id)
	{
		$s=new CSearcher('allowed_functions');
		$role_id=PositionManager::get_role_id();
		$s['role_id']=$role_id;
		$s['module_id']=$module_id;
		$s->orderBy='is_menu_item';
		$s->orderByDirection='DESC';
		$functions=$s->fetchPlainArray(1);
		if(empty($functions)) return '';
		else return $functions[0]['method_name'];
	}
	
	public static function set_request_path($request_path)
	{
		if(empty($request_path)) return;
		self::$request_path=$request_path;
		self::$is_loaded=false;
	}
	
	public static function get_request_path()
	{
		global $APP_ENV;
		if(empty(self::$request_path)) return $APP_ENV['requestPath'];
		return self::$request_path;
	}
	
	public static function set_controller_name($controller_name)
	{
		if(empty($controller_name)) return;
		self::$controller_name=$controller_name;
		self::$is_loaded=false;
	}
	
	public static function get_controller_name()
	{
		global $APP_ENV;
		if(empty(self::$controller_name)) return $APP_ENV['controllerName'];
		return self::$controller_name;
	}
	
	public static function set_request_method($request_method)
	{
		if(empty($request_method)) return;
		self::$request_method=$request_method;
		self::$is_loaded=false;
	}
	
	public static function get_request_method()
	{
		global $APP_ENV;
		if(empty(self::$request_method)) return $APP_ENV['requestMethod'];
		return self::$request_method;
	}
	
	private static function get_params_query_string()
	{
		if(empty(self::$params)) return '';
		$tmp=array();
		foreach (self::$params as $key=>$param) $tmp[]=$key.'='.urlencode($param);
		$tmp=implode('&',$tmp);
		if(empty($tmp)) return '';
		return '&'.$tmp;
	}
	
	
	public static function get_top_tabs()
	{
		self::init();
		return self::$top_tabs;
	}
	
	public static function get_side_tabs()
	{
		self::init();
		return self::$side_tabs;
	}
	
	public static function get_breadcrumbs($class=null,$without_tags=false)
	{
		$class=$class?"class=\"$class\"":'';
		$request_path=self::get_request_path();
		$controller_name=self::get_controller_name();
		$request_method=self::get_request_method();
		$role_id=PositionManager::get_role_id();
		
		$title=array();
		
		$s=new CSearcher('allowed_modules');
		$s['path']=$request_path;
		$s['controller_name']=$controller_name;
		$s['role_id']=$role_id;
		$s->setFetchDataSet(array('name','id'));
		$s->orderBy='order';
		$s->orderDirection='DESC';
		$modules=$s->fetchPlainArray(1);
		if(!empty($modules)) {
			$module=$modules[0];
			$title[0]=$module['name'];
			
			$s=new CSearcher('allowed_functions');
			$s['method_name']=$request_method;
			$s['role_id']=$role_id;
			$s['module_id']=$module['id'];
			$s->setFetchDataSet(array('name','id'));
			$s->orderBy='order';
			$s->orderDirection='DESC';
			$functions=$s->fetchPlainArray(1);
			if(!empty($functions)) $title[1]=$functions[0]['name'];
		}
		
		$top_tabs=self::get_top_tabs();
		$side_tabs=self::get_side_tabs();
		
		$cur_top_tab=null;
		foreach ($top_tabs as $tab)
			if($tab['selected']) {
				$title[0]=$tab['name'];
				$cur_top_tab=$tab;
				break;
			}
		if($cur_top_tab){
			$request_method=self::get_request_method();
			$s=new CSearcher('allowed_functions');
			$s['module_id']=$cur_top_tab['id'];
			$s['method_name']=$request_method;
			$s->setFetchDataSet(array('name'));
			$functions=$s->fetchPlainArray(1);
			if(!empty($functions)) $title[1]=$functions[0]['name'];
		}
		$title=implode(' / ',$title);
		if($without_tags) return $title;
		else return "<h1 $class>$title</h1>";
	}
	
	public static function get_side_tabs_number()
	{
		self::init();
		if(empty(self::$side_tabs) || !is_array(self::$side_tabs)) return 0;
		return count(self::$side_tabs);
	}

	
	private static function get_side_tabs_by_params($module_id,$role_id)
	{
		$s=new CSearcher('side_tabs');
		$s['role_id']=$role_id;
		$s['module_id']=$module_id;
		$records=$s->fetchPlainArray();
		if(empty($records)) return array();
		$request_path=self::get_request_path();
		$controller_name=self::get_controller_name();
		$request_method=self::get_request_method();
		foreach ($records as &$tab){
			if($tab['path']==$request_path && $tab['controller_name']==$controller_name && $tab['method_name']==$request_method) $tab['selected']=true;
			$tab['url']='?path='.self::strip_extra_sp($tab['path'].'/'.$tab['controller_name'].'/'.$tab['method_name']).self::get_params_query_string();
		}
		return $records;
	}
	
	private static function init()
	{
		if(self::$is_loaded) return;
		self::$is_loaded=true;
		
		self::clean();
		
		$request_path=self::get_request_path();
		$controller_name=self::get_controller_name();
		$request_method=self::get_request_method();
		$role_id=PositionManager::get_role_id();
		$s=new CSearcher('top_tabs');
		$s['role_id']=$role_id;
		$top_tabs=$s->fetchPlainArray();
		if(!empty($top_tabs)){
			foreach ($top_tabs as &$tab)
			{
				$side_tabs=self::get_side_tabs_by_params($tab['id'],$role_id);
				if(empty($side_tabs)) {
					$default_action=self::get_default_action($tab['id']);
					$default_action_str=$default_action?'/'.$default_action:'';
					$tab['url']='?path='.self::strip_extra_sp($tab['path'].'/'.$tab['controller_name'].$default_action_str).self::get_params_query_string();
				}else $tab['url']='?path='.self::strip_extra_sp($tab['path'].'/'.$tab['controller_name'].'/'.$side_tabs[0]['method_name']).self::get_params_query_string();
				if($tab['path']==$request_path && $tab['controller_name']==$controller_name){
					$tab['selected']=true;
					self::$side_tabs=$side_tabs;
				}
				$tab['side_tabs']=$side_tabs;
			}
			self::$top_tabs=$top_tabs;
		}
	}
	

	
	private static function clean()
	{
		self::$top_tabs=array();
		self::$side_tabs=array();
	}
	
	
	public static function set_params($params)
	{
		if(empty($params)) return;
		self::$is_loaded=false;
		self::$params=$params;
	}
	
	public static function add_param($name,$value)
	{
		if(empty($name)) return;
		self::$is_loaded=false;
		if(!is_array(self::$params)) self::$params=array($name=>$value);
		else self::$params[]=array($name=>$value);
	}
}
?>