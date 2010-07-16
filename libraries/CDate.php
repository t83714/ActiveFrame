<?php
class CDate
{
	private $now;
	
	function __construct($now=null)
	{
		if($now==null) $this->now=time();
		else $this->now=$now;
	}
	
	function end_of_month()
	{
		$month=(int)date('n',$this->now);
		if($month==12) return mktime(0,0,0,1,1,date('Y',$this->now)+1)-1;
		else return mktime(0,0,0,$month+1,1,date('Y',$this->now))-1;
	}
	
	function begin_of_month()
	{
		return mktime(0,0,0,date('n',$this->now),1,date('Y',$this->now));
	}
	
	function end_of_last_month()
	{
		return $this->begin_of_month()-1;
	}
	
	function begin_of_last_month()
	{
		$end_of_last_month=$this->end_of_last_month();
		return mktime(0,0,0,date('n',$end_of_last_month),1,date('Y',$end_of_last_month));
	}
	
	function begin_of_next_month()
	{
		return $this->end_of_month()+1;
	}
	
	function end_of_next_month()
	{
		$cs=new CDate($this->begin_of_next_month());
		return $cs->end_of_month();
	}
	
	
	public function last_12_month($n=12)
	{
		$months=array();
		$cd=new CDate($this->now);
		for($i=0;$i<$n;$i++)
		{
			$month=array();
			$month['start']=$cd->begin_of_month();
			$month['end']=$cd->end_of_month();
			$month['year']=date('Y',$month['start']);
			$month['month']=date('n',$month['start']);;
			$month['label']=globallanguagevar('month_'.$month['month']).' '.$month['year'];
			$months[]=$month;
			$cd=new CDate($cd->begin_of_last_month());
		}
		return array_reverse($months);
	}
	
	public function year_month_array()
	{
		$year=(int)date('Y',$this->now);
		$months=array();
		$cd=new CDate(mktime(0,0,0,1,1,$year));
		for($i=0;$i<12;$i++)
		{
			$month=array();
			$month['start']=$cd->begin_of_month();
			$month['end']=$cd->end_of_month();
			$month['year']=date('Y',$month['start']);
			$month['month']=date('n',$month['start']);;
			$month['label']=globallanguagevar('month_'.$month['month']).' '.$month['year'];
			$months[]=$month;
			$cd=new CDate($cd->begin_of_next_month());
		}
		return $months;
	}
	
	
	
	public function begin_of_day()
	{
		return mktime(0,0,0,date('n',$this->now),date('j',$this->now),date('Y',$this->now));
	}
	
	public function end_of_day()
	{
		return mktime(23,59,59,date('n',$this->now),date('j',$this->now),date('Y',$this->now));
	}
	
	public function begin_of_week()
	{
		return $this->begin_of_day()-((date('N',$this->now)-1)*86400);
	}
	
	public function end_of_week()
	{
		return $this->end_of_day()+(7-(date('N',$this->now))*86400);
	}
	
	public function being_of_next_week()
	{
		return $this->end_of_week()+1;
	}
	
	public function end_of_next_week()
	{
		$cd=new CDate($this->being_of_next_week());
		return $cd->end_of_week();
	}
	
	public function begin_of_last_week()
	{
		$cd=new CDate($this->end_of_last_week());
		return $cd->begin_of_week();
	}
	
	public function end_of_last_week()
	{
		return $this->begin_of_week()-1;
	}
	
}
	
?>