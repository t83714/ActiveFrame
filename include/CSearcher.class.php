<?php
/**
 * @author Jacky Jiang
 * @version 0.1.1
 */


class CSearchOption
{
	public $equal;
	public $min;
	public $max;
	public $like='';
	public $name='';
	public $unequal;
	public $equal_value;
	public $in;

	static function addQuotaSign(&$v,$k){$v="'".addslashes($v)."'";}

	public function getOptionText()
	{
		if(!empty($this->in))
		{
			if(is_array($this->in)) { array_walk($this->in,'CSearchOption::addQuotaSign'); $tempStr=implode(',',$this->in); return " `{$this->name}` IN ($tempStr) ";}
			else return " `{$this->name}` IN ($this->in) ";
		}
		if(isset($this->equal_value)) return "`{$this->name}`={$this->equal_value}";
		if(isset($this->equal)) return "`{$this->name}`='".addslashes($this->equal)."'";
		if(isset($this->unequal)) return "`{$this->name}`!='".addslashes($this->unequal)."'";
		else if(!empty($this->like)) return "`{$this->name}` LIKE '".addslashes($this->like)."'";
		else{
			$str='';
			if(isset($this->min)) $strMin="`{$this->name}`>='{$this->min}'";
			if(isset($this->max)) $strMax=" `{$this->name}`<='{$this->max}'";
			if(!empty($strMin) && !empty($strMax) ) $str.=$strMin.' AND '.$strMax;
			else $str.=$strMin.$strMax;
			return $str;	
		}
	}
	
	
}
class CSearcher implements ArrayAccess
{
	private $sql='';
	private $table='';
	private $db;
	private $searchOption=array();
	private $searchOptionText='';
	private $orSearcherTextArray=array();
	private $orSearcherText='';
	private $andSearcherTextArray=array();
	private $andSearcherText='';
	public $orderBy='';
	public $orderDirection='';
	public $start;
	public $limit;
	private $countSql='';
	private $count='';
	private $records;
	private $tableSet=array();
	private $fetchDataSet='*';
	
