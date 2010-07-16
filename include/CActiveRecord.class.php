<?php
/**
 * @author Jacky Jiang
 * @version 0.1.1
 */

class CActiveRecord implements Iterator, ArrayAccess, Countable
{
	private $updateList=array();
	public $bDeleted=false;

	private $sql='';
	private $table='';
	public $newRecord=false;
	private $record=array();
	private $fieldNameList=array();
	private $db;
	public $tableSet;
	private $idname='';
	private $bIfUpdate=false;
	private $bIfGetInsertId=false;
	public $auto_escape=true;
	
	function __construct($tableSet,$record=null)
	{
		global $APP_ENV;
		$this->db=$APP_ENV['db'];
		if(is_null($record))
		{
			$this->newRecord=true;
			if(!is_string($tableSet)) throw new RuntimeException('ActiveRecord need $tableSet as string when insert mode!');
			$this->table=$tableSet;
			return;
		}
		$this->record=$record;
		$this->tableSet=$tableSet;
		$this->table=key($tableSet);
		$this->idname=$tableSet[$this->table]['primarykeyName'];
		$this->fieldNameList=array_keys($record);	
	}
	
	function __destruct()
	{
		if($this->bIfUpdate===false) $this->bIfUpdate=true;
		else return;
		if($this->bInsert===true && $this->newRecord===true) return;
		$this->sql=$this->getSqlStatement($this->table);
		if(!empty($this->sql)) $this->db->query($this->sql);
	}
	
	public function insert_id()
	{
		if($this->newRecord===false) return false;
		if($this->bIfUpdate===false)
		{
			$this->bIfUpdate=true;
			$this->sql=$this->getSqlStatement($this->table);
			if(!empty($this->sql)) $this->db->query($this->sql);
		}
		if($this->bIfGetInsertId===false)
		{
			$this->id=$this->db->insert_id();
			$this->bIfGetInsertId=true;
		}
		return $this->id;
		
	}
	
	public function rewind() 
	{
	  reset($this->record);
	}

	public function current() 
	{
	  return current($this->record);
	}
	
	public function key() 
	{
	  return key($this->record);
	}
	
	public function next() 
	{
	  return next($this->record);
	}
	
	public function valid() 
	{
	  return ($this->current() !== false);
	}

	public function key_exists($key)
	{
		return key_exists($key,$this->record);
	}
	
	public function array_keys()
	{
		return array_keys($this->record);
	}
	
	public function array_values()
	{
		return array_values($this->record);
	}
	
	
	public function offsetExists($n)
	{
		if(isset($this->record[$n])) return true;
		else return false;
	}
	
	public function offsetGet($n)
	{
		if(isset($this->record[$n])) return $this->record[$n];
		else return NULL;
	}
	
	public function offsetSet($name,$value)
	{
		if($this->newRecord===true)
		{
			
			if(is_null($name)) throw new RuntimeException('Try to set undefined field: null in table:'.$this->table);
			else $this->record[$name]=$value;
			return;
		}
		if( in_array($name,$this->fieldNameList)) 
		{
			$this->record[$name]=$value;
			$this->updateList[$name]=true;
		}
	}
	
	public function offsetUnset($name)
	{
		if($this->newRecord===true)
		{		
			unset($this->record[$name]);
			return;
		}
		if(isset($this->record[$name])) 
		{
			unset($this->record[$name]);
			$this->updateList[$name]=true;
		}
	}
	
	public function count()
	{
		return count($this->record);
	}
	
	private function pad($array,$leftStr,$rightStr=false)
	{
		if($rightStr===false) $rightStr=$leftStr;
		
		foreach($array as $key => $v) $array[$key]=$leftStr.$v.$rightStr;
		return $array;
	}
	
	private function compileSql()
	{
		$this->sql='';
		if($this->newRecord===true)
		{
			$keys=implode('`,`', array_keys($this->record));
			$values=array_values($this->record);
			if($this->auto_escape) foreach($values as $k=>$v) $values[$k]=addslashes($v);
			$values=implode('\',\'', $values);
			$this->sql.="INSERT INTO `{$this->table}` ( `$keys` ) values ( '$values' )";
			return;
		}
		if($this->bDeleted==true)
		{
			$this->sql='DELETE FROM `'.$this->table.'` WHERE `'.$this->idname.'`=\''.$this->record[$this->idname].'\' LIMIT 1';
		}else{
			if(count($this->updateList)==0) return;
			$this->sql='UPDATE `'.$this->table.'` SET ';
			$tempUpdate=array();
			foreach($this->updateList as $name => $v)
			{
				if(!$v) continue;
				if($this->auto_escape) $tempUpdate[]=isset($this->record[$name]) ? " `$name`='".addslashes($this->record[$name])."'" : " `$name`=NULL";
				else 	$tempUpdate[]=isset($this->record[$name]) ? " `$name`='{$this->record[$name]}'" : " `$name`=NULL";
			}
			
			$this->sql.=implode(',',$tempUpdate);
			$this->sql.=' WHERE `'.$this->idname.'`=\''.$this->record[$this->idname].'\' LIMIT 1';
		}
	}
	
	public function getSqlStatement($table)
	{
		$this->table=$table;
		$this->compileSql();
		return $this->sql;
	}

	
	public function delete()
	{
		$this->bDeleted=true;
	}
	
	public function update()
	{
		if($this->bIfUpdate===true) return;
		$this->bIfUpdate=true;
		$this->sql=$this->getSqlStatement($this->table);
		if(!empty($this->sql)) $this->db->query($this->sql);
		if($this->newRecord===true && $this->bIfGetInsertId===false)
		{
			$this->id=$this->db->insert_id();
			$this->bIfGetInsertId=true;
		}
		
	}
}


?>