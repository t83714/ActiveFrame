<?php
class CDate
{
	private $now;
	const TODAY='today';
	const TOMORROW='tomorrow';
	const THIS_WEEK='this_week';
	const NEXT_WEEK='next_week';
	const LATER='later';
	const SPECIFY='specify';
	
	function __construct($now=null)
	{
		if($now==null) $this->now=time();
		else $this->now=$now;
	}
	
	public function __toString()
	{
		return date('Y-m-d H:i:s',$this->now);
	}
	
	function get_time()
	{
		return $this->now;
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
	
	public function begin_of_next_day()
	{
		return $this->end_of_day()+1;
	}
	
	public function end_of_next_day()
	{
		$cd=new CDate($this->begin_of_next_day());
		return $cd->end_of_day();
	}
	
	public function begin_of_last_day()
	{
		return $this->end_of_day()+1;
	}
	
	public function end_of_last_day()
	{
		return $this->begin_of_day()-1;
	}
	
	public function begin_of_week()
	{
		$cd=new CDate($this->end_of_last_day());
		return $cd->begin_of_day();
	}
	
	public function end_of_week()
	{
		return $this->end_of_day()+(7-date('N',$this->now))*86400;
	}
	
	public function begin_of_next_week()
	{
		return $this->end_of_week()+1;
	}
	
	public function end_of_next_week()
	{
		$cd=new CDate($this->begin_of_next_week());
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
	
	public function working_days_of_month()
	{
		$month=(int)date('n',$this->now);
		$day=(int)date('j',$this->now);
		$year=(int)date('Y',$this->now);
		$days=(int)date('t',mktime(0,0,0,$month,$day,$year));
		$workingDays=0;
		for($i=0;$i<$days;$i++)
		{
			if(date('N',mktime(0,0,0,$month,$i+1,$year))<6) $workingDays++;
		}
		return $workingDays;
	}
	
	public function current_working_day_month()
	{
		$month=(int)date('n',$this->now);
		$year=(int)date('Y',$this->now);
		$day=(int)date('j',$this->now);
		$days=(int)date('t',mktime(0,0,0,$month,$day,$year));
		$today=(int)date('j',mktime(0,0,0,$month,$day,$year));

		$currentDay=0;

		for($i=1;$i<=$today;$i++)
		{
			if(date('N',mktime(0,0,0,$month,$i,$year))<6) $currentDay++;
		}

		return $currentDay;
	}
	
	/**
	 * short cut for making time
	 *
	 * @param boolean $due if true, will return the end of the date or the start of the date
	 * @param string $date_type see CDate const
	 * @param string $date YYYY-MM-DD
	 * @param string $time_hour HH
	 * @param string $time_min mm
	 * @param string $time_clock AM or PM
	 */
	public function make_time($due,$date_type,$date=null,$time_hour=null,$time_min=null,$time_clock=null)
	{
		switch ($date_type)
		{
			case self::TODAY : return $due?$this->end_of_day():$this->begin_of_day();
			case self::TOMORROW : return $due?$this->end_of_next_day():$this->begin_of_next_day();
			case self::THIS_WEEK : return $due?$this->end_of_week():$this->begin_of_week();
			case self::NEXT_WEEK : return $due?$this->end_of_next_week():$this->begin_of_next_week();
			case self::LATER : return null;
			case self::SPECIFY : $time=strtotime("$date $time_hour:$time_min:00 $time_clock");
								if($time===false) return null;
								else return $time;
			default: return null;
		}
	}
	
    public function make_due_time($date_type,$date=null,$time_hour=null,$time_min=null,$time_clock=null)
    {
    	return $this->make_time(true,$date_type,$date,$time_hour,$time_min,$time_clock);
    }
    
    public function make_start_time($date_type,$date=null,$time_hour=null,$time_min=null,$time_clock=null)
    {
    	return $this->make_time(false,$date_type,$date,$time_hour,$time_min,$time_clock);
    }
	
}
	
?>