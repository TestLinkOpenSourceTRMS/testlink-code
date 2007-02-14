<?php
// charts.php v4.5
// ------------------------------------------------------------------------
// Copyright (c) 2003-2007, maani.us
// ------------------------------------------------------------------------
// This file is part of "PHP/SWF Charts"
//
// PHP/SWF Charts is a shareware. See http://www.maani.us/charts/ for
// more information.
// ------------------------------------------------------------------------

//====================================
function InsertChart( $flash_file, $library_path, $php_source, $width=400, $height=250, $bg_color="666666", $transparent=false, $license=null ){
	
	$php_source=urlencode($php_source);
	$library_path=urlencode($library_path);

	$html="<OBJECT classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0' ";
	$html.="WIDTH=".$width." HEIGHT=".$height." id='charts' ALIGN=''>";
	$u=(strpos ($flash_file,"?")===false)? "?" : ((substr($flash_file, -1)==="&")? "":"&");
	$html.="<PARAM NAME=movie VALUE='".$flash_file.$u."library_path=".$library_path."&php_source=".$php_source;
	if($license!=null){$html.="&license=".$license;}
	$html.="'> <PARAM NAME=quality VALUE=high> <PARAM NAME=bgcolor VALUE=#".$bg_color."> ";
	if($transparent){$html.="<PARAM NAME=wmode VALUE=transparent> ";}
	$html.="<EMBED src='".$flash_file.$u."library_path=".$library_path."&php_source=".$php_source;
	if($license!=null){$html.="&license=".$license;}
	$html.="' quality=high bgcolor=#".$bg_color." WIDTH=".$width." HEIGHT=".$height." NAME='charts' ALIGN='' swLiveConnect='true' ";
	if($transparent){$html.="wmode=transparent ";}
	$html.="TYPE='application/x-shockwave-flash' PLUGINSPAGE='http://www.macromedia.com/go/getflashplayer'></EMBED></OBJECT>";
	return $html;
	
}

//====================================
function SendChartData( $chart=array() ){
	
	$xml="<chart>\r\n";
	$Keys1= array_keys((array) $chart);
	for ($i1=0;$i1<count($Keys1);$i1++){
		if(is_array($chart[$Keys1[$i1]])){
			$Keys2=array_keys($chart[$Keys1[$i1]]);
			if(is_array($chart[$Keys1[$i1]][$Keys2[0]])){
				$xml.="\t<".$Keys1[$i1].">\r\n";
				for($i2=0;$i2<count($Keys2);$i2++){
					$Keys3=array_keys((array) $chart[$Keys1[$i1]][$Keys2[$i2]]);
					switch($Keys1[$i1]){
						case "chart_data":
						$xml.="\t\t<row>\r\n";
						for($i3=0;$i3<count($Keys3);$i3++){
							switch(true){
								case ($chart[$Keys1[$i1]][$Keys2[$i2]][$Keys3[$i3]]===null):
								$xml.="\t\t\t<null/>\r\n";
								break;
								
								case ($Keys2[$i2]>0 and $Keys3[$i3]>0):
								$xml.="\t\t\t<number>".$chart[$Keys1[$i1]][$Keys2[$i2]][$Keys3[$i3]]."</number>\r\n";
								break;
								
								default:
								$xml.="\t\t\t<string>".$chart[$Keys1[$i1]][$Keys2[$i2]][$Keys3[$i3]]."</string>\r\n";
								break;
							}
						}
						$xml.="\t\t</row>\r\n";
						break;
						
						case "chart_value_text":
						$xml.="\t\t<row>\r\n";
						$count=0;
						for($i3=0;$i3<count($Keys3);$i3++){
							if($chart[$Keys1[$i1]][$Keys2[$i2]][$Keys3[$i3]]===null){$xml.="\t\t\t<null/>\r\n";}
							else{$xml.="\t\t\t<string>".$chart[$Keys1[$i1]][$Keys2[$i2]][$Keys3[$i3]]."</string>\r\n";}
						}
						$xml.="\t\t</row>\r\n";
						break;
						
						/*case "link_data_text":
						$xml.="\t\t<row>\r\n";
						$count=0;
						for($i3=0;$i3<count($Keys3);$i3++){
							if($chart[$Keys1[$i1]][$Keys2[$i2]][$Keys3[$i3]]===null){$xml.="\t\t\t<null/>\r\n";}
							else{$xml.="\t\t\t<string>".$chart[$Keys1[$i1]][$Keys2[$i2]][$Keys3[$i3]]."</string>\r\n";}
						}
						$xml.="\t\t</row>\r\n";
						break;*/
						
						case "draw":
						$text="";
						$xml.="\t\t<".$chart[$Keys1[$i1]][$Keys2[$i2]]['type'];
						for($i3=0;$i3<count($Keys3);$i3++){
							if($Keys3[$i3]!="type"){
								if($Keys3[$i3]=="text"){$text=$chart[$Keys1[$i1]][$Keys2[$i2]][$Keys3[$i3]];}
								else{$xml.=" ".$Keys3[$i3]."=\"".$chart[$Keys1[$i1]][$Keys2[$i2]][$Keys3[$i3]]."\"";}
							}
						}
						if($text!=""){$xml.=">".$text."</text>\r\n";}
						else{$xml.=" />\r\n";}
						break;
						
						
						default://link, etc.
						$xml.="\t\t<value";
						for($i3=0;$i3<count($Keys3);$i3++){
							$xml.=" ".$Keys3[$i3]."=\"".$chart[$Keys1[$i1]][$Keys2[$i2]][$Keys3[$i3]]."\"";
						}
						$xml.=" />\r\n";
						break;
					}
				}
				$xml.="\t</".$Keys1[$i1].">\r\n";
			}else{
				if($Keys1[$i1]=="chart_type" or $Keys1[$i1]=="series_color" or $Keys1[$i1]=="series_image" or $Keys1[$i1]=="series_explode" or $Keys1[$i1]=="axis_value_text"){							
					$xml.="\t<".$Keys1[$i1].">\r\n";
					for($i2=0;$i2<count($Keys2);$i2++){
						if($chart[$Keys1[$i1]][$Keys2[$i2]]===null){$xml.="\t\t<null/>\r\n";}
						else{$xml.="\t\t<value>".$chart[$Keys1[$i1]][$Keys2[$i2]]."</value>\r\n";}
					}
					$xml.="\t</".$Keys1[$i1].">\r\n";
				}else{//axis_category, etc.
					$xml.="\t<".$Keys1[$i1];
					for($i2=0;$i2<count($Keys2);$i2++){
						$xml.=" ".$Keys2[$i2]."=\"".$chart[$Keys1[$i1]][$Keys2[$i2]]."\"";
					}
					$xml.=" />\r\n";
				}
			}
		}else{//chart type, etc.
			$xml.="\t<".$Keys1[$i1].">".$chart[$Keys1[$i1]]."</".$Keys1[$i1].">\r\n";
		}
	}
	$xml.="</chart>\r\n";
	echo $xml;
}
//====================================
?>
