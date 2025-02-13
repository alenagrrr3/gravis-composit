<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (!CModule::IncludeModule("search")):
	ShowError(GetMessage("F_NO_SEARCH_MODULE"));
	return 0;
endif;
if (!function_exists("__array_merge"))
{
	function __array_merge($arr1, $arr2)
	{
		$arResult = $arr1;
		foreach ($arr2 as $key2 => $val2)
		{
			if (!array_key_exists($key2, $arResult))
			{
				$arResult[$key2] = $val2;
				continue;
			}
			elseif ($val2 == $arResult[$key2])
				continue;
			elseif (!is_array($arResult[$key2]))
				$arResult[$key2] = array($arResult[$key2]);
			$arResult[$key2] = __array_merge($arResult[$key2], $val2);
		}
		return $arResult;
	}
}
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$q = trim($_REQUEST["q"]);
	$arResult["q"] = htmlspecialcharsEx($q);
	$arParams["FID"] = (!empty($_REQUEST["FID"]) ? $_REQUEST["FID"] : $_REQUEST["FORUM_ID"]);
	$arParams["FID"] = (!empty($arParams["FID"]) ? $arParams["FID"] : $_REQUEST["find_forum"]);
	$arParams["FID"] = is_array($arParams["FID"]) ? $arParams["FID"] : array($arParams["FID"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "",
		"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
		"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["TOPICS_PER_PAGE"] = intVal(intVal($arParams["TOPICS_PER_PAGE"]) > 0 ? $arParams["TOPICS_PER_PAGE"] : COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
$arResult["FID"] = (is_array($arParams["FID"]) ? $arParams["FID"] : array($arParams["FID"]));
$arResult["ERROR_MESSAGE"] = "";
$arResult["SHOW_FORUMS"] = "Y";
$arResult["SHOW_RESULT"] = (strLen($q) > 0 || !empty($_REQUEST["tags"]) ? "Y" : "N");
$arResult["FORUMS"] = array();
$arResult["GROUPS_FORUMS"] = array(); // declared in result_modifier.php
$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
$arResult["order"] = array("active" => "relevance");
$aSort = array("RANK"=>"DESC", "DATE_CHANGE"=>"DESC");
$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");
$arResult["URL"] = array(
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()), 
	"~INDEX" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_INDEX"], array()));
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** Forums *********************************************/
$arFilter = array("INDEXATION" => "Y");
$arForums = array();
$arGroups = array();
if ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N" || !$USER->IsAdmin())
	$arFilter["LID"] = SITE_ID;
if (!empty($arParams["FID_RANGE"]))
	$arFilter["@ID"] = $arParams["FID_RANGE"];
if (!$USER->IsAdmin()):
	$arFilter["PERMS"] = array($USER->GetGroups(), 'A'); 
	$arFilter["ACTIVE"] = "Y";
endif;

$cache_id = "forum_forums_".serialize($arFilter);
$cache_path = $cache_path_main."forums";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	$arForums = $res["arForums"];
}
$arForums = (is_array($arForums) ? $arForums : array());
if (empty($arForums))
{
	$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do 
		{
			$arForums[$res["ID"]] = $res;
		} while ($res = $db_res->GetNext());
	}
	if ($arParams["CACHE_TIME"] > 0):
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(array("arForums" => $arForums));
	endif;
}
$arResult["FORUMS"] = $arForums;

if (empty($arResult["FORUMS"])):
	ShowError(GetMessage("F_ERROR_NO_FORUMS"));
	return false;
