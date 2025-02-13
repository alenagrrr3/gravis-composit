<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
	global $APPLICATION, $DB, $date_create_DAYS_TO_BACK, $date_create, $date_create1;
$APPLICATION->ResetException();
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
	$arParams["UID"] = intVal(intVal($arParams["UID"]) > 0 ? $arParams["UID"] : $_REQUEST["UID"]);
	$arParams["mode"] = strToLower((strLen($arParams["mode"]) <= 0) ? $_REQUEST["mode"] : $arParams["mode"]);
	$arParams["mode"] = (in_array($arParams["mode"], array("all", "lt", "lta")) ? $arParams["mode"] : "all");
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"user_list" => "user_list.php");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["MESSAGES_PER_PAGE"] = intVal((intVal($arParams["MESSAGES_PER_PAGE"]) > 0) ? $arParams["MESSAGE_PER_PAGE"] : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10")); 
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
	$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);
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
if ($arParams["UID"] <= 0):
	ShowError(GetMessage("F_ERROR_USER_IS_EMPTY"));
	return false;
endif;
$arResult["USER"] = array();
$db_res = CForumUser::GetList(array(), array("USER_ID" => $arParams["UID"], "SHOW_ABC" => ""));
if ($db_res && $res = $db_res->GetNext())
	$arResult["USER"] = $res;
if (empty($arResult["USER"])):
	ShowError(GetMessage("F_ERROR_USER_IS_LOST"));
	return false;
endif;

$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");
$arFilter = array(); $arForums = array();
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
if (empty($arForums)):
	ShowError(GetMessage("F_ERROR_FORUMS_IS_LOST"));
	return false;
