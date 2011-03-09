<?php
class PositionManager
{
	const POS_DISTRIBUTOR=1;
	const POS_COMPANY_GROUP=2;
	const POS_DEALERSHIP=2;
	const POS_COMPANY=3;
	const POS_STORE=4;
	
	public static function is_logged_in()
	{
		return !self::is_anonymous_user();
	}
	
	public static function is_anonymous_user()
	{
		return !is_uuid($_SESSION['people']['id']);
	}

	public static function get_role_id()
	{
		if(is_uuid($_SESSION['position']['role_id'])) return $_SESSION['position']['role_id'];
		$s=new CSearcher('roles');
		$s['default']=1;
		$roles=$s->fetchPlainArray(1);
		if(empty($roles)) throw new CACLException(CACLException::INVALID_ROLE_ID);
		$_SESSION['role']=$roles[0];
		$_SESSION['position']['role_id']=$roles[0]['id'];
		return $roles[0]['id'];
	}
	
	public static function get_default_role()
	{
		$s=new CSearcher('roles');
		$s['default']=1;
		$roles=$s->fetchPlainArray(1);
		return $roles[0];
	}
	
	public static function update_runtime_position($force_update=false)
	{
		if(!$force_update && is_uuid($_SESSION['position']['id'])) return;
		$_SESSION['position']=array();
		$_SESSION['role']=array();
		if(empty($_SESSION['positions']) || !is_array($_SESSION['positions'])) { //--load default position
			$s=new CSearcher('roles');
			$s['default']=1;
			$roles=$s->fetchPlainArray(1);
			if(empty($roles)) throw new Exception('Cannot find default Anonymous User role record!',0);
			$_SESSION['position']['role_id']=$roles[0]['id'];
			$_SESSION['role']=$roles[0];
			return;
		}
		$positions_num=count($_SESSION['positions']);
		if($positions_num==1) {
			$_SESSION['position']=$_SESSION['positions'][0];
			$s=new CSearcher('roles');
			$s['id']=$_SESSION['position']['role_id'];
			$roles=$s->fetchPlainArray(1);
			$_SESSION['role']=$roles[0];
			return;
		}else if($positions_num>1){
			//if(is_uuid($people['last_select_position_id'])) $_SESSION['position']=self::get_position_by_id($people['last_select_position_id']);
			//else $_SESSION['position']=self::get_default_position();
			$_SESSION['position']=self::get_default_position();
			$s=new CSearcher('roles');
			$s['id']=$_SESSION['position']['role_id'];
			$roles=$s->fetchPlainArray(1);
			$_SESSION['role']=$roles[0];
			return;
		}
	}
	
	public static function change_runtime_position($position_id)
	{
		if(!is_uuid($position_id)) throw new Exception("Invalid position ID!");
		$s=new CSearcher('positions');
		$s['people_id']=$_SESSION['people']['id'];
		$s['id']=$position_id;
		$positions=$s->fetchPlainArray(1);
		if(empty($positions)) throw new Exception("Cannot find position record!");
		$_SESSION['position']=$positions[0];
		$s=new CSearcher('roles');
		$s['id']=$_SESSION['position']['role_id'];
		$roles=$s->fetchPlainArray(1);
		$_SESSION['role']=$roles[0];
	}
	
	public static function update_last_select_position_id()
	{
		if(!is_uuid($_SESSION['people']['id']) || !is_uuid($_SESSION['position']['id'])) return;
		$sql="UPDATE `peoples` SET `last_select_position_id`=:last_select_position_id WHERE `id`=:people_id LIMIT 1";
		$params=array(
			':last_select_position_id'=>$_SESSION['position']['id'],
			':people_id'=>$_SESSION['people']['id']
		);
		global $APP_ENV;
		$db=$APP_ENV['db'];
		$db->query($sql,$params);
	}
	
	public static function load_postions()
	{
		if(!is_uuid($_SESSION['people']['id'])) return;
		$s=new CSearcher('positions');
		$s['people_id']=$_SESSION['people']['id'];
		$s->orderBy=array('role_id ASC');
		$positions=$s->fetchPlainArray();
		if(empty($positions)){ //-- load default postion
			$default_role=self::get_default_role();
			if(!empty($default_role)){
				$positions=array(array(
					'id' => null,
					'role_id' => $default_role['id'],
					'people_id' => $_SESSION['people']['id'],
					'default' =>1
				));
			}
		}
		$_SESSION['positions']=$positions;
	}
	
	public static function get_position_by_id($position_id)
	{
		if(empty($_SESSION['positions']) || !is_array($_SESSION['positions'])) return null;
		if(!is_uuid($position_id)) return null;
		foreach($_SESSION['positions'] as $position)
			if($position['id']==$position_id) return $position;
		return null;
	}
	
	public static function get_positions_by_role_id($role_id)
	{
		if(empty($_SESSION['positions']) || !is_array($_SESSION['positions'])) return array();
		if(!is_uuid($role_id)) return array();
		$positions=array();
		foreach($_SESSION['positions'] as $position)
			if($position['role_id']==$role_id) $positions[]=$position;
		return $positions;
	}
	
	public static function get_default_position()
	{
		if(empty($_SESSION['positions']) || !is_array($_SESSION['positions'])) return null;
		
		foreach($_SESSION['positions'] as $position)
			if($position['default']==1) return $position;
		return $_SESSION['positions'][0];
	}
	
	public static function set_default_position($position_id)
	{
		if(!is_uuid($_SESSION['people']['id']) || !is_uuid($position_id)) return;
		global $APP_ENV;
		$db=$APP_ENV['db'];
		$sql="UPDATE `positions` SET `default`='0' WHERE `people_id`=:people_id";
		$params=array(':people_id'=>$_SESSION['people']['id']);
		$db->query($sql,$params);
		
		$sql="UPDATE `positions` SET `default`='1' WHERE `people_id`=:people_id AND `id`=:id LIMIT 1";
		$params=array(':people_id'=>$_SESSION['people']['id'],':id' => $position_id);
		$db->query($sql,$params);
		
		self::load_postions();
		
		if($_SESSION['position']['id']==$position_id) $_SESSION['position']['default']='1';
		else $_SESSION['position']['default']='0';
	}
	
	private static function get_record_name($id,$table)
	{
		if(empty($id)) return null;
		$s=new CSearcher($table);
		$s['id']=$id;
		$s->setFetchDataSet(array('name'));
		$records=$s->fetchPlainArray(1);
		if(empty($records)) return null;
		return $records[0]['name'];
	}
	
	public static function get_position_description($position)
	{
		if(empty($position)) return 'N/A';

		$com_names=array();
		if(is_uuid($position['store_id'])) $com_names[]='Store: '.self::get_record_name($position['store_id'],'stores');
		if(is_uuid($position['company_id'])) $com_names[]='Company: '.self::get_record_name($position['company_id'],'companies');
		if(is_uuid($position['company_group_id'])) $com_names[]='Group: '.self::get_record_name($position['company_group_id'],'company_groups');
		$com_names=join(', ',$com_names);
		return $com_names;

		if(is_uuid($position['dealership_id'])) {
			$d_names=array();
			$d_names[]='Dealership: '.self::get_record_name($position['dealership_id'],'dealerships');
			if(is_uuid($position['company_id'])) $d_names[]='Company: '.self::get_record_name($position['company_id'],'companies');
			$d_names=join(', ',$d_names);
			return $d_names;
		}
		
		if(is_uuid($position['distributor_id'])) {
			$dis_name='Distributor: '.self::get_record_name($position['distributor_id'],'distributors');
			return $dis_name;
		}
		return '';
	}
	
	
}
?>