endif;
$arForums = array();
foreach ($arResult["FORUMS"] as $key => $res)
{
	$arForums[$res["FORUM_GROUP_ID"]][$key] = $res;
}
foreach ($arForums as $PARENT_ID => $res)
{
	$bResult = true;
	$res = array("FORUMS" => $res);
	while ($PARENT_ID > 0) 
	{
		if (!key_exists($PARENT_ID, $arResult["GROUPS"]))
		{
			$bResult = false;
			$PARENT_ID = false;
			break;
		}
		$res = array($PARENT_ID => __array_merge($arResult["GROUPS"][$PARENT_ID], $res));
		$PARENT_ID = $arResult["GROUPS"][$PARENT_ID]["PARENT_ID"];
		$res = array("GROUPS" => $res);
		if ($PARENT_ID > 0)
			$res = __array_merge($arResult["GROUPS"][$PARENT_ID], $res);
	}
	if ($bResult == true)
		$arGroups = __array_merge($arGroups, $res);
}
$arResult["GROUPS_FORUMS"] = $arGroups;
$arParams["FID"] = array_intersect($arParams["FID"], array_keys($arResult["FORUMS"]));
/************** Search data ****************************************/
if (strLen($_REQUEST["q"]) > 0 || !empty($_REQUEST["tags"])):
	if ($_REQUEST["order"] == "date"):
		$arResult["order"]["active"] = "date";
		$aSort = array("DATE_CHANGE"=>"DESC");
	elseif($_REQUEST["order"] == "topic"):
		$arResult["order"]["active"] = "topic";
		$aSort = array("PARAM2"=>"DESC", "DATE_CHANGE"=>"ASC");
	endif;
	$arFilter1 = array(
		"MODULE_ID" => "forum",
		"SITE_ID" => SITE_ID,
		"QUERY" => $q, 
		"TAGS" => $_REQUEST["tags"] ? $_REQUEST["tags"] : "");
	if (intVal($_REQUEST["DATE_CHANGE"]) > 0)
	{
		$arFilter1["DATE_CHANGE"] = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANGUAGE_ID)), 
			time()-(intVal($_REQUEST["DATE_CHANGE"])*24*3600));
	}
	$arFilter2 = array();
	if (!empty($arParams["FID_RANGE"]) || !empty($arParams["FID"]))
	{
		$arFilter2["PARAM1"] = empty($arParams["FID_RANGE"]) ? array() : array_keys($arResult["FORUMS"]);
		$arFilter2["PARAM1"] = empty($arParams["FID"]) ? $arFilter2["PARAM1"] : $arParams["FID"];
	}
	$obSearch = new CSearch();
	$obSearch->Search($arFilter1, $aSort, array($arFilter2));
	
	if ($obSearch->errorno != 0):
		$arResult["ERROR_MESSAGE"] = $obSearch->error;
	else:
		$obSearch->NavStart($arParams["TOPICS_PER_PAGE"], false);
		$obSearch->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
		$arResult["NAV_RESULT"] = $obSearch;
		$arResult["NAV_STRING"] = $obSearch->GetPageNavStringEx($navComponentObject, GetMessage("FL_TOPIC_LIST"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
		$arResult["EMPTY"] = "Y";
		if ($res = $obSearch->GetNext())
		{
			$arResult["order"]["~relevance"] = $APPLICATION->GetCurPageParam(
				"q=".urlencode($q).(!empty($arParams["FID"]) ? "&FORUM_ID=".$arParams["FID"] : ""), 
				array("FORUM_ID", "q", "order", "s", BX_AJAX_PARAM_ID));
			$arResult["order"]["~topic"] = $APPLICATION->GetCurPageParam(
				"q=".urlencode($q).
				(!empty($arParams["FID"]) ? "&FORUM_ID=".$arParams["FID"] : "").
				"&order=topic", array("FORUM_ID", "q", "order", "s", BX_AJAX_PARAM_ID));
			$arResult["order"]["~date"] = $APPLICATION->GetCurPageParam(
				"q=".urlencode($q).
				(!empty($arParams["FID"]) ? "&FORUM_ID=".$arParams["FID"] : "").
				"&order=date", array("FORUM_ID", "q", "order", "s", BX_AJAX_PARAM_ID));
			$arResult["order"]["relevance"] = htmlspecialchars($arResult["order"]["~relevance"]);
			$arResult["order"]["topic"] = htmlspecialchars($arResult["order"]["~topic"]);
			$arResult["order"]["date"] = htmlspecialchars($arResult["order"]["~date"]);
			$arResult["EMPTY"] = "N";
			do
			{
				if (intVal($res["ITEM_ID"]) > 0)
				{
					$res["URL"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
						array("FID" => $res["PARAM1"], "TID"=>$res["PARAM2"], "MID" => $res["ITEM_ID"]));
					$res["~URL"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
						array("FID" => $res["PARAM1"], "TID"=>$res["PARAM2"], "MID" => $res["ITEM_ID"]));
				}
				else
				{ 
					$res["URL"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
						array("FID" => $res["PARAM1"], "TID"=>$res["PARAM2"], "MID" => "s"));
					$res["~URL"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"], 
						array("FID" => $res["PARAM1"], "TID"=>$res["PARAM2"], "MID" => "s"));
				}

				$res["BODY_FORMATED"] = preg_replace("#\[/?(quote|b|i|u|code|url).*?\]#i", "", $res["BODY_FORMATED"]);
				$res["DATE_CHANGE"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["DATE_CHANGE"], CSite::GetDateFormat()));
				if (strpos($res["SITE_URL"], "#message") !== false)
					$res["SITE_URL"] = substr($res["SITE_URL"], 0, strpos($res["SITE_URL"], "#message"));
					
				$res["TAGS"] = array();
				if (!empty($res["~TAGS_FORMATED"]))
				{
					foreach ($res["~TAGS_FORMATED"] as $name => $tag)
					{
						$tags = $tag;
						$res["TAGS"][] = array(
							"URL" => $APPLICATION->GetCurPageParam("tags=".urlencode($tags), array("tags")),
							"TAG_NAME" => htmlspecialchars($name),
						);
					}
				}
					
				$arResult["TOPICS"][] = $res;
			}
			while ($res = $obSearch->GetNext());
		}
	endif;
endif;
/************** For custom template *******************************/
	$arResult["index"] = $arResult["URL"]["INDEX"];
/******************************************************************/
	if ($arParams["SET_NAVIGATION"] != "N")
		$APPLICATION->AddChainItem(GetMessage("F_TITLE"));
/******************************************************************/
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle(GetMessage("F_TITLE"));
	if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
		CForumNew::ShowPanel(0, 0, false);
/******************************************************************/
	$this->IncludeComponentTemplate();
/******************************************************************/
?>