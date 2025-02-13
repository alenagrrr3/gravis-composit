<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["SEND_MAIL"] = (in_array($arParams["SEND_MAIL"], array("A", "E", "U", "Y")) ? $arParams["SEND_MAIL"] : "E");
	$arParams["SEND_ICQ"] = (in_array($arParams["SEND_ICQ"], array("A", "E", "U", "Y")) ? $arParams["SEND_ICQ"] : "A");
	$arParams["SHOW_USER_STATUS"] = ($arParams["SHOW_USER_STATUS"] == "Y" ? "Y" : "N");
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"message_send" => "PAGE_NAME=message_send&TYPE=#TYPE#&UID=#UID#",
			"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"user_post" => "PAGE_NAME=user_post&UID=#UID#&mode=#mode#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	// Page elements
	$arParams["USERS_PER_PAGE"] = (intVal($arParams["USERS_PER_PAGE"]) > 0 ? intVal($arParams["USERS_PER_PAGE"]) : 20);
	// Data and data-time format
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
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
				Default params
********************************************************************/
$arResult["SHOW_RESULT"] = "N";
$arResult["SHOW_ICQ"] = (COption::GetOptionString("forum", "SHOW_ICQ_CONTACT", "N") != "Y") ? "N" : ($arParams["SEND_ICQ"] > "A" ? "Y" : "N");
$arResult["SHOW_PM"] = $USER->IsAuthorized() ? "Y" : "N";
$arResult["SHOW_MAIL"] = ($arParams["SEND_MAIL"] > "A" ? "Y" : "N");
$arResult["SHOW_VOTES"] = COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y" ? "Y" : "N";
$arResult["USERS"] = array();
/*************** Options and default settings **********************/
$parser = new textParser(false, false, false, "light");
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
/******************************************************************/
$ForumsPerms = array("Q" => GetMessage("LU_USER_Q"), "U" => GetMessage("LU_USER_U"), 
	"Y" => GetMessage("LU_USER_Y"), "user" => GetMessage("LU_USER_USER"));
$strError = "";
$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");
InitSorting();
global $by, $order;
/********************************************************************
				/Default params
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$cache_id = "forum_forums_listex_".serialize(array());
$cache_path = $cache_path_main."forums";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	if (is_array($res["arForums"]))
		$arForums = $res["arForums"];
}
if (!is_array($arForums) || count($arForums) <= 0)
{
	$db_res = CForumNew::GetListEx();
	while ($res = $db_res->GetNext())
		$arForums[$res["ID"]] = array("ID" => $res["ID"], "NAME" => $res["NAME"]);
		
	if ($arParams["CACHE_TIME"] > 0):
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(array("arForums" => $arForums));
	endif;
}
/******************************************************************/
$arFilter = array("SHOW_ABC" => "");
if (!$USER->IsAdmin())
	$arFilter["ACTIVE"] = "Y";
if (strLen($_REQUEST["del_filter"]) <= 0 && strLen($_REQUEST["set_filter"]) > 0)
{
	if (strlen($_REQUEST["date_last_visit1"])>0 && !$GLOBALS["DB"]->IsDate($_REQUEST["date_last_visit1"])) 
		$strError .= GetMessage("LU_INCORRECT_LAST_MESSAGE_DATE");
	elseif (strlen($_REQUEST["date_last_visit2"])>0 && !$GLOBALS["DB"]->IsDate($_REQUEST["date_last_visit2"])) 
		$strError .= GetMessage("LU_INCORRECT_LAST_MESSAGE_DATE");
	if (empty($strError))
	{
		if (intVal($_REQUEST["date_last_visit1_DAYS_TO_BACK"]) > 0)
			$_REQUEST["date_last_visit1"] = GetTime(time()-86400*intval($_REQUEST["date_last_visit1_DAYS_TO_BACK"]));
		if (strlen($_REQUEST["date_last_visit1"])>0)
			$arFilter[">=LAST_VISIT"] = $_REQUEST["date_last_visit1"];
		if (strlen($_REQUEST["date_last_visit2"])>0)
			$arFilter["<=LAST_VISIT"] = $_REQUEST["date_last_visit2"];
	}
	$_REQUEST["user_name"] = trim($_REQUEST["user_name"]);
	if (!empty($_REQUEST["user_name"]))
		$arFilter["SHOW_ABC"] = $_REQUEST["user_name"];
	if ($_REQUEST["avatar"] == "Y")
		$arFilter[">=AVATAR"] = 1;
/************** For custom ****************************************/
	$arResult["filter"]["date_last_visit"] = CalendarPeriod("date_last_visit1", $_REQUEST["date_last_visit1"], "date_last_visit2", 
		$_REQUEST["date_last_visit2"], "form1", "Y", "", "");
	$arResult["filter"]["~user_name"] = $_REQUEST["user_name"];
	$arResult["filter"]["user_name"] = htmlspecialchars($_REQUEST["user_name"]);
/************** For custom/****************************************/
}
elseif (strLen($_REQUEST["del_filter"]) > 0)
{
	unset($_REQUEST["user_name"]);
	unset($_REQUEST["date_last_visit2"]);
	unset($_REQUEST["date_last_visit1"]);
	unset($_REQUEST["avatar"]);
	unset($_REQUEST["sort"]);
/************** For custom ****************************************/
	unset($GLOBALS["date_last_visit1_DAYS_TO_BACK"]);
	$arResult["filter"] = array();
	$arResult["filter"]["date_last_visit"] = CalendarPeriod("date_last_visit1", "", "date_last_visit2", "", "form1", "Y", "", "");
/************** For custom/****************************************/
}
if (!$by && !is_set($_REQUEST, "sort"))
{
	$by = "NUM_POSTS"; $order = "DESC";
	$_REQUEST["sort"] = "NUM_POSTS";
}
elseif (!$by && is_set($_REQUEST, "sort"))
{
	$by = $_REQUEST["sort"]; 
	$order = ($_REQUEST["sort"] == "SHOW_ABC" ? "ASC" : "DESC");
}
/******************************************************************/
$arResult["ERROR_MESSAGE"] = $strError;
CPageOption::SetOptionString("main", "nav_page_in_session", "N");
$db_res = CForumUser::GetList(array($by => $order), $arFilter, 
	array("bDescPageNumbering" => false, "nPageSize"=>$arParams["USERS_PER_PAGE"], "bShowAll" => false));
