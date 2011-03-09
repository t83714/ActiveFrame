<?php
/**
 * @author Jacky Jiang
 * @version 1.1.1
 */

class CActiveRecord implements Iterator, ArrayAccess, Countable
{
	private $db;
	
	private $table;
	private $data;
	private $pks;//--- primary keys
	private $fields;
	private $updated_fields=array();
	
	private $is_new_record=false;
	private $has_committed=true;
	private $is_deleted=false;
	
	private $params;//--- $parametters for prepare SQL
	
	function __construct($table,$pks=null,$data=null)
	{
		global $APP_ENV;
		$this->db=$APP_ENV['db'];
		$this->table=trim($table);
		if(empty($table)) throw new RuntimeException('Error@CActiveRecord::__construct: insufficient Parameters!');
		if($data===null) $this->is_new_record=true;
		else if(!is_array($data) || empty($data)) throw new RuntimeException('Error@CActiveRecord::__construct: incorrect raw record data type!');
		else $this->data=$data;
		
		if(is_array($pks) && !empty($pks)) $this->pks=$pks;
		else $this->pks=array('id');
	}
	
	function __destruct()
	{
		$this->commit();
	}
	
	public function get_raw_data()
	{
		return $this->data;
	}
	
	public function insert_id()
	{
		if(!$this->is_new_record) return null;
		$this->commit();
		return $this->db->insert_id();
	}
	
	public function rewind() 
	{
	  reset($this->data);
	}

	public function current() 
	{
	  return current($this->data);
	}
	
	public function key() 
	{
	  return key($this->data);
	}
	
	public function next() 
	{
	  return next($this->data);
	}
	
	public function valid() 
	{
	  return ($this->current() !== false);
	}

	public function key_exists($key)
	{
		return key_exists($key,$this->data);
	}
	
	public function array_keys()
	{
		return array_keys($this->data);
	}
	
	public function array_values()
	{
		return array_values($this->data);
	}
	
	public function count()
	{
		return count($this->data);
	}
	
	public function offsetExists($n)
	{
		if($this->is_deleted) return false;
		if(isset($this->data[$n])) return true;
		else return false;
	}
	
	public function offsetGet($n)
	{
		if($this->is_deleted) return NULL;
		if(isset($this->data[$n])) return $this->data[$n];
		else return NULL;
	}
	
	public function offsetSet($name,$value)
	{
		if($this->is_deleted) return;
		$name=trim($name);
		if($name=='') return;
		if(!$this->is_new_record && !array_key_exists($name,$this->data)) return;
		$this->data[$name]=$value;
		$this->updated_fields[$name]=$name;
		$this->has_committed=false;
	}
	
	public function offsetUnset($name)
	{
		if($this->is_deleted) return;
		$name=trim($name);
		if($name=='') return;
		if($this->is_new_record){
			unset($this->data[$name]);
			return;
		}
		if(isset($this->data[$name])) 
		{
			$this->data[$name]=NULL;
			$this->updated_fields[$name]=$name;
			$this->has_committed=false;
		}
	}
	
	private function get_where()
	{
		$where=" WHERE ";
		$conditions=array();
		foreach($this->pks as $pk) {
			if($this->data[$pk]===NULL) {
				$conditions[]="`".$this->db->real_escape_string($pk)."` IS NULL";
				continue;
			}
			$param_key=':p'.count($this->params);
			$this->params[$param_key]=$this->data[$pk];
			$conditions[]="`".$this->db->real_escape_string($pk)."`=$param_key";
		}
		$where.=implode(' AND ',$conditions);
		return $where;
	}
	
	
	public function commit()
	{
		if($this->has_committed) return;
		$this->params=array();
		if($this->is_deleted){
			$where=$this->get_where();
			$sql="DELETE FROM `{$this->table}` $where LIMIT 1;";
			$this->db->query($sql,$this->params);
			$this->updated_fields=array();
			$this->has_committed=true;
			return;
		}else if($this->is_new_record){
			if(empty($this->updated_fields)){
				$this->updated_fields=array();
				$this->has_committed=true;
				return;
			}
			$keys=array();
			$values=array();
			foreach($this->updated_fields as $u_field)
			{
				$keys[]='`'.$this->db->real_escape_string($u_field).'`';
				if($this->data[$u_field]===NULL) $values[]='NULL';
				else {
					$param_key='@:p'.count($this->params).':';
					$values[]="$param_key";
					$this->params[$param_key]=$this->data[$u_field];
				}
			}
			$keys=implode(',',$keys);
			$values=implode(',',$values);
			$sql="INSERT INTO `{$this->table}` ($keys) VALUES ($values);";
			$this->db->query($sql,$this->params);
			$this->updated_fields=array();
			$this->has_committed=true;
			return;
		}else{
			if(empty($this->updated_fields)){
				$this->updated_fields=array();
				$this->has_committed=true;
				return;
			}
			$changes=array();
			$change='';
			$values=array();
			foreach($this->updated_fields as $u_field)
			{
				$change='`'.$this->db->real_escape_string($u_field).'`';
				if($this->data[$u_field]===NULL) $change.='=NULL';
				else {
					$param_key="@:p".count($this->params).':';
					$change.="=$param_key";
					$this->params[$param_key]=$this->data[$u_field];
				}
				$changes[]=$change;
			}
			$changes=implode(',',$changes);
			$where=$this->get_where();
			$sql="UPDATE `{$this->table}` SET $changes $where LIMIT 1;";
			$this->db->query($sql,$this->params);
			$this->updated_fields=array();
			$this->has_committed=true;
			return;
		}
	}
	

	
	public function delete()
	{
		$this->is_deleted=true;
		$this->has_committed=false;
	}
	
	public function update()
	{
		$this->commit();
	}
	
	
}


?>