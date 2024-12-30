<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
if (!function_exists("CheckLastTopicsFilter"))
{
	function CheckLastTopicsFilter()
	{
		global $DB, $strError, $FilterArr, $MESS;
		foreach ($FilterArr as $s) global $$s;
		$str = "";
		if (strlen($find_date1)>0 && !$DB->IsDate($find_date1)) $str .= GetMessage("FL_INCORRECT_LAST_MESSAGE_DATE")."<br>";
		elseif (strlen($find_date2)>0 && !$DB->IsDate($find_date2)) $str .= GetMessage("FL_INCORRECT_LAST_MESSAGE_DATE")."<br>";
		$strError .= $str;
		if (strlen($str)>0) return false; else return true;
	}
}
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
global $by, $order, $FilterArr, $strError, $find_date1_DAYS_TO_BACK;
extract($GLOBALS);
if (is_array($_REQUEST)) 
	extract($_REQUEST, EXTR_SKIP);
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intVal((intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]));
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["PAGEN"] = (intVal($arParams["PAGEN"]) <= 0 ? 1 : intVal($arParams["PAGEN"]));
	$arParams["MESSAGES_PER_PAGE"] = intVal(empty($arParams["MESSAGES_PER_PAGE"]) ? COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10") : $arParams["MESSAGES_PER_PAGE"]);
	$arParams["TOPICS_PER_PAGE"] = intVal(empty($arParams["TOPICS_PER_PAGE"]) ? COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10") : $arParams["TOPICS_PER_PAGE"]);
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
$arResult["FORUM"] = array();
$arResult["FORUMS"] = array();
$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
$arResult["GROUPS_FORUMS"] = array();
$arResult["TOPICS"] = array();
$arResult["SHOW_RESULT"] = "N";
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = "";
$arResult["SortingEx"] = array(
	"TITLE" => SortingEx("TITLE"), 
	"FORUM_ID" => SortingEx("FORUM_ID"), 
	"USER_START_NAME" => SortingEx("USER_START_NAME"), 
	"POSTS" => SortingEx("POSTS"), 
	"VIEWS" => SortingEx("VIEWS"), 
	"LAST_POST_DATE" => SortingEx("LAST_POST_DATE"));

$parser = new textParser(false, false, false, "light");
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");
$arFilter = array();
$arrFilter = array();

$FilterArr = Array("find_date1", "find_date2", "find_forum");

$arForums = array();
ForumSetLastVisit();
$by = (strlen($by)<=0) ? "LAST_POST_DATE" : $by;
$order = ($order!="asc") ? "desc" : "asc";
if (!is_set($_REQUEST, "find_forum"))
	$set_default = "Y";
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["index"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array());
/************** Forums *********************************************/
$arFilter = array();
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
foreach ($arForums as $key => $val)
{
	$arForums[$key]["LAST_VISIT"] = intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"]);
	if ($arForums[$key]["LAST_VISIT"] < intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_".intVal($key)]))
		$arForums[$key]["LAST_VISIT"] = intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_".intVal($key)]);
}
$arForumsID = array_keys($arForums);
$arResult["FORUMS"] = $arForums;
if (empty($arResult["FORUMS"])):
	ShowError(GetMessage("F_NO_FORUMS"));
	return false;
endif;

$arGroupForum = array();
$arGroups = array();
foreach ($arForums as $res)
	$arGroupForum[intVal($res["FORUM_GROUP_ID"])]["FORUM"][] = $res;
foreach ($arGroupForum as $PARENT_ID => $res)
{
	$bResult = true;
	$res = array("FORUMS" => $res["FORUM"]);
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
/************** Topic list of forum ********************************/
if ($set_default == "Y")
{
/*	reset($arResult["FORUMS"]);
	$res = current($arResult["FORUMS"]);
	$GLOBALS["find_forum"] = $res["ID"];
	$_REQUEST["find_forum"] = $GLOBALS["find_forum"];
*/	$GLOBALS["find_date1_DAYS_TO_BACK"] = 2;
	$set_filter = "Y";
}
InitFilterEx($FilterArr, "LAST_TOPICS_LIST", (strlen($set_filter) > 0 ? "set" : "get"), false);
if (strlen($del_filter)>0) 
	DelFilterEx($FilterArr,"LAST_TOPICS_LIST",false);
extract($GLOBALS);
$arFilter = array(
	"@FORUM_ID" => array_keys($arResult["FORUMS"]), 
	"APPROVED" => "Y");
if (CheckLastTopicsFilter())
{
	$find_forum = (!empty($arResult["FORUMS"][$find_forum]) ? $find_forum : 0);
	if (intval($find_forum) > 0) 
	{
		$arFilter = array(
			"FORUM_ID" => intVal($find_forum), 
			"APPROVED" => "Y");
	}
	if (intval($find_date1)>0) 
	{
		$arFilter[">=LAST_POST_DATE"] = $find_date1;
	}
	if (intval($find_date2)>0)
	{
		$arFilter["<=LAST_POST_DATE"] = $find_date2;
	}
}
if ($USER->IsAuthorized())
{
	$arFilter["USER_ID"] = $USER->GetID();
	$arFilter[">RENEW_TOPIC"] = ConvertTimeStamp($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"], "FULL");
}
else 
{
	//604800 = 7*24*60*60;
	$arFilter[">LAST_POST_DATE"] = ConvertTimeStamp((time()-604800), "FULL");
}
$arFilter["!STATE"] = "L";
/*******************************************************************/
CPageOption::SetOptionString("main", "nav_page_in_session", "N");
if (!$USER->IsAuthorized()):
	$rsTopics = CForumTopic::GetListEx(array($by => $order, "POSTS" => "DESC"), $arFilter, false, 500);
	while ($arTopic = $rsTopics->Fetch())
	{
		if (!NewMessageTopic($arTopic["FORUM_ID"], $arTopic["ID"], $arTopic["LAST_POST_DATE"], false))
			continue; 
		$arrTOPICS[] = $arTopic;
	}
	$rsTopics = new CDBResult;
	$rsTopics->InitFromArray($arrTOPICS);
else:
	$rsTopics = CForumTopic::GetListEx(array($by => $order, "POSTS" => "DESC"), $arFilter, false, 0, 
		array("bDescPageNumbering" => false, "nPageSize" => $arParams["TOPICS_PER_PAGE"], "bShowAll" => false));
endif;
$rsTopics->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
$rsTopics->NavStart($arParams["TOPICS_PER_PAGE"], false);
$arResult["NAV_RESULT"] = $rsTopics;
$arResult["NAV_STRING"] = $rsTopics->GetPageNavStringEx($navComponentObject, GetMessage("FL_TOPIC_LIST"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
while ($res = $rsTopics->Fetch())
{
	$res["PERMISSION"] = ForumCurrUserPermissions($res["FORUM_ID"]);
/*******************************************************************/
	$res["URL"] = array(
		"TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"],  "MID" => "s")), 
		"~TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"],  "MID" => "s")), 
		"LAST_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "MID" => intVal($res["LAST_MESSAGE_ID"]))), 
		"~LAST_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "MID" => intVal($res["LAST_MESSAGE_ID"]))), 
		"MESSAGE_UNREAD" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "MID" => "unread_mid")),
		"~MESSAGE_UNREAD" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "MID" => "unread_mid")),
		"USER_START" => CComponentEngine::MakePathFromTemplate(	$arParams["URL_TEMPLATES_PROFILE_VIEW"], 
			array("UID" => $res["USER_START_ID"])), 
		"~USER_START" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], 
			array("UID" => $res["USER_START_ID"])), 
		"LAST_POSTER" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], 
			array("UID" => $res["LAST_POSTER_ID"])), 
		"~LAST_POSTER" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], 
			array("UID" => $res["LAST_POSTER_ID"])));
	$res["TopicStatus"] = "NEW";