if($db_res)
{
	$db_res->NavStart($arParams["USERS_PER_PAGE"], false);
	$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("LU_TITLE_USER"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arResult["NAV_RESULT"] = $db_res;
	$arResult["SHOW_RESULT"] = "Y";
	$arResult["SortingEx"]["SHOW_ABC"] = SortingEx("SHOW_ABC", $APPLICATION->GetCurPageParam());
	$arResult["SortingEx"]["NUM_POSTS"] = SortingEx("NUM_POSTS", $APPLICATION->GetCurPageParam());
	$arResult["SortingEx"]["POINTS"] = SortingEx("POINTS", $APPLICATION->GetCurPageParam());
	$arResult["SortingEx"]["DATE_REGISTER"] = SortingEx("DATE_REGISTER", $APPLICATION->GetCurPageParam());
	$arResult["SortingEx"]["LAST_VISIT"] = SortingEx("LAST_VISIT", $APPLICATION->GetCurPageParam());

	if ($res = $db_res->GetNext())
	{
		do
		{
			$arUserGroup = array();
			$UserPerm = array();
			// geting max permisson of User from all forums
			if ($arParams["SHOW_USER_STATUS"] == "Y")
			{
				$arUserGroup = CUser::GetUserGroup($res["USER_ID"]);
				if (!in_array(2, $arUserGroup)) 
					$arUserGroup[] = 2;
				sort($arUserGroup);
				foreach ($arForums as $forum)
					$UserPerm[] = CForumNew::GetUserPermission($forum["ID"], $arUserGroup);
				rsort($UserPerm);
				if (array_key_exists($UserPerm[0], $ForumsPerms))
				{
					$res["UserStatus"] = $ForumsPerms[$UserPerm[0]];
				}
				elseif ($arResult["SHOW_VOTES"]=="Y")
				{
					$arUserRank = CForumUser::GetUserRank($res["USER_ID"], LANGUAGE_ID);
					$res["UserStatus"] = $arUserRank["NAME"];
				}
				 
				if (strlen(trim($res["UserStatus"])) <= 0)
					$res["UserStatus"] = $ForumsPerms["user"];
			}
			$res["AUTHOR_STATUS"] = $res["UserStatus"];
			$res["URL"] = array(
				"AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["USER_ID"])), 
				"~AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["USER_ID"])), 
				"POSTS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"], array("UID" => $res["USER_ID"], "mode" => "all")), 
				"~POSTS" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_USER_POST"], array("UID" => $res["USER_ID"], "mode" => "all")));
			
			$res["profile_view"] = $res["URL"]["AUTHOR"];
			$res["user_post"] = $res["URL"]["POSTS"];
			$res["pm_edit"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"], array("FID" => 0, "MID" => 0, "mode" => "new", "UID" => $res["USER_ID"]));
			$res["mail"] =  CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"], array("TYPE" => "mail", "UID" => $res["USER_ID"]));
			$res["DATE_REG"] = !empty($res["DATE_REGISTER_SHORT"]) ? CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["DATE_REGISTER_SHORT"], CSite::GetDateFormat())) : "";
			$res["LAST_VISIT"] = !empty($res["LAST_VISIT"]) ? CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_VISIT"], CSite::GetDateFormat())) : "";
			if ($arResult["SHOW_ICQ"] == "Y")
				$res["icq"] =  CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"], array("TYPE" => "icq", "UID" => $res["USER_ID"]));
				
			if (strLen($res["AVATAR"])>0)
			{
				$res["~AVATAR"] = array("ID" => $res["AVATAR"]);
				$res["~AVATAR"]["FILE"] = CFile::GetFileArray($res["~AVATAR"]["ID"]);
				$res["~AVATAR"]["HTML"] = CFile::ShowImage($res["~AVATAR"]["FILE"]["SRC"], 
					COption::GetOptionString("forum", "avatar_max_width", 90), 
					COption::GetOptionString("forum", "avatar_max_height", 90), 
					"border=\"0\"", "", true);
						
				$res["~AVATAR"]["HTML_SMALL"] = CFile::ShowImage($res["~AVATAR"]["FILE"]["SRC"], 20, 20, "border=0 alt=\"\"", "", true);
				$res["AVATAR_ARRAY"] = $res["~AVATAR"];
				$res["AVATAR"] = $res["~AVATAR"]["HTML_SMALL"];
			}
			$res["SHOW_ABC"] = $parser->wrap_long_words($res["SHOW_ABC"]);
			$arResult["USERS"][] = $res;
		}while($res = $db_res->GetNext());
	}
}
/********************************************************************
				/Data
********************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
	$APPLICATION->AddChainItem(GetMessage("LU_TITLE_USER"));
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("LU_TITLE_USER"));
/******************************************************************/
$this->IncludeComponentTemplate();
?>