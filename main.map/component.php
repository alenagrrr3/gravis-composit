<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$GLOBALS["arrMainMenu"] = explode(",",COption::GetOptionString("main","map_top_menu_type","top"));
$GLOBALS["arrChildMenu"] = explode(",",COption::GetOptionString("main","map_left_menu_type","left"));
$GLOBALS["arrSearchPath"] = array();

$arParams["LEVEL"] = intval($arParams["LEVEL"]);
$arParams["COL_NUM"] = intval($arParams["COL_NUM"]);
if ($arParams["LEVEL"] < 0) $arParams["LEVEL"] = 0;
if ($arParams["COL_NUM"] <= 0) $arParams["COL_NUM"] = 1;

if (!is_set($arParams, "CACHE_TIME")) $arParams["CACHE_TIME"] = "14400";

$arParams["SHOW_DESCRIPTION"] = $arParams["SHOW_DESCRIPTION"] == "N" ? "N" : "Y";

if (!function_exists('GetTree'))
{
	function GetTree($dir, $max_depth, $get_description = false)
	{
		$arMap = GetTreeRecursive($dir, 0, $max_depth, $get_description);

		return $arMap;
	}
}

if (!function_exists('GetTreeRecursive'))
{
	function GetTreeRecursive($PARENT_PATH, $level, $max_depth, $get_description = false)
	{
		global $arrMainMenu, $arrChildMenu, $arrSearchPath, $APPLICATION;

		$i = 0;
		
		$arrMenu = $level == 0 ? $arrMainMenu : $arrChildMenu;
		
		$map = array();	
		
		if(is_array($arrMenu) && count($arrMenu)>0)
		{
			foreach($arrMenu as $mmenu)
			{
				$menu_file = ".".trim($mmenu).".menu.php";
				$menu_file_ext = ".".trim($mmenu).".menu_ext.php";				
				
				$aMenuLinks = array();
				
				if(file_exists($PARENT_PATH.$menu_file))
				{
					include($PARENT_PATH.$menu_file);
					$bExists = true;
				}
					
				if(file_exists($PARENT_PATH.$menu_file_ext))
				{
					include($PARENT_PATH.$menu_file_ext);
					$bExists = true;
				}					
				
				if ($bExists && is_array($aMenuLinks))
				{
					foreach ($aMenuLinks as $aMenu)
					{
						if (strlen($aMenu[0]) <= 0) continue;
						if(count($aMenu)>4)
						{
							$CONDITION = $aMenu[4];
							if(strlen($CONDITION)>0 && (!@eval("return ".$CONDITION.";")))
								continue;
						}
						
						if (strlen($aMenu[1])>0)
						{
							$search_child = true;
							
							if(preg_match("'^(([A-Za-z]+://)|mailto:|javascript:)'i", $aMenu[1]))
							{
								$full_path = $aMenu[1];
								$search_child = false;
							}
							else
							{
								$full_path = trim(Rel2Abs(substr($PARENT_PATH, strlen($_SERVER["DOCUMENT_ROOT"])), $aMenu[1]));

								$slash_pos = strrpos($full_path, "/");
								if ($slash_pos === false) 
								{
									$search_child = false;
								}
								else
								{
									$search_path = substr($full_path, 0, $slash_pos+1);
								}
							}
						}
						else
						{
							$search_child = false;
							$full_path = $PARENT_PATH;
						}
						
						if (strlen($full_path)>0)
						{
							$FILE_ACCESS = (preg_match("'^(([A-Za-z]+://)|mailto:|javascript:)'i", $full_path)) ? "R" : $APPLICATION->GetFileAccessPermission($full_path);
							
							if ($FILE_ACCESS!="D" && $aMenu[3]["SEPARATOR"]!="Y")
							{
								$is_dir = (is_dir($_SERVER["DOCUMENT_ROOT"].$search_path)) ? "Y" : "N";
								if ($is_dir=="Y")
								{
									$search_child &= $level < $max_depth;
									$search_child &= !in_array($search_path, $arrSearchPath);
								}
								else
								{
									$search_child = false;
								}
								
								$ar = array();
								$ar["ID"] = md5($full_path.$ar["COUNTER"]);
								$ar["LEVEL"] = $level;
								$ar["IS_DIR"] = is_dir($_SERVER["DOCUMENT_ROOT"].$full_path) ? "Y" : "N";
								$ar["NAME"] = $aMenu[0];
								$ar["PATH"] = $PARENT_PATH;
								$ar["FULL_PATH"] = $full_path;
								$ar["SEARCH_PATH"] = $search_path;
								$ar["DESCRIPTION"] = "";
								
								if ($get_description && $ar["IS_DIR"] == "Y")
								{
									if (file_exists($_SERVER["DOCUMENT_ROOT"].$full_path.".section.php"))
									{
										$arDirProperties = array();
										include($_SERVER["DOCUMENT_ROOT"].$full_path.".section.php");
										if($arDirProperties["description"] <> '')
											$ar["DESCRIPTION"] = $arDirProperties["description"];
									}
								}
								
								if ($search_child)
								{
									$arrSearchPath[] = $search_path;
									$ar["CHILDREN"] = GetTreeRecursive($_SERVER["DOCUMENT_ROOT"].$ar["SEARCH_PATH"], $level+1, $max_depth, $get_description);
								}
								
								$map[] = $ar;
							}
						}
					}
				}
			}
		}
		
		return $map;
	}
}

if (!function_exists('CreateMapStructure'))
{
	function CreateMapStructure($arMap)
	{
		$arReturn = array();
		
		foreach ($arMap as $key => $arMapItem)
		{
			$arChildrenItems = $arMapItem["CHILDREN"];
			unset($arMapItem["CHILDREN"]);
			
			$arMapItem["STRUCT_KEY"] = $key;
			
			$arReturn[] = $arMapItem;
			if (is_array($arChildrenItems) && count($arChildrenItems) > 0)
			{
				$arChildren = CreateMapStructure($arChildrenItems);
				$arReturn = array_merge($arReturn, $arChildren);
			}
		}

		return $arReturn;
	}
}

$additionalCacheID = $USER->GetGroups();

if ($this->StartResultCache(false, $additionalCacheID))
{
	$sl = @CLang::GetList();
	while ($slr = $sl->Fetch())
	{
		if ($slr["LID"] == LANG)
		{
			$lang_dir = $slr["DIR"];
			break;
		}
	}

	$arResult["arMapStruct"] = GetTree($_SERVER["DOCUMENT_ROOT"].$lang_dir, $arParams["LEVEL"], $arParams["SHOW_DESCRIPTION"] == "Y");
	
	//echo "<pre>"; print_r($arResult["arMapStruct"]); echo "</pre>";
	
	$arResult["arMap"] = CreateMapStructure($arResult["arMapStruct"]);
	
	$this->IncludeComponentTemplate();	
}


?>