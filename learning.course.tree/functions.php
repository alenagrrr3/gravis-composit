<?
if (!function_exists("_IsItemSelected"))
{
	function _IsItemSelected($arLinks = Array())
	{
		if (!is_array($arLinks))
			$arLinks = Array($arLinks);

		$cur_page = $GLOBALS["APPLICATION"]->GetCurPage();
		$cur_dir = $GLOBALS["APPLICATION"]->GetCurDir();
		$selected = false;

		foreach($arLinks as $tested_link)
		{
			if (strlen($tested_link) <= 0)
				continue;

			$tested_link = trim(Rel2Abs($cur_dir, $tested_link));

			if(strpos($cur_page, $tested_link)===0)
			{
				$selected = true;
				break;
			}
			if(($pos = strpos($tested_link, "?"))!==false)
			{
				
				if(substr($tested_link, 0, $pos)==$cur_page)
				{
					$params = explode("&", substr($tested_link, $pos+1));
					$bOK = true;
					foreach($params as $param)
					{
						$eqpos=strpos($param, "=");
						$varvalue="";
						if($eqpos===false)
							$varname = $param;
						elseif($eqpos==0)
							continue;
						else
						{
							$varname = substr($param, 0, $eqpos);
							$varvalue = urldecode(substr($param, $eqpos+1));
						}

						$globvarvalue = isset($GLOBALS[$varname])?$GLOBALS[$varname]:"";

						if($globvarvalue != $varvalue)
						{
							$bOK = false;
							break;
						}
					}

					if($bOK)
					{
						$selected = true;
						break;
					}
				}
			} 
		}
		return $selected;
	}
}

if (!function_exists("_IsInsideSelect"))
{
	function _IsInsideSelect(&$arItems, $itemIndex, $depth_level)
	{
		for ($size = count($arItems); $itemIndex < $size; $itemIndex++)
		{
			if ($arItems[$itemIndex]["DEPTH_LEVEL"] <= $depth_level)
				return false;

			if ($arItems[$itemIndex]["SELECTED"])
				return true;
		}
		return false;
	}
}
?>