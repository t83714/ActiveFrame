<?php

class CPagination
{

	private $onePageRecordNumber=15;
	public $start=0;
	public $limit=0;
	
	
	function CPagination($onePageRecordNumber=15)
	{
		$this->onePageRecordNumber=$onePageRecordNumber;
	}
	
	public function getIndexBarHtml($RecordNum)
	{
		$PageIndex=isset($_GET['PageIndex'])?$_GET['PageIndex']:0;
		$PageIndex=$this->validatePageIndex($PageIndex,$RecordNum,$this->onePageRecordNumber);
		$this->getLimitArrayByPage($PageIndex,$this->onePageRecordNumber);
		return $this->IndexBar($PageIndex,$RecordNum,$this->onePageRecordNumber);
	}

	public function getLimitArrayByPage($PageIndex,$OnePageNum=15)
	{
		if($PageIndex==0) $PageIndex++;
		
		$ItemIndex=intval(($PageIndex-1)*$OnePageNum);
		
		$this->start=$ItemIndex;
		$this->limit=$this->onePageRecordNumber;
		return array('from'=>$ItemIndex ,'to' => $OnePageNum);
	}
	/**
	**paging function by jacky.Will validate Page Index
	**/
	private function validatePageIndex($PageIndex,$RecordNum,$OnePageNum=15)
	{
		$PageIndex=intval($PageIndex);
		if($PageIndex<1) $PageIndex=1;
		$PageNum=intval($RecordNum/$OnePageNum);
	  	$PageNum=(($RecordNum%$OnePageNum)===0) ? $PageNum : $PageNum+1;
		$PageIndex=($PageIndex>$PageNum)?$PageNum:$PageIndex;
		return $PageIndex;
	}

	/**
	*
	*generate page bar  by Jacky
	**/
	private function IndexBar($PageIndex,$RecordNum,$OnePageNum=15)
	{
	  $BaseAddr='';
	  
	  $a_style='style="background-color: transparent;text-decoration: none;color: #333333;"';
	  $BaseUri=preg_replace('/&PageIndex=[^&]*/','',$_SERVER["REQUEST_URI"]);
	  $BaseAddr.=$BaseUri.'&PageIndex=';
	  $BaseAddr=str_replace('//','/',$BaseAddr);
	  if($PageIndex==0) $PageIndex++;
	  $PageNum=intval($RecordNum/$OnePageNum);
      $PageNum=(($RecordNum%$OnePageNum)===0) ? $PageNum : $PageNum+1;
	  $PageIndex=($PageIndex>$PageNum)?$PageNum:$PageIndex;
	  $BarText='<table width="99%" cellspacing="0" cellpadding="0" align="center">
			   <tr><td valign="bottom">
			   <table cellspacing="0" cellpadding="0" border="0">
			   <tr><td height="3"></td></tr>
			   <tr>
			   <td><table cellspacing="1" cellpadding="2" style="background: #D6E0EF; border: 1px solid #698CC3">
			   <tr bgcolor="#F8F8F8" class="smalltxt">';
	  $BarText.="<td class=\"header\">&nbsp;{$RecordNum}&nbsp;</td>";
	  $BarText.='<td class="header">&nbsp;'.$PageIndex.'/'.$PageNum.'&nbsp;</td>';
	  $BarText.='<td>&nbsp;<a '.$a_style.' href="'.$BaseAddr.'1"><b>|</b>&lt;&nbsp;</td>';
	  //---finish front index
	  for($i=$PageIndex-4;$i<$PageIndex;$i++)
	  {
		if($i<1) continue;
		$BarText.='<td>&nbsp;<a '.$a_style.' href="'.$BaseAddr.$i.'">'.$i.'</a>&nbsp;</td>';
	  }
	  $BarText.='<td bgcolor="#FFFFFF">&nbsp;<u><b>'.$PageIndex.'</b></u>&nbsp;</td>';
	
	  for($i=$PageIndex+1;$i< $PageIndex+6;$i++)
	  {
		if($i>$PageNum) break;
		$BarText.='<td>&nbsp;<a '.$a_style.' href="'.$BaseAddr.$i.'">'.$i.'</a>&nbsp;</td>';
	  }
	  if($PageIndex==$PageNum)
	  {
		$BarText.='<td>&nbsp;<a '.$a_style.' href="'.$BaseAddr.$PageNum.'">&gt;</a>&nbsp;</td>';
	  }
	  else $BarText.='<td>&nbsp;<a '.$a_style.' href="'.$BaseAddr.($PageIndex+1).'">&gt;</a>&nbsp;</td>';
	  
	  $BarText.='<td>&nbsp;<a '.$a_style.' href="'.$BaseAddr.$PageNum.'">&gt;<b>|</b></a>&nbsp;</td>';
	  $BarText.='<td><center>Jump to<input type="text" name="custompage" size="2" style="border: 1px solid #698CC3" onKeyDown="javascript: if(window.event.keyCode == 13) window.location=\''.$BaseAddr.'\'+this.value;"></center></td></tr></table></td></tr><tr><td height="3"></td></tr></table></td>';
	  $BarText.='</table>';	
	  return $BarText;
	}
	
}