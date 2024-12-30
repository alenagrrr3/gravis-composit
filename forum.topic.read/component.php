<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return false;
endif;
$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/functions.php");
include_once($path);
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["VERSION"] = intVal($arParams["VERSION"]);
	$arParams["FID"] = intVal((intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]));
	$GLOBALS["FID"] = $arParams["FID"]; // for top panel
	$arParams["TID"] = intVal((intVal($arParams["TID"]) <= 0 ? $_REQUEST["TID"] : $arParams["TID"]));
	$arParams["MID_UNREAD"] = (strLen(trim($arParams["MID"])) <= 0 ? $_REQUEST["MID"] : $arParams["MID"]);
	$arParams["MID"] = (is_array($arParams["MID"]) ? 0 : intVal($arParams["MID"]));
	if (strToLower($arParams["MID_UNREAD"]) == "unread_mid")
		$arParams["MID"] = intVal(ForumGetFirstUnreadMessage($arParams["FID"], $arParams["TID"]));
	$arParams["MESSAGES_PER_PAGE"] = intVal(empty($arParams["MESSAGES_PER_PAGE"]) ? 
		COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10") : $arParams["MESSAGES_PER_PAGE"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"forums" => "PAGE_NAME=forums&GID=#GID#",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"subscr_list" => "PAGE_NAME=subscr_list",
			"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#",
			"message_send" => "PAGE_NAME=message_send&UID=#UID#&TYPE=#TYPE#",
			"message_move" => "PAGE_NAME=message_move&FID=#FID#&TID=#TID#&MID=#MID#",
			"topic_new" => "PAGE_NAME=topic_new&FID=#FID#",
			"topic_move" => "PAGE_NAME=topic_move&FID=#FID#&TID=#TID#",
			"rss" => "PAGE_NAME=rss&TYPE=#TYPE#&MODE=#MODE#&IID=#IID#", 
			"user_post" => "PAGE_NAME=user_post&UID=#UID#&mode=#mode#");
		
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
	$arParams["PAGEN"] = (intVal($arParams["PAGEN"]) <= 0 ? 1 : intVal($arParams["PAGEN"]));
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);

	$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);
	$arParams["PATH_TO_ICON"] = trim($arParams["PATH_TO_ICON"]);
	
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);

	// Data and data-time format
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	// AJAX
	if ($arParams["AJAX_TYPE"] == "Y" || ($arParams["AJAX_TYPE"] == "A" && COption::GetOptionString("main", "component_ajax_on", "Y") == "Y"))
		$arParams["AJAX_TYPE"] = "Y";
	else
		$arParams["AJAX_TYPE"] = "N";
	$arParams["AJAX_CALL"] = (($arParams["AJAX_TYPE"] == "Y" && $_REQUEST["AJAX_CALL"] == "Y") ? "Y" : "N");
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
		
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default params
********************************************************************/
	$arMessage = array(); 
	$arResult["TOPIC"] = array();
	$arResult["FORUM"] = array();
	$arParams["PERMISSION"] = ForumCurrUserPermissions($arParams["FID"]);
	$arResult["MESSAGE_LIST"] = array();
	$arResult["MESSAGE_VIEW"] = array();
	$arResult["USER"] = array(
		"INFO" => array(),
		"PERMISSION" => $arParams["PERMISSION"],
		"RIGHTS" => array(),
		"SUBSCRIBE" => array());

	$UserInfo = array();
	
	$arOk = array();
	$action = false;
	if (!empty($_REQUEST["ACTION"]))
		$action = $_REQUEST["ACTION"];
	elseif ($_POST["MESSAGE_TYPE"]=="REPLY")
		$action = "REPLY";
	elseif (($_REQUEST["TOPIC_SUBSCRIBE"] == "Y") || ($_REQUEST["FORUM_SUBSCRIBE"] == "Y"))
		$action = "SUBSCRIBE";
	$number = 1;
	$strErrorMessage = "";
	$strOKMessage = "";
	$View = false;
	$arResult["VIEW"] = "N";
	$bVarsFromForm = false;
	$arError = array();
	$arNote = array();
	$_REQUEST["result"] = ($_SERVER['REQUEST_METHOD'] == 'GET' ? $_REQUEST["result"] : '');
	switch (strToLower($_REQUEST["result"]))
	{
		case "message_add":
		case "mid_add":
		case "reply":
				$strOKMessage = GetMessage("F_MESS_SUCCESS_ADD");
			break;
			
		case "show": $strOKMessage = GetMessage("F_MESS_SUCCESS_SHOW"); break;
		case "hide": $strOKMessage = GetMessage("F_MESS_SUCCESS_HIDE"); break;
		case "del":	$strOKMessage = GetMessage("F_MESS_SUCCESS_DEL"); break;
		
		case "top": 	$strOKMessage = GetMessage("F_TOPIC_SUCCESS_TOP"); break;
		case "ordinary": 	$strOKMessage = GetMessage("F_TOPIC_SUCCESS_ORD"); break;
		case "open": 	$strOKMessage = GetMessage("F_TOPIC_SUCCESS_OPEN"); break;
		case "close": 	$strOKMessage = GetMessage("F_TOPIC_SUCCESS_CLOSE"); break;
		
		case "VOTE4USER":
			$arFields = array(
				"UID" => $_GET["UID"],
				"VOTES" => $_GET["VOTES"],
				"VOTE" => (($_GET["VOTES_TYPE"]=="U") ? True : False));
			$url = CComponentEngine::MakePathFromTemplate(
				$arParams["URL_TEMPLATES_MESSAGE"], 
				array("FID" => $arParams["FID"], 
					"TID" => $arParams["FID"], 
					"MID" => (intVal($_REQUEST["MID"]) > 0 ? $_REQUEST["MID"] : "s")
				));
			break;
		case "FORUM_SUBSCRIBE":
		case "TOPIC_SUBSCRIBE":
		case "FORUM_SUBSCRIBE_TOPICS":
			$arFields = array(
				"FID" => $arParams["FID"],
				"TID" => (($action=="FORUM_SUBSCRIBE")?0:$arParams["TID"]),
				"NEW_TOPIC_ONLY" => (($action=="FORUM_SUBSCRIBE_TOPICS")?"Y":"N"));
			$url = ForumAddPageParams(
					CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_SUBSCR_LIST"], 
						array()
					), 
					array("FID" => $arParams["FID"], "TID" => $arParams["TID"]));
			break;
		case "mid_for_move_is_empty":
			$strErrorMessage = "mid_for_move_is_empty"; 
			break;
	}
	unset($_GET["result"]);
	DeleteParam(array("result", "MID", "ACTION"));
	unset($_GET["MID"]); unset($GLOBALS["HTTP_GET_VARS"]["MID"]);
	unset($_GET["ACTION"]); unset($GLOBALS["HTTP_GET_VARS"]["ACTION"]);
	
	$parser = new textParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
	$parser->MaxStringLen = $arParams["WORD_LENGTH"];
	$parser->image_params["width"] = $arParams["IMAGE_SIZE"];
	$parser->image_params["height"] = $arParams["IMAGE_SIZE"];
	
	$arResult["GROUP_NAVIGATION"] = array();
	$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
	
	$_REQUEST["FILES"] = is_array($_REQUEST["FILES"]) ? $_REQUEST["FILES"] : array();
	$_REQUEST["FILES_TO_UPLOAD"] = is_array($_REQUEST["FILES_TO_UPLOAD"]) ? $_REQUEST["FILES_TO_UPLOAD"] : array();