	private $bIfCompiled=false;
	
	
	function __construct($table)
	{
		global $APP_ENV;
		$this->db=$APP_ENV['db'];
		$this->table=$table;
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
	
	
	public function offsetExists($name)
	{
		if(isset($this->records)) $this->cleanEnv();
		if(isset($this->searchOption[$name])) return true;
		else return false;
	}
	
	public function offsetGet($name)
	{
		if(isset($this->records)) $this->cleanEnv();
		if(isset($this->searchOption[$name])) return $this->searchOption[$name];
		else {
			$this->searchOption[$name]=new CSearchOption();
			$this->searchOption[$name]->name=$name;
			return $this->searchOption[$name];
		}
	}
	
	public function offsetSet($name,$value)
	{
		if(!isset($value) || !isset($name)) return;
		if(isset($this->records)) $this->cleanEnv();
		if(isset($this->searchOption[$name])) 
		{
			$this->searchOption[$name]->equal=$value;
			$this->searchOption[$name]->name=$name;
		}
		else {
			$this->searchOption[$name]=new CSearchOption();
			$this->searchOption[$name]->equal=$value;
			$this->searchOption[$name]->name=$name;
		}
	}
	
	public function offsetUnset($name)
	{
		if(isset($this->searchOption[$name])) unset($this->searchOption[$name]);
	}
	
	private function cleanEnv()
	{
		$this->sql='';
		$this->searchOption=array();
	 	$this->searchOptionText='';
		$this->orSearcherTextArray=array();
		$this->orSearcherText='';
		$this->andSearcherTextArray=array();
		$this->andSearcherText='';
		$this->orderBy='';
		$this->orderDirection='';
		if(isset($this->start)) unset($this->start);
		if(isset($this->limit)) unset($this->limit);
		$this->countSql='';
		$this->count='';
		if(isset($this->records)) unset($this->records);
		$this->bIfCompiled=false;
		
	}

	function delete()
	{
		if($this->bIfCompiled===false) $this->bIfCompiled=true;
		else return;
		$sql="DELETE FROM `{$this->table}` ";
		$whereOption='';
		$this->getOptionText();
		$this->andSearcherText=implode(' AND ',$this->andSearcherTextArray);
		$this->orSearcherText=implode(' AND ',$this->orSearcherTextArray);
		if(!empty($this->andSearcherText) || !empty($this->andSearcherText))
		{
			$whereOption.='('.$this->searchOptionText.')';
			if(!empty($this->andSearcherText)) $whereOption.=' AND '.$this->andSearcherText;
			if(!empty($this->orSearcherText)) $whereOption.=' OR '.$this->orSearcherText;
		}else $whereOption.=$this->searchOptionText;
		if(!empty($whereOption)) $sql.=' WHERE '.$whereOption;
		$this->db->query($sql);
	}
	
	private function compileSql()
	{
		if($this->bIfCompiled===false) $this->bIfCompiled=true;
		else return;
		$this->sql="SELECT {$this->fetchDataSet} FROM `{$this->table}` ";
		$whereOption='';
		$this->getOptionText();
		$this->andSearcherText=implode(' AND ',$this->andSearcherTextArray);
		$this->orSearcherText=implode(' AND ',$this->orSearcherTextArray);
		if(!empty($this->andSearcherText) || !empty($this->andSearcherText))
		{
			$whereOption.='('.$this->searchOptionText.')';
			if(!empty($this->andSearcherText)) $whereOption.=' AND '.$this->andSearcherText;
			if(!empty($this->orSearcherText)) $whereOption.=' OR '.$this->orSearcherText;
		}else $whereOption.=$this->searchOptionText;
		
		if(!empty($whereOption)) $this->sql.=' WHERE '.$whereOption;
		
		$this->countSql="SELECT COUNT(*) FROM `{$this->table}` ";
		if(!empty($whereOption)) $this->countSql.=' WHERE '.$whereOption;
		
		if(!empty($this->orderBy)) 
		{
			if(!is_array($this->orderBy)) $temp_orderby=str_replace(',','`,`',$this->orderBy);
			else $temp_orderby=implode('`,`',$this->orderBy);
			$this->sql.=" ORDER BY `{$temp_orderby}`";
		}
		if(!empty($this->orderDirection)) 
		{
			if(strtolower($this->orderDirection)=='desc') $this->sql.=" DESC";
			else  $this->sql.=" ASC";
		}
	}
	
	public function setFetchDataSet($dataFields=false)
	{
		if($dataFields===false) $this->fetchDataSet='*';
		else if(is_string($dataFields))  $this->fetchDataSet=$dataFields;
		else if(is_array($dataFields)) $this->fetchDataSet=implode(',',$dataFields);
		else $this->fetchDataSet='*';
	}
	
	public function getTotalRecordsNumber()
	{
		$this->compileSql();
		if(empty($this->countSql)) 
		{
			$this->count=0;
			return 0;
		}
		$query=$this->db->query($this->countSql);
		if($query===false) return false;
		$r=mysql_fetch_array($query,MYSQL_ASSOC);
		if($r===false) return false;
		$this->count=intval($r['COUNT(*)']);
		return $this->count;
	}
	
	private function gettableSetInfo($query)
	{
		
		$num=mysql_num_fields($query);
		$tableSet=array();
		
		for($i=0;$i<$num;$i++)
		{
			mysql_field_seek ($query,$i);
			$obj=mysql_fetch_field($query,$i);
			$tableSet[$obj->table]['collist'][$obj->name]=array(
													'default' => $obj->def,
													'primary_key' => $obj->primary_key);
		}
		foreach($tableSet as $key => $value)
		{
			foreach($value['collist'] as $k => $v)
			{
				if($v['primary_key']==1)
				{
					$tableSet[$key]['primarykeyName']=$k;
				}
				
			}
			if(!isset($tableSet[$key]['primarykeyName'])) throw new RuntimeException('Can\'t load data set without primary key from table:'.$key);
		}
		return $tableSet;
	}
	
	public function fetchResult($arg1=NULL,$arg2=NULL)
	{
		$this->compileSql();
		if(isset($arg2))
		{
			$this->start=$arg1;
			$this->limit=$arg2;
			$this->sql.=" LIMIT {$this->start},{$this->limit}";	
		}elseif (isset($arg1)){
			$this->limit=$arg1;
			$this->sql.=" LIMIT {$this->limit}";
		}
		if(empty($this->sql)) return array();
		//return $this->db->activeRecordQuery($this->sql);
		if(!isset($this->records))
		{
			$query=$this->db->query($this->sql);
			if($query===false) return false;
			$num=mysql_num_fields($query);
			$tableSet=array();
			
			for($i=0;$i<$num;$i++)
			{
				mysql_field_seek ($query,$i);
				$obj=mysql_fetch_field($query,$i);
				$tableSet[$obj->table]['collist'][$obj->name]=array(
														'default' => $obj->def,
														'primary_key' => $obj->primary_key);
			}
			foreach($tableSet as $key => $value)
			{
				foreach($value['collist'] as $k => $v)
				{
					if($v['primary_key']==1)
					{
						$tableSet[$key]['primarykeyName']=$k;
					}
					
				}
				if(!isset($tableSet[$key]['primarykeyName'])) throw new RuntimeException('Can\'t load data set without primary key from table:'.$key);
			}
			$this->tableSet=$tableSet;
			$records=array();
			$this->records=array();
			while($row=mysql_fetch_array($query,MYSQL_ASSOC)) 
			{
				$this->records[]=$row;
				$records[]=new CActiveRecord($tableSet,$row);
			}
			return $records;
		}else{
			$records=array();
			foreach($this->records as $r) $records[]=new CActiveRecord($this->tableSet,$this->records);
			return $records;
		}
	}
	
	public function fetchPlainArray($arg1=NULL,$arg2=NULL)
	{
		$this->compileSql();
		if(isset($arg2))
		{
			$this->start=$arg1;
			$this->limit=$arg2;
			$this->sql.=" LIMIT {$this->start},{$this->limit}";	
		}elseif (isset($arg1))
		{
			$this->limit=$arg1;
			$this->sql.=" LIMIT {$this->limit}";
		}
		if(empty($this->sql)) return array();
		if(!isset($this->records))
		{
			$query=$this->db->query($this->sql);
			if($query===false) return false;
			$records=array();
			while($row=mysql_fetch_array($query,MYSQL_ASSOC)) $records[]=$row;
			$this->records=$records;
			return $records;
		}else return $this->records;
		
	}
	
	
	private function getOptionText()
	{
		$tempArray=array();
		foreach($this->searchOption as $option)
		{
			$option_text=$option->getOptionText();
			if(!empty($option_text)) $tempArray[]=$option_text;
		}
		$this->searchOptionText=implode(' AND ',$tempArray);
		return $this->searchOptionText;
	}
	
	public function _or(CSearcher $os)
	{
		$str=$os->getOptionText();
		if(empty($str)) return;
		$this->orSearcherTextArray[]='('.$str.')';
	}
	
	public function _and(CSearcher $os)
	{
		$str=$os->getOptionText();
		if(empty($str)) return;
		$this->andSearcherTextArray[]='('.$str.')';
	}
	
}




?>