endif;
$arResult["FORUMS_ALL"] = $arForums;
/********************************************************************
				Default params
********************************************************************/
$arResult["user_list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_LIST"], array());
$arResult["SHOW_RESULT"] = "N";
$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
$arResult["FORUMS"] = array();
$arResult["FILES"] = array();
$arResult["GROUPS_FORUMS"] = array();
$arResult["USER"]["URL"] = array(
	"PROFILE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])), 
	"~PROFILE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])));
if (!empty($arResult["USER"]["AVATAR"])):
	
	$arResult["USER"]["~AVATAR"] = array("ID" => $arResult["USER"]["AVATAR"]);
	$arResult["USER"]["~AVATAR"]["FILE"] = CFile::GetFileArray($arResult["USER"]["~AVATAR"]["ID"]);
	$arResult["USER"]["~AVATAR"]["HTML"] = CFile::ShowImage($arResult["USER"]["~AVATAR"]["FILE"]["SRC"], COption::GetOptionString("forum", "avatar_max_width", 90), 
		COption::GetOptionString("forum", "avatar_max_height", 90), "border=\"0\"", "", true);
	$arResult["USER"]["AVATAR"] = $arResult["USER"]["~AVATAR"]["HTML"];
endif;
if (!empty($arResult["USER"]["DATE_REG"])):
	$arResult["USER"]["DATE_REG"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($arResult["USER"]["DATE_REG"], CSite::GetDateFormat()));
endif;
$arResult["USER"]["GROUPS"] = CUser::GetUserGroup($arParams["UID"]);
if (COption::GetOptionString("forum", "SHOW_VOTES", "Y") == "Y")
{
	$arResult["USER"]["RANK"] = CForumUser::GetUserRank($arParams["UID"], LANGUAGE_ID);
}
$ForumsPerms = array("Q" => GetMessage("LU_USER_Q"), "U" => GetMessage("LU_USER_U"), 
	"Y" => GetMessage("LU_USER_Y"), "user" => GetMessage("LU_USER_USER"));
$parser = new textParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
$parser->image_params["width"] = $arParams["IMAGE_SIZE"];
$parser->image_params["height"] = $arParams["IMAGE_SIZE"];
$arResult["PARSER"] = $parser;
$arTopics = array();
$arTopicNeeded = array();
$main = array();
$arFilterFromForm = array();
$FilterMess = array();
$FilterMessLast = array();
$arForum_posts = array();
$arResult["MESSAGE_LIST"] = array();
/********************************************************************
				/Default params
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** Filter *********************************************/
if (!is_set($_REQUEST, "set_filter") && !is_set($_REQUEST, "del_filter")):
	$_REQUEST["set_filter"] = "Y";
	reset($arResult["FORUMS_ALL"]);
	$res = current($arResult["FORUMS_ALL"]);
	$_REQUEST["fid"] = $res["ID"];
	$_REQUEST["sort"] = "topic";
endif;
if (!empty($_REQUEST["set_filter"]))
{
	InitFilterEx(array("date_create", "date_create1"),"USER_LIST","set",false); 
	if (intVal($_REQUEST["fid"]) > 0)
	{
		if (!empty($arResult["FORUMS_ALL"][$_REQUEST["fid"]]))
			$arFilterFromForm["fid"] = $_REQUEST["fid"];
		else
		{
			reset($arResult["FORUMS_ALL"]);
			$res = current($arResult["FORUMS_ALL"]);
			$_REQUEST["fid"] = $res["ID"];
			$arFilterFromForm["fid"] = $res["ID"];
			$APPLICATION->ThrowException(GetMessage("LU_INCORRECT_FORUM_ID"), "BAD_FORUM_ID");
		}
	}
	
	if (!empty($date_create) && $DB->IsDate($date_create))
		$arFilterFromForm["date_create"] = $date_create;
	elseif (!empty($date_create))
		$APPLICATION->ThrowException(GetMessage("LU_INCORRECT_LAST_MESSAGE_DATE"), "BAD_DATE_FROM");
		
	if (!empty($date_create1) && $DB->IsDate($date_create1)) 
		$arFilterFromForm["date_create1"] = $date_create1;
	elseif (!empty($date_create1))
		$APPLICATION->ThrowException(GetMessage("LU_INCORRECT_LAST_MESSAGE_DATE"), "BAD_DATE_TO");
		
	if (!empty($_REQUEST["topic"]))
		$arFilterFromForm["topic"] = $_REQUEST["topic"];
	if (!empty($_REQUEST["message"]))
		$arFilterFromForm["message"] = $_REQUEST["message"];
	$arFilterFromForm["sort"] = ($arFilterFromForm["sort"] == "message" ? "message" : "topic");
}
elseif (!empty($_REQUEST["del_filter"]))
{
	DelFilterEx(array("date_create", "date_create1"), "USER_LIST",false);
	unset($_REQUEST["fid"]);
	unset($_REQUEST["topic"]);
	unset($_REQUEST["message"]);
}
else
	InitFilterEx(array("date_create", "date_create1"), "USER_LIST","get",false);
/*******************************************************************/
$arGroupForum = array();
foreach ($arResult["FORUMS_ALL"] as $res):
	$arGroupForum[intVal($res["FORUM_GROUP_ID"])]["FORUMS"][] = $res;
endforeach;
/*******************************************************************/
$arGroups = array();
foreach ($arGroupForum as $PARENT_ID => $res)
{
	$bResult = true;
	$res = array("FORUMS" => $res["FORUMS"]);
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

/*******************************************************************/
foreach ($arResult["FORUMS_ALL"] as $key => $res)
{
	$arResult["FORUMS_ALL"][$key]["ALLOW"] = array(
		"HTML" => $res["ALLOW_HTML"],
		"ANCHOR" => $res["ALLOW_ANCHOR"],
		"BIU" => $res["ALLOW_BIU"],
		"IMG" => $res["ALLOW_IMG"],
		"VIDEO" => $res["ALLOW_VIDEO"],
		"LIST" => $res["ALLOW_LIST"],
		"QUOTE" => $res["ALLOW_QUOTE"],
		"CODE" => $res["ALLOW_CODE"],
		"FONT" => $res["ALLOW_FONT"],
		"SMILES" => $res["ALLOW_SMILES"],
		"UPLOAD" => $res["ALLOW_UPLOAD"],
		"NL2BR" => $res["ALLOW_NL2BR"]);	
	$arResult["FORUMS_ALL"][$key]["URL"] = array(
		"FORUM" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"])), 
		"~FORUM" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $res["ID"])));
	$arResult["FORUMS_ALL"][$key]["list"] = $arResult["FORUMS_ALL"][$key]["URL"]["FORUM"];
	$arResult["FORUMS_ALL"][$key]["~list"] = $arResult["FORUMS_ALL"][$key]["URL"]["~FORUM"];
}
/************** getting list topics ********************************/
CPageOption::SetOptionString("main", "nav_page_in_session", "N");
if ($arParams["mode"] == "lta" || $arParams["mode"] == "lt")
{
	if (is_set($arFilterFromForm, "fid"))
		$arFilter = array("FORUM_ID" => $arFilterFromForm["fid"]);
	else
		$arFilter = array("@FORUM_ID" => array_keys($arResult["FORUMS_ALL"]));
	$arOrder = array("FIRST_POST" => "DESC");
	if ($arParams["mode"] == "lta"):
		$arFilter["USER_START_ID"] = $arParams["UID"];
	else:
		$arFilter["AUTHOR_ID"] = $arParams["UID"];
		$arOrder = array("LAST_POST" => "DESC");
	endif;
/*******************************************************************/
	// set filters
	if (is_set($arFilterFromForm, "date_create"))
		$arFilter[">=POST_DATE"] = $arFilterFromForm["date_create"];
	if (is_set($arFilterFromForm, "date_create1"))
		$arFilter["<=POST_DATE"] = $arFilterFromForm["date_create1"];
	if (is_set($arFilterFromForm, "topic"))
		$arFilter["%TOPIC_TITLE"] = $arFilterFromForm["topic"];
	if (is_set($arFilterFromForm, "message"))
		$arFilter["%POST_MESSAGE"] = $arFilterFromForm["message"];
		
	$db_res = CForumUser::UserAddInfo($arOrder, $arFilter, "topics");
	$db_res->NavStart($arParams["MESSAGES_PER_PAGE"],false);
	$db_res->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
	$arResult["NAV_RESULT"] = $db_res;
	$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("LU_TITLE_POSTS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	if ($db_res && $res = $db_res->GetNext())
	{
		do
		{
			$arForum_posts[$res["FORUM_ID"]] += intVal($res["COUNT_MESSAGE"]);
			
			$res["ID"] = $res["TOPIC_ID"];
			$res["URL"] = array(
				"TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
					array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => "s")), 
				"~TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"], 
					array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => "s")));
			$res["read"] = $res["URL"]["TOPIC"];
			$arTopics[$res["TOPIC_ID"]] = $res;
			$FilterMess[] = $res["FIRST_POST"];
			$FilterMessLast[] = $res["LAST_POST"];
		}while ($res = $db_res->GetNext());
	}
}

