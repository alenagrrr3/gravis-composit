<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
if (!function_exists("__forum_topic_last_get_id"))
{
	function __forum_topic_last_get_id()
	{
		static $ii = -1;
		$ii++;
		return $ii;
	}
}
$arSorting = array("index" => __forum_topic_last_get_id());
if ($arSorting["index"] <= 0):
	$arSorting["index"] = "";
endif;
InitSorting(false, "by".$arSorting["index"], "order".$arSorting["index"]);
global ${"by".$arSorting["index"]}, ${"order".$arSorting["index"]};
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["FID"] = (is_array($arParams["FID"]) && !empty($arParams["FID"]) ? $arParams["FID"] : array());
$arParams["SORT_BY"] = (empty($arParams["SORT_BY"]) ? false : $arParams["SORT_BY"]);
$arParams["SORT_BY"] = (${"by".$arSorting["index"]} ? ${"by".$arSorting["index"]} : $arParams["SORT_BY"]);
$arParams["SORT_BY"] = ($arParams["SORT_BY"] ? $arParams["SORT_BY"] : "LAST_POST_DATE");
$arParams["SORT_ORDER"] = strToUpper($arParams["SORT_ORDER"] == "ASC" ? "ASC" : "DESC");
$arParams["SORT_ORDER"] = strToUpper(${"order".$arSorting["index"]} ? ${"order".$arSorting["index"]} : $arParams["SORT_ORDER"]);
${"by".$arSorting["index"]} = $arParams["SORT_BY"]; ${"order".$arSorting["index"]} = $arParams["SORT_ORDER"];
$arParams["SORT_BY_SORT_FIRST"] = ($arParams["SORT_BY_SORT_FIRST"] == "N" ? "N" : "Y");
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
$arParams["TOPICS_PER_PAGE"] = intVal($arParams["TOPICS_PER_PAGE"] > 0 ? $arParams["TOPICS_PER_PAGE"] : 
	COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : 
	$arParams["DATE_TIME_FORMAT"]);
$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
/***************** ANOTHER *****************************************/
$arParams["PAGER_DESC_NUMBERING"] = ($arParams["PAGER_DESC_NUMBERING"] == "N" ? "N" : "Y");
$arParams["PAGER_TITLE"] = (empty($arParams["PAGER_TITLE"]) ? GetMessage("FTP_TITLE_NAV") : $arParams["PAGER_TITLE"]);
$arParams["PAGER_TEMPLATE"] = (empty($arParams["PAGER_TEMPLATE"]) ? false : $arParams["PAGER_TEMPLATE"]);
$arParams["PAGER_SHOW_ALWAYS"] = ($arParams["PAGER_SHOW_ALWAYS"] == "Y" ? true : false);
/***************** STANDART ****************************************/
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

$arNavParams = array("nPageSize"=>$arParams["TOPICS_PER_PAGE"], "bDescPageNumbering"=>($arParams["PAGER_DESC_NUMBERING"] == "Y"));
$arNavigation = CDBResult::GetNavParams($arNavParams);

$arSort = ($arParams["SORT_BY_SORT_FIRST"] == "Y" ? array("SORT" => "ASC") : array());
$arSort[$arParams["SORT_BY"]] = $arParams["SORT_ORDER"];

$arFilter = array();
if (!$USER->IsAdmin())
	$arFilter = array("LID" => SITE_ID, "PERMS" => array($USER->GetGroups(), 'A'), "ACTIVE" => "Y");
elseif ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N") 
	$arFilter["LID"] = SITE_ID;
if (!empty($arParams["FID"]))
	$arFilter["@ID"] = $arParams["FID"];

if($this->StartResultCache(false, array($arNavigation, $GLOBALS["USER"]->GetGroups(), $arSort, $arFilter, $arSorting)))
{
	
/********************************************************************
				Default values
********************************************************************/
$arResult["TOPIC"] = array();
$arResult["FORUM"] = array();
$arResult["FORUMS"] = array();
$arResult["TOPICS"] = array();
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
	$db_res = CForumNew::GetListEx(array(), $arFilter);
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do
		{
			$res["URL"] = array(
				"LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"])), 
				"~LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $res["ID"])));
			$res["list"] = $res["URL"]["list"]; // for custom
			$arResult["FORUMS"][$res["ID"]] = $res; 
		}while ($res = $db_res->GetNext());
	}
	
	if (!empty($arResult["FORUMS"]))
	{
// it need for custom components
		foreach (array("TITLE", "USER_START_NAME", "POSTS", "VIEWS", "LAST_POST_DATE") as $res):
			$arResult["SortingEx"][$res] = SortingEx($res, false, "by".$arSorting["index"], "order".$arSorting["index"]);
		endforeach;
// /it need for custom components
		
		CPageOption::SetOptionString("main", "nav_page_in_session", "N");
		$db_res = CForumTopic::GetListEx($arSort,
				array("@FORUM_ID" => array_keys($arResult["FORUMS"]), "APPROVED" => "Y"),
				false, false, $arNavParams);
		if ($db_res)
		{
			$db_res->NavStart($arParams["TOPICS_PER_PAGE"], false);
			$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"], $arParams["PAGER_SHOW_ALWAYS"]);
			$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
			$arResult["NAV_RESULT"] = $db_res;
			
			while ($res = $db_res->GetNext())
			{
				if (strLen(trim($res["LAST_POST_DATE"])) > 0)
				{
					$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], 
						MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
				}
				$res["URL"] = array(
					"AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], 
						array("UID" => $res["USER_START_ID"])), 
					"~AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], 
						array("UID" => $res["USER_START_ID"])), 
					"READ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
						array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "MID" => intVal($res["LAST_MESSAGE_ID"]))).
							"#message".intVal($res["LAST_MESSAGE_ID"]), 
					"~READ" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
						array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "MID" => intVal($res["LAST_MESSAGE_ID"]))).
							"#message".intVal($res["LAST_MESSAGE_ID"]));
				$res["user_start_id_profile"] = $res["URL"]["AUTHOR"]; // For custom
				$res["read"] = $res["URL"]["READ"]; // For custom
				$arResult["TOPICS"][] = $res;
			}
		}
	}
	$arResult["TOPIC"] = $arResult["TOPICS"]; // For custom
	$arResult["FORUM"] = $arResult["FORUMS"]; // For custom
/********************************************************************
				Data
********************************************************************/
	$this->IncludeComponentTemplate();
}
if ($arParams["SET_NAVIGATION"] != "N")
	$APPLICATION->AddChainItem(GetMessage("FTP_INDEX"), CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()));
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("FTP_TITLE"));
if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
	CForumNew::ShowPanel(0, 0, false);
?>