/*******************************************************************/
	$res["numMessages"] = $res["POSTS"]+1;
/*******************************************************************/
	if($res["PERMISSION"] >= "Q"):
		$pageInfo = CForumMessage::GetList(array(), array("TOPIC_ID" => $res["ID"]), "cnt_not_approved");
		$res["mCnt"] = $pageInfo["CNT_NOT_APPROVED"];
		$res["numMessages"] = $pageInfo["CNT"];
	endif;
	$res["PAGES_COUNT"] = intVal(ceil($res["numMessages"]/$arParams["MESSAGES_PER_PAGE"]));
/*******************************************************************/
	$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
	$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
	$res["USER_START_NAME"] = $parser->wrap_long_words($res["USER_START_NAME"]);
	$res["LAST_POSTER_NAME"] = $parser->wrap_long_words($res["LAST_POSTER_NAME"]);
	$res["LAST_POST_DATE_FORMATED"] = $res["LAST_POST_DATE"];
	$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
	$res["START_DATE"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["START_DATE"], CSite::GetDateFormat()));
/************** For custom template ********************************/
	if ($res["APPROVED"] != "Y")
		$res["Status"] = "NA";
	$res["LAST_POSTER_HREF"] = $res["URL"]["LAST_POSTER"];
	$res["USER_START_HREF"] = $res["URL"]["USER_START"];
	$res["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["FORUM_ID"]));
	$res["read"] = $res["URL"]["TOPIC"];
	$res["read_unread"] = $res["URL"]["MESSAGE_UNREAD"];
	$res["read_last_message"] = $res["URL"]["LAST_MESSAGE"]; 
	$res["UserPermission"] = $res["PERMISSION"];
	$res["image_prefix"] = ($res["STATE"]!="Y") ? "closed_" : "";
	$res["ForumShowTopicPages"] = ForumShowTopicPages($res["numMessages"], $res["read"], "PAGEN_".$arParams["PAGEN"], 
		intVal($arParams["MESSAGES_PER_PAGE"]));
/************** For custom template/********************************/
	$arResult["TOPICS"][$res["ID"]] = $res;
}
/*******************************************************************/
$arResult["PAGE_NAME"] = "active";
$arResult["find_forum"]["data"] = $arForums;
$arResult["find_forum"]["active"] = $find_forum;
$arResult["find_date1"] = CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y", "", "");
/*******************************************************************/
$arResult["ERROR_MESSAGE"] = $strError;
/*******************************************************************/
$arResult["SHOW_RESULT"] = (empty($arResult["TOPICS"]) ? "N" : "Y");
/********************************************************************
				/Data
********************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
	$APPLICATION->AddChainItem(GetMessage("F_TITLE"));
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("F_TITLE"));
if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
	CForumNew::ShowPanel($arParams["FID"], $arParams["TID"], false);
/*******************************************************************/
	$this->IncludeComponentTemplate();
?>