$arFilter = array("AUTHOR_ID"=>$arParams["UID"]);
if (is_set($arFilterFromForm, "fid"))
	$arFilter["FORUM_ID"] = $arFilterFromForm["fid"];
else
	$arFilter["@FORUM_ID"] = array_keys($arResult["FORUMS_ALL"]);
if (!$USER->IsAdmin())
	$arFilter["USER_GROUP"] = $USER->GetUserGroupArray(); 
if ($arParams["mode"] == "lta" && count($FilterMess) > 0)
	$arFilter["@ID"] = implode(", ", $FilterMess);
elseif ($arParams["mode"] == "lt" && count($FilterMessLast) > 0)
	$arFilter["@ID"] = implode(", ", $FilterMessLast);
	
// set filter
if (is_set($arFilterFromForm, "date_create"))
	$arFilter[">=POST_DATE"] = $arFilterFromForm["date_create"];
if (is_set($arFilterFromForm, "date_create1"))
	$arFilter["<=POST_DATE"] = $arFilterFromForm["date_create1"];
if (is_set($arFilterFromForm, "topic"))
	$arFilter["%TOPIC_TITLE"] = $arFilterFromForm["topic"];
if (is_set($arFilterFromForm, "message"))
	$arFilter["%POST_MESSAGE"] = $arFilterFromForm["message"];

if (empty($arResult["NAV_RESULT"]) && $arFilterFromForm["sort"] == "topic")
	$arSort = array("TOPIC_ID" => "DESC", "ID" => "DESC");
else
	$arSort = array("ID" => "DESC");

$db_res = CForumMessage::GetListEx($arSort, $arFilter, false, false,
	array("bDescPageNumbering"=>false, "nPageSize"=>$arParams["MESSAGES_PER_PAGE"], "bShowAll" => false));