/********************************************************************
				/Default params
********************************************************************/


/********************************************************************
				Main Data & Permissions
********************************************************************/
	if ($arParams["MID"] > 0):
		$res = CForumMessage::GetByIDEx($arParams["MID"], array("GET_TOPIC_INFO" => "Y", "GET_FORUM_INFO" => "Y"));
		if (is_array($res)):
			$arParams["TID"] = intVal($res["TOPIC_ID"]);
			$arParams["FID"] = intVal($res["FORUM_ID"]);
			$arResult["TOPIC"] = $res["TOPIC_INFO"];
			$arResult["FORUM"] = $res["FORUM_INFO"];
			if ($arParams["PERMISSION"] < "Q" && $res["APPROVED"] != "Y"):
				$strOKMessage = GetMessage("F_MESS_SUCCESS_ADD_MODERATE");
			endif;
		else:
			$strErrorMessage .= GetMessage("F_ERROR_MID_IS_LOST");
		endif;
	endif;
	
	if (empty($arResult["TOPIC"])):
		$res = CForumTopic::GetByIDEx($arParams["TID"], array("GET_FORUM_INFO" => "Y")); 
		if (is_array($res)):
			$arParams["FID"] = intVal($res["FORUM_ID"]);
			$arResult["TOPIC"] = $res;
			$arResult["FORUM"] = $res["FORUM_INFO"];
		endif;
	endif;
	
	if (empty($arResult["TOPIC"])):
		$arError = array(
			"code" => "tid_is_lost",
			"title" => GetMessage("F_ERROR_TID_IS_LOST"),
			"link" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])));
	elseif (($arResult["TOPIC"]["STATE"] == "L") && (intVal($arResult["TOPIC"]["TOPIC_ID"]) > 0)):
		$res = CForumTopic::GetByID($arResult["TOPIC"]["TOPIC_ID"]); 
		if ($res)
		{
			$arNote = array(
				"code" => "tid_moved",
				"title" => GetMessage("F_ERROR_TID_MOVED"),
				"link" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"], 
					array("FID" => $arResult["TOPIC"]["FORUM_ID"], "TID" => $arResult["TOPIC"]["TOPIC_ID"], "MID" => "s")));
		}
		else 
		{
			$arError = array(
				"code" => "tid_is_lost",
				"title" => GetMessage("F_ERROR_TID_IS_LOST"),
				"link" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], 
					array("FID" => $arResult["TOPIC"]["FORUM_ID"])));
		}
	elseif (!CForumNew::CanUserViewForum($arParams["FID"], $USER->GetUserGroupArray())):
		$APPLICATION->AuthForm(GetMessage("F_FPERMS"));
	elseif (!CForumTopic::CanUserViewTopic($arParams["TID"], $USER->GetUserGroupArray())):
	// Topic is approve? For moderation forum.
		$arError = array(
			"code" => "tid_not_approved",
			"title" => GetMessage("F_ERROR_TID_NOT_APPROVED"),
			"link" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], 
				array("FID" => $arParams["FID"])));
	endif;
/********************************************************************
				/Main Data & Permissions
********************************************************************/
if (!empty($arNote["link"]) || !empty($arError)):
	if ($arParams["AJAX_CALL"] == "N" && !empty($arError))
	{
		ShowError($arError["title"]);
		return false;
		//LocalRedirect(ForumAddPageParams($arError["link"], array("error" => $arError["code"])));
	}
	elseif ($arParams["AJAX_CALL"] == "N" && !empty($arNote["link"]))
	{
		LocalRedirect(ForumAddPageParams($arNote["link"], array("result" => $arNote["action"])));
	}
	elseif ($arParams["AJAX_CALL"] == "Y")
	{
		$APPLICATION->RestartBuffer();
		?><?=CUtil::PhpToJSObject(
			array(
				"error" => $arError,
				"note" => $arNote
				))?><?
		die();
	}
endif;

ForumSetLastVisit($arParams["FID"], $arParams["TID"]);
ForumSetReadTopic($arParams["FID"], $arParams["TID"]);

/********************************************************************
				Action
********************************************************************/
$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/action.php");
include($path);
if ($arParams["AJAX_CALL"] == "Y")
{
	$APPLICATION->RestartBuffer();
	?><?=CUtil::PhpToJSObject(
		array(
		"error" => array(
			"code" => $action,
			"title" => $strErrorMessage),
		"note" => $arNote));
	die();
}
elseif (!empty($arNote["link"]))
{
	LocalRedirect(ForumAddPageParams($arNote["link"], array("result" => $arNote["code"]), true, false).
		(!empty($arParams["MID"]) ? "#message".$arParams["MID"] : ""));
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** Topic **********************************************/
foreach ($arResult["TOPIC"] as $key => $val):
	$arResult["TOPIC"]["~".$key] = $val;
	$arResult["TOPIC"][$key] = htmlspecialcharsEx($val);
	if (!is_array($val))
		$arResult["TOPIC"][$key] = $parser->wrap_long_words($arResult["TOPIC"][$key]);
endforeach;
$arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] = "";
/************** Forum **********************************************/
foreach ($arResult["FORUM"] as $key => $val):
	$arResult["FORUM"]["~".$key] = $val;
	$arResult["FORUM"][$key] = htmlspecialcharsEx($val);
endforeach;
/************** Current User ***************************************/
$arResult["USER"]["SHOW_NAME"] = GetMessage("F_GUEST");
$arResult["USER"]["RIGHTS"] = array(
	"ADD_TOPIC" => CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID(), $arResult["FORUM"]) ? "Y" : "N", 
	"MODERATE" => (CForumNew::CanUserModerateForum($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID()) == true ? "Y" : "N"), 
	"EDIT" => CForumNew::CanUserEditForum($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID()) ? "Y" : "N", 
	"ADD_MESSAGE" => CForumMessage::CanUserAddMessage($arParams["TID"], $USER->GetUserGroupArray(), $USER->GetID()) ? "Y" : "N");
if ($USER->IsAuthorized()):
	$arResult["USER"]["INFO"] = CForumUser::GetByUSER_ID($USER->GetParam("USER_ID"));
	$arResult["USER"]["SHOW_NAME"] = $_SESSION["FORUM"]["SHOW_NAME"];
	$arResult["USER"]["RANK"] = CForumUser::GetUserRank($USER->GetParam("USER_ID")); 
	$arFields = array("USER_ID" => $USER->GetID(), "FORUM_ID" => $arParams["FID"], "TOPIC_ID" => $arParams["TID"], "SITE_ID" => SITE_ID);
	$db_res = CForumSubscribe::GetList(array(), $arFields);
	if ($db_res && $res = $db_res->Fetch())
	{
		$arResult["USER"]["SUBSCRIBE"][$res["ID"]] = $res;
	}
endif;
/************** Edit panels info ***********************************/
$arResult["PANELS"] = array(
	"MODERATE" => $arResult["USER"]["RIGHTS"]["MODERATE"], 
	"DELETE" => $arResult["USER"]["RIGHTS"]["EDIT"], 
	"SUPPORT" => IsModuleInstalled("support") && $APPLICATION->GetGroupRight("forum") >= "W" ? "Y" : "N", 
	"EDIT" => $arResult["USER"]["RIGHTS"]["EDIT"], 
	"STATISTIC" => IsModuleInstalled("statistic") && $APPLICATION->GetGroupRight("statistic") > "D" ? "Y" : "N", 
	"MAIN" => $APPLICATION->GetGroupRight("main") > "D" ? "Y" : "N");
/************** Urls ***********************************************/
$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
	array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => "s"));
if ((intVal($_REQUEST["PAGEN_".$arParams["PAGEN"]]) > 1) && (intVal($arParams["MID"]) <= 0))
	$arResult["CURRENT_PAGE"] = ForumAddPageParams($arResult["CURRENT_PAGE"], 
		array("PAGEN_".$arParams["PAGEN"] => intVal($_REQUEST["PAGEN_".$arParams["PAGEN"]])));
$_SERVER["REQUEST_URI"] = $arResult["CURRENT_PAGE"];

$arResult["URL"] = array(
	"TOPIC_NEW" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_NEW"], array("FID" => $arParams["FID"])), 
	"~TOPIC_NEW" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC_NEW"], array("FID" => $arParams["FID"])), 
	"TOPIC_LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])), 
	"~TOPIC_LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])), 
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()), 
	"RSS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"], array("TYPE" => "default", "MODE" => "topic", "IID" => $arParams["TID"])), 
	"~RSS" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RSS"], array("TYPE" => "default", "MODE" => "topic", "IID" => $arParams["TID"])));

$arResult["ERROR_MESSAGE"] = $strErrorMessage;
$arResult["OK_MESSAGE"] = $strOKMessage;
$arResult["PARSER"] = $parser;
$arResult["FILES"] = array();
$arResult["MESSAGE_FILES"] = array();
/************** Message List ***************************************/
$arAllow = array(
	"HTML" => $arResult["FORUM"]["ALLOW_HTML"],
	"ANCHOR" => $arResult["FORUM"]["ALLOW_ANCHOR"],
	"BIU" => $arResult["FORUM"]["ALLOW_BIU"],
	"IMG" => $arResult["FORUM"]["ALLOW_IMG"],
	"VIDEO" => $arResult["FORUM"]["ALLOW_VIDEO"],
	"LIST" => $arResult["FORUM"]["ALLOW_LIST"],
	"QUOTE" => $arResult["FORUM"]["ALLOW_QUOTE"],
	"CODE" => $arResult["FORUM"]["ALLOW_CODE"],
	"FONT" => $arResult["FORUM"]["ALLOW_FONT"],
	"SMILES" => $arResult["FORUM"]["ALLOW_SMILES"],
	"UPLOAD" => $arResult["FORUM"]["ALLOW_UPLOAD"],
	"NL2BR" => $arResult["FORUM"]["ALLOW_NL2BR"]);

// LAST MESSAGE
$arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] = 0;
if ($arResult["USER"]["RIGHTS"]["EDIT"] != "Y"):
	$db_res = CForumMessage::GetList(array("ID"=>"DESC"), array("TOPIC_ID"=>$arParams["TID"]), false, 1);
	if (($db_res) && ($res = $db_res->Fetch()))
		$arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] = intVal($res["ID"]);
endif;
// NUMBER CURRENT PAGE
$iNumPage = 0;
if ($arParams["MID"] > 0)
	$iNumPage = CForumMessage::GetMessagePage($arParams["MID"], $arParams["MESSAGES_PER_PAGE"], $USER->GetUserGroupArray(), $arParams["TID"]);
// Create filter and additional fields for message select
$arFilter = array("TOPIC_ID" => $arParams["TID"]);
if ($arResult["USER"]["RIGHTS"]["MODERATE"] != "Y") {$arFilter["APPROVED"] = "Y";}
if ($USER->IsAuthorized()) {$arFilter["POINTS_TO_AUTHOR_ID"] = $USER->GetID();}
$arFields = array("bDescPageNumbering"=>false, "nPageSize"=>$arParams["MESSAGES_PER_PAGE"], "bShowAll" => false);
if ($iNumPage > 0) {$arFields["iNumPage"] = $iNumPage;}
/*******************************************************************/
CPageOption::SetOptionString("main", "nav_page_in_session", "N");
$db_res = CForumMessage::GetListEx(
	array("ID"=>"ASC"), $arFilter, false, false, 
	$arFields);
$db_res->NavStart($arParams["MESSAGES_PER_PAGE"], false, ($iNumPage > 0 ? $iNumPage : false));
$db_res->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
/*******************************************************************/
$arResult["NAV_RESULT"] = $db_res;
$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("F_TITLE_NAV"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
$number = intVal($db_res->NavPageNomer-1)*$arParams["MESSAGES_PER_PAGE"] + 1;

while ($res = $db_res->GetNext())
{
/************** Message info ***************************************/
	// number in topic
	$res["NUMBER"] = $number++;
	// data
	$res["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat()));
	$res["EDIT_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["EDIT_DATE"], CSite::GetDateFormat()));
	// text
	$arAllow["SMILES"] = ($res["USE_SMILES"] == "Y" ? $arResult["FORUM"]["ALLOW_SMILES"] : "N");
	$res["~POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
	$res["POST_MESSAGE_TEXT"] = $parser->convert($res["~POST_MESSAGE_TEXT"], $arAllow);
	$arAllow["SMILES"] = $arResult["FORUM"]["ALLOW_SMILES"];
	// attach
	$res["ATTACH_IMG"] = ""; $res["FILES"] = array();
	$res["~ATTACH_FILE"] = array(); $res["ATTACH_FILE"] = array();
/************** Message info/***************************************/
/************** Author info ****************************************/
	$res["AUTHOR_ID"] = intVal($res["AUTHOR_ID"]);
	if ($res["AUTHOR_ID"] <= 0):
		$arUser = array();
	else:
		if (!array_key_exists($res["AUTHOR_ID"], $UserInfo)):
			$arUser["Groups"] = CUser::GetUserGroup($res["AUTHOR_ID"]);
			$arUser["Perms"] = CForumNew::GetUserPermission($res["FORUM_ID"], $arUser["Groups"]);
			if ($arUser["Perms"] <= "Q" && COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y")
				$arUser["Rank"] = CForumUser::GetUserRank($res["AUTHOR_ID"], LANGUAGE_ID);
			$arUser["Points"] = (intVal($res["POINTS"]) > 0 ? 
				array("POINTS" => $res["POINTS"], "DATE_UPDATE" => $res["DATE_UPDATE"]) : false);
			$UserInfo[$res["AUTHOR_ID"]] = $arUser;
		endif;
		$arUser = $UserInfo[$res["AUTHOR_ID"]];
	endif;
	// Status
	$res["AUTHOR_STATUS"] = "";
	if ($res["AUTHOR_ID"] > 0):
		if ($arUser["Perms"] == "Q") 
			$res["AUTHOR_STATUS"] = GetMessage("F_MODERATOR");
		elseif ($arUser["Perms"] == "U") 
			$res["AUTHOR_STATUS"] = GetMessage("F_EDITOR");
		elseif ($arUser["Perms"] == "Y") 
			$res["AUTHOR_STATUS"] = GetMessage("F_ADMIN");
		elseif (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y")
			$res["AUTHOR_STATUS"] = $arUser["Rank"]["NAME"];
		elseif ($arParams["SHOW_DEFAULT_RANK"] == "Y") 
			$res["AUTHOR_STATUS"] = GetMessage("F_USER");
		?><?
	else: 
		$res["AUTHOR_STATUS"] = GetMessage("F_GUEST");
	endif;
	// Avatar
	if (strLen($res["AVATAR"]) > 0):
		$res["AVATAR"] = array("ID" => $res["AVATAR"]);
		$res["AVATAR"]["FILE"] = CFile::GetFileArray($res["AVATAR"]["ID"]);
		$res["AVATAR"]["HTML"] = CFile::ShowImage($res["AVATAR"]["FILE"]["SRC"], COption::GetOptionString("forum", "avatar_max_width", 90), 
			COption::GetOptionString("forum", "avatar_max_height", 90), "border=\"0\"", "", true);
	endif;
	// data
	$res["DATE_REG"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["DATE_REG"], CSite::GetDateFormat()));
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
	// Voting
	$res["VOTING"] = "N";
	if ($res["AUTHOR_ID"] > 0 && COption::GetOptionString("forum", "SHOW_VOTES", "Y") == "Y" && $USER->IsAuthorized() &&
		($USER->IsAdmin() || (intVal($USER->GetID()) != $res["AUTHOR_ID"]))):
		$strNotesText = "";
		$bVote = "N"; $bUnVote = "N";
		if ($arUser["Points"])
		{
			$bUnVote = "Y";
			$strNotesText = str_replace("#POINTS#", $arUser["Points"]["POINTS"], 
				str_replace("#END#", ForumNumberEnding($arUser["Points"]["POINTS"]), GetMessage("F_YOU_ALREADY_VOTE1"))).". ";

			if (intVal($arUser["Points"]["POINTS"]) < intVal($arResult["USER"]["RANK"]["VOTES"]))
			{
				$bVote = "Y";
				$strNotesText .= str_replace("#POINTS#", (intVal($arUser["Points"]["VOTES"])-intVal($arUser["Points"]["POINTS"])), 
					str_replace("#END#", ForumNumberEnding((intVal($arResult["USER"]["RANK"]["VOTES"])-intVal($arUser["Points"]["POINTS"]))), GetMessage("F_YOU_ALREADY_VOTE3")));
			}
			if ($USER->IsAdmin())
				$strNotesText .= GetMessage("F_VOTE_ADMIN");
		}
		elseif (intVal($arResult["USER"]["RANK"]["VOTES"]) > 0 || $USER->IsAdmin())
		{
			$bVote = "Y";
			$strNotesText = GetMessage("F_NO_VOTE").
				str_replace("#POINTS#", (intVal($arResult["USER"]["RANK"]["VOTES"])-intVal($arUser["Points"]["POINTS"])), 
				str_replace("#END#", ForumNumberEnding((intVal($arResult["USER"]["RANK"]["VOTES"])-intVal($arUser["Points"]["POINTS"]))), 
					GetMessage("F_NO_VOTE1"))).". ";
			if ($USER->IsAdmin())
				$strNotesText .= GetMessage("F_VOTE_ADMIN");
		}
		
		if ($bVote == "Y" || $bUnVote == "Y")
		{
			$res["VOTING"] = ($bVote == "Y" ? "VOTE" : "UNVOTE");
		}
	endif;
/************** Author info/****************************************/
/************** Panels *********************************************/
	$res["PANELS"] = array(
		"MODERATE" => $arResult["PANELS"]["MODERATE"], 
		"DELETE" => $arResult["PANELS"]["DELETE"],
		"SUPPORT" => $arResult["PANELS"]["SUPPORT"] == "Y" && $res["AUTHOR_ID"] > 0 ? "Y" : "N", 
		"EDIT" => $arResult["PANELS"]["EDIT"], 
		"STATISTIC" => $arResult["PANELS"]["STATISTIC"] == "Y" && intVal($res["GUEST_ID"]) > 0 ? "Y" : "N", 
		"MAIN" => $arResult["PANELS"]["MAIN"] == "Y" && $res["AUTHOR_ID"] > 0 ? "Y" : "N", 
		"VOTES" => $res["VOTING"] != "N" ? "Y" : "N");
	if ($res["PANELS"]["EDIT"] != "Y" && $USER->IsAuthorized() && $res["AUTHOR_ID"] == $USER->GetId() &&
		(COption::GetOptionString("forum", "USER_EDIT_OWN_POST", "N") == "Y" || $arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] == intVal($res["ID"])))
	{
		$res["PANELS"]["EDIT"] = "Y";
	}
	$res["SHOW_PANEL"] = in_array("Y", $res["PANELS"]) ? "Y" : "N";
	
	if ($arResult["USER"]["PERMISSION"] >= "Q")
	{
		$bIP = (ereg("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$", $res["~AUTHOR_IP"]) ? true : false);
		$res["AUTHOR_IP"] = ($bIP ? GetWhoisLink($res["~AUTHOR_IP"], "") : $res["AUTHOR_IP"]);
		$bIP = (ereg("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$", $res["~AUTHOR_REAL_IP"]) ? true : false);
		$res["AUTHOR_REAL_IP"] = ($bIP ? GetWhoisLink($res["~AUTHOR_REAL_IP"], "") : $res["AUTHOR_REAL_IP"]);
		$res["IP_IS_DIFFER"] = ($res["AUTHOR_IP"] <> $res["AUTHOR_REAL_IP"] ? "Y" : "N");
	}
/************** Panels/*********************************************/
/************** Urls ***********************************************/
	$res["URL"] = array(
		"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
			array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => $res["ID"])), 
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
		"AUTHOR_POSTS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"], 
			array("UID" => $res["AUTHOR_ID"], "mode" => "all")));
	$res["URL"]["AUTHOR_VOTE"] = ForumAddPageParams($res["URL"]["MESSAGE"],
			array("UID" => $res["AUTHOR_ID"], "MID" => $res["ID"], "VOTES" => intVal($arResult["USER"]["RANK"]["VOTES"]),
				"VOTES_TYPE" => ($res["VOTING"] == "VOTE" ? "V" : "U"), "ACTION" => "VOTE4USER"))."&amp;".bitrix_sessid_get();
		
	if ($res["SHOW_PANEL"] == "Y")
	{
		$res["URL"]["MODERATE"] = ForumAddPageParams($res["URL"]["MESSAGE"], 
				array("MID" => $res["ID"], "ACTION" => $res["APPROVED"]=="Y" ? "HIDE" : "SHOW"))."&amp;".bitrix_sessid_get();
		$res["URL"]["DELETE"] = ForumAddPageParams($res["URL"]["MESSAGE"], 
				array("MID" => $res["ID"], "ACTION" => "DEL"))."&amp;".bitrix_sessid_get();
		$res["URL"]["SUPPORT"] = ForumAddPageParams($res["URL"]["MESSAGE"], 
				array("MID" => $res["ID"], "ACTION" => "FORUM_MESSAGE2SUPPORT"))."&amp;".bitrix_sessid_get();
		$res["URL"]["EDIT"] = ForumAddPageParams(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_NEW"], 
				array("FID" => $arParams["FID"])), array("TID" => $arParams["TID"], "MID" => $res["ID"], "MESSAGE_TYPE" => "EDIT")
				)."&amp;".bitrix_sessid_get();
	}
/************** For custom templates *******************************/
	if ($arParams["VERSION"] < 1)
	{
		$res["MESSAGE_ANCHOR"] = $res["URL"]["MESSAGE"];
		$res["message_link"] = $res["URL"]["MESSAGE"];
		$res["profile_view"] = $res["URL"]["AUTHOR"];
		$res["email"] = $res["URL"]["AUTHOR_EMAIL"];
		$res["icq"] = $res["URL"]["AUTHOR_ICQ"];
		$res["pm_edit"] = $res["URL"]["AUTHOR_PM"];
		
		if ($res["SHOW_PANEL"] == "Y")
		{
			$res["SHOW_HIDE"] = array(
				"ACTION" => $res["PANELS"]["MODERATE"] == "Y" ? ($res["APPROVED"]=="Y" ? "HIDE" : "SHOW") : "N", 
				"link" => $res["URL"]["MODERATE"]);
			$res["MESSAGE_DELETE"] = array(
				"ACTION" => ($res["PANELS"]["DELETE"] == "Y" ? "DELETE" : "N"), 
				"link" => $res["URL"]["DELETE"]);
			$res["MESSAGE_SUPPORT"] = array(
				"ACTION" => ($res["PANELS"]["SUPPORT"] == "Y" ? "SUPPORT" : "N"), 
				"link" => $res["URL"]["SUPPORT"]);
			$res["MESSAGE_EDIT"] = array(
				"ACTION" => ($res["PANELS"]["EDIT"] == "Y" ? "EDIT" : "N"), 
				"link" => $res["URL"]["EDIT"]);
			$res["VOTES"] = array(
				"ACTION" => $res["PANELS"]["VOTES"] == "Y" ? $res["VOTING"] : "N", 
				"link" => $res["URL"]["AUTHOR_VOTE"]);
			$res["SHOW_STATISTIC"] = $arResult["PANELS"]["STATISTIC"];
			$res["SHOW_AUTHOR_ID"] = $arResult["PANELS"]["MAIN"];
		}
	}
/************** For custom templates/*******************************/
	$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
}
/************** Attach files ***************************************/
if (!empty($arResult["MESSAGE_LIST"]))
{
	$res = array_keys($arResult["MESSAGE_LIST"]);
	$arFilter[">MESSAGE_ID"] = intVal($res[0]) - 1;
	$arFilter["<MESSAGE_ID"] = intVal($res[count($res) - 1]) + 1;
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
			// attach for custom 
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["~ATTACH_FILE"] = $res;
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["ATTACH_IMG"] = CFile::ShowFile($res["FILE_ID"], 0, 
					$arParams["IMAGE_SIZE"], $arParams["IMAGE_SIZE"], true, "border=0", false);
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["ATTACH_FILE"] = $arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["ATTACH_IMG"];
			}
			$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
			$arResult["FILES"][$res["FILE_ID"]] = $res;
		}while ($res = $db_files->Fetch());
	}
}
/************** Message List/***************************************/
/************** Navigation *****************************************/
if (intVal($arResult["FORUM"]["FORUM_GROUP_ID"]) > 0):
	$PARENT_ID = intVal($arResult["FORUM"]["FORUM_GROUP_ID"]);
	while ($PARENT_ID > 0)
	{
		$res = $arResult["GROUPS"][$PARENT_ID];
		$res["URL"] = array(
			"GROUP" => CComponentEngine::MakePathFromTemplate(
				$arParams["URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID)), 
			"~GROUP" => CComponentEngine::MakePathFromTemplate(
				$arParams["~URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID)));
		$arResult["GROUP_NAVIGATION"][] = $res;
		$PARENT_ID = intVal($arResult["GROUPS"][$PARENT_ID]["PARENT_ID"]);
	}
	$arResult["GROUP_NAVIGATION"] = array_reverse($arResult["GROUP_NAVIGATION"]);
endif;
/************** Navigation/*****************************************/
/************** For custom templates *******************************/
if ($arParams["VERSION"] < 1)
{
	$arResult["topic_new"] = $arResult["URL"]["TOPIC_NEW"];
	$arResult["list"] = $arResult["URL"]["TOPIC_LIST"];
	$arResult["UserPermission"] = $arParams["PERMISSION"];
	$res = ShowActiveUser(array("PERIOD" => 600, "TITLE" => "", "FORUM_ID" => $arParams["FID"], "TOPIC_ID" => $arParams["TID"]));
	$res["SHOW_USER"] = "N";
	if ($res["NONE"] != "Y")
	{
		$arUser = array();
		if (is_array($res["USER"]) && count($res["USER"]) > 0)
		{
			foreach ($res["USER"] as $r)
			{
				$r["SHOW_NAME"] = $parser->wrap_long_words($r["SHOW_NAME"]);
				$r["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $r["UID"]));
				$arUser[] = $r;
			}
			if (count($arUser) > 0)
			{
				$res["SHOW_USER"] = "Y";
			}
			$res["USER"] = $arUser;
		}
	}
	$arResult["UserOnLine"] = $res;
	$arResult["bVarsFromForm"] = $bVarsFromForm;
	$arResult["CanUserAddTopic"] = $arResult["USER"]["RIGHTS"]["ADD_TOPIC"] == "Y";
}
/************** For custom templates/*******************************/
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(htmlspecialcharsEx($arResult["TOPIC"]["~TITLE"]));
if ($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
	CForumNew::ShowPanel($arParams["FID"], $arParams["TID"], false);
		
if ($arParams["SET_NAVIGATION"] != "N")
{
	foreach ($arResult["GROUP_NAVIGATION"] as $key => $res):
		$APPLICATION->AddChainItem($res["NAME"], $res["URL"]["~GROUP"]);
	endforeach;
	$APPLICATION->AddChainItem(htmlspecialchars($arResult["FORUM"]["~NAME"]), $arResult["URL"]["~TOPIC_LIST"]);
	$APPLICATION->AddChainItem(htmlspecialchars($arResult["TOPIC"]["~TITLE"]));
}

$this->IncludeComponentTemplate();

return array("FORUM" => $arResult["FORUM"], "bVarsFromForm" => ($bVarsFromForm ? "Y" : "N"), 
	"TID" => $arParams["TID"], "FID" => $arParams["FID"], "arFormParams" => $arResult);
?>