$db_res->NavStart($arParams["MESSAGES_PER_PAGE"],false);
$db_res->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
if (empty($arResult["NAV_RESULT"]))
{
	$arResult["NAV_RESULT"] = $db_res;
	$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("LU_TITLE_POSTS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
}
if ($db_res && ($res = $db_res->GetNext()))
{
	do
	{
/************** Message info ***************************************/
	// data
	$res["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat()));
	$res["EDIT_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["EDIT_DATE"], CSite::GetDateFormat()));
	// text
	$arAllow = $arResult["FORUMS_ALL"][$res["FORUM_ID"]]["ALLOW"];
	$arAllow["SMILES"] = ($res["USE_SMILES"] == "Y" ? $arResult["FORUMS_ALL"][$res["FORUM_ID"]]["ALLOW_SMILES"] : "N");
	$res["~POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
	$res["POST_MESSAGE_TEXT"] = $parser->convert($res["~POST_MESSAGE_TEXT"], $arAllow);
	$arAllow["SMILES"] = $arResult["FORUMS_ALL"][$res["FORUM_ID"]]["ALLOW_SMILES"];
	// attach
	$res["ATTACH_IMG"] = ""; $res["FILES"] = array();
	$res["~ATTACH_FILE"] = array(); $res["ATTACH_FILE"] = array();
/************** Message info/***************************************/
/************** Author info ****************************************/
	$res["AUTHOR_ID"] = intVal($res["AUTHOR_ID"]);
	$res["AVATAR"] = $arResult["USER"]["AVATAR"];
	$res["~AVATAR"] = $arResult["USER"]["~AVATAR"];
	// data
	$res["DATE_REG"] = $arResult["USER"]["DATE_REG"];
	// Another data
	$res["AUTHOR_NAME"] = $parser->wrap_long_words($res["AUTHOR_NAME"]);
	$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
	if (strLen($res["SIGNATURE"]) > 0)
	{
		$arAllow["SMILES"] = "N";
		$res["SIGNATURE"] = $parser->convert($res["~SIGNATURE"], $arAllow);
	}
	$res["FOR_JS"]["AUTHOR_NAME"] = Cutil::JSEscape(htmlspecialchars($res["~AUTHOR_NAME"]));
	$res["FOR_JS"]["POST_MESSAGE"] = Cutil::JSEscape(htmlspecialchars($res["~POST_MESSAGE_TEXT"]));
/************** Author info/****************************************/
/************** Urls ***********************************************/
	$res["URL"] = array(
		"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => $res["ID"])), 
		"EDITOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], 
			array("UID" => $res["EDITOR_ID"])), 
		"AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], 
			array("UID" => $res["AUTHOR_ID"])), 
		"AUTHOR_EMAIL" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"], 
			array("UID" => $res["AUTHOR_ID"], "TYPE" => "email")), 
		"AUTHOR_ICQ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"], 
			array("UID" => $res["AUTHOR_ID"], "TYPE" => "icq")), 
		"AUTHOR_PM" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"], 
			array("FID" => 0, "MID" => 0, "UID" => $res["AUTHOR_ID"], "mode" => "new")), 
		);
	$res["URL"]["AUTHOR_VOTE"] = ForumAddPageParams($res["URL"]["MESSAGE"],
			array("UID" => $res["AUTHOR_ID"], "MID" => $res["ID"], "VOTES" => intVal($arResult["USER"]["RANK"]["VOTES"]),
				"VOTES_TYPE" => ($res["VOTING"] == "VOTE" ? "V" : "U"), "ACTION" => "VOTE4USER"))."&amp;".bitrix_sessid_get();
/************** For custom templates *******************************/
	$res["read"] = $res["URL"]["MESSAGE"];
	
	if (empty($arTopics[$res["TOPIC_ID"]]))
		$arTopicNeeded[$res["TOPIC_ID"]] = $res["TOPIC_ID"];
	$main[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]] = $res;
	$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
	}while ($res = $db_res->GetNext());
}
/************** Attach files ***************************************/
if (!empty($arResult["MESSAGE_LIST"]))
{
	$arFilter = array("@FILE_MESSAGE_ID" => array_keys($arResult["MESSAGE_LIST"]));
	$src = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/";
	if (defined("BX_IMG_SERVER"))
		$src = BX_IMG_SERVER.$src;
	$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"), $arFilter);
	if ($db_files && $res = $db_files->Fetch())
	{
		do 
		{
			$res["SRC"] = str_replace("//", "/" , $src.$res["SUBDIR"]."/".$res["FILE_NAME"]);
			if ($arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["~ATTACH_IMG"] == $res["FILE_ID"])
			{
				$res["TOPIC_ID"] = $arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["TOPIC_ID"];
				$res["FORUM_ID"] = $arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["FORUM_ID"];
			// attach for custom 
				$main[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["MESSAGE_ID"]]["ATTACH_IMG"] =  CFile::ShowFile($res["FILE_ID"], 0, 
					$arParams["IMAGE_SIZE"], $arParams["IMAGE_SIZE"], true, "border=0", false);
				$main[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["MESSAGE_ID"]]["~ATTACH_FILE"] = $res;
			}
			$main[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
			$arResult["FILES"][$res["FILE_ID"]] = $res;
		}while ($res = $db_files->Fetch());
	}
}
if (!empty($arTopicNeeded))
{
	$db_res = CForumUser::UserAddInfo(array(), array("@TOPIC_ID" => implode(",", $arTopicNeeded), "AUTHOR_ID" => $arParams["UID"]), false, false, false);
	if ($db_res && $res = $db_res->GetNext())
	{
		do 
		{
			$arTopics[$res["TOPIC_ID"]] = $res;
		}while ($res = $db_res->GetNext());
	}
}
foreach ($main as $forum_id => $forum)
{
	$UserPermStr = "";
	$UserPerm = CForumNew::GetUserPermission($forum_id, $arResult["USER"]["GROUPS"]);
	if (array_key_exists($UserPerm, $ForumsPerms))
		$UserPermStr = $ForumsPerms[$UserPerm];
	elseif (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y")
		$UserPermStr = $arResult["USER"]["RANK"]["NAME"];
	if (strLen(trim($UserPermStr)) <= 0 && $arParams["SHOW_DEFAULT_RANK"] == "Y")
	{
		$UserPermStr = $ForumsPerms["user"];
	}
	$arForum = $arResult["FORUMS_ALL"][$forum_id];
	$arForum["NUM_POSTS_ALL"] = $arForum_posts[$forum_id];
	$arForum["PERMISSION"] = $UserPerm;
	$arForum["USER_PERM"] = $UserPerm;
	$arForum["USER_PERM_STR"] = $UserPermStr;
	$main[$forum_id] = array_merge($arForum, $main[$forum_id]);
	foreach ($main[$forum_id]["TOPICS"] as $topic_id => $topic)
	{
		$arTopics[$topic_id]["TITLE"] = $parser->wrap_long_words($arTopics[$topic_id]["TITLE"]);
		$arTopics[$topic_id]["DESCRIPTION"] = $parser->wrap_long_words($arTopics[$topic_id]["DESCRIPTION"]);
		$arTopics[$topic_id]["URL"] = array(
			"TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
				array("FID" => $arTopics[$topic_id]["FORUM_ID"], "TID" => $arTopics[$topic_id]["TOPIC_ID"], "MID" => "s")), 
			"~TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"], 
				array("FID" => $arTopics[$topic_id]["FORUM_ID"], "TID" => $arTopics[$topic_id]["TOPIC_ID"], "MID" => "s")), 
			);
/************** For custom templates *******************************/
		$arTopics[$topic_id]["read"] = $arTopics[$topic_id]["URL"]["TOPIC"];
/************** For custom templates *******************************/
		$main[$forum_id]["TOPICS"][$topic_id] = array_merge($arTopics[$topic_id], $main[$forum_id]["TOPICS"][$topic_id]);
	}
}
/*******************************************************************/
if ($APPLICATION->GetException())
{
	$err = $APPLICATION->GetException();
	$arResult["ERROR_MESSAGE"] .= $err->GetString();
}
$arResult["SHOW_RESULT"] = (!empty($main) ? "Y" : "N");
$arResult["FORUMS"] = $main;
$arResult["USER"]["profile_view"] = $arResult["USER"]["URL"]["PROFILE"];
$arResult["USER"]["~profile_view"] = $arResult["USER"]["URL"]["~PROFILE"];
/********************************************************************
				/Data
********************************************************************/

if (strToLower($arParams["mode"]) == "lta")
	$Title = GetMessage("LU_TITLE_LTA");
elseif (strToLower($arParams["mode"]) == "lt")
	$Title = GetMessage("LU_TITLE_LT");
else 
	$Title = GetMessage("LU_TITLE_ALL");
	
if ($arParams["SET_NAVIGATION"] != "N")
	$APPLICATION->AddChainItem($arResult["USER"]["SHOW_ABC"], $arResult["USER"]["~profile_view"]);
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle($arResult["USER"]["SHOW_ABC"]." (".$Title.")");
/*******************************************************************/
	$this->IncludeComponentTemplate();
?>