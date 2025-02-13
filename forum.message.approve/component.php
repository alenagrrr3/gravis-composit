<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return false;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("F_AUTH"));
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intVal(intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["TID"] = intVal(intVal($arParams["TID"]) <= 0 ? $_REQUEST["TID"] : $arParams["TID"]);
	$arParams["action"] = strToUpper(trim($_REQUEST["ACTION"]));
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "",
		"list" => "PAGE_NAME=list&FID=#FID#",
		"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
		"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
		"message_appr" => "PAGE_NAME=message_appr&FID=#FID#&TID=#TID#", 
		"message_send" => "PAGE_NAME=message_send&UID=#UID#&TYPE=#TYPE#", 
		"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#", 
		"topic_new" => "PAGE_NAME=topic_new&FID=#FID#");
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
	$arParams["MESSAGES_PER_PAGE"] = intVal(intVal($arParams["MESSAGES_PER_PAGE"]) > 0 ? $arParams["MESSAGES_PER_PAGE"] : 
		COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);	
	
	$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);
	
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);

	// Data and data-time format
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
/***************** CACHE *******************************************/
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
$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);

if ($arParams["FID"] <= 0):
	ShowError(GetMessage("F_ERRROR_FORUM_EMPTY"));
	return false;
elseif (empty($arResult["FORUM"])):
	ShowError(GetMessage("F_ERRROR_FORUM_NOT_FOUND"));
	return false;
elseif (ForumCurrUserPermissions($arParams["FID"]) < "Q"):
	$APPLICATION->AuthForm(GetMessage("F_NO_PERMS"));
	return false;
endif;
/********************************************************************
				Default params
********************************************************************/
$arParams["PERMISSION"] = ForumCurrUserPermissions($arParams["FID"]);
$arResult["USER"] = array(
	"INFO" => array(),
	"PERMISSION" => $arParams["PERMISSION"],
	"RIGHTS" => array(
		"EDIT" => CForumNew::CanUserEditForum($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID()) ? "Y" : "N"), 
	"SUBSCRIBE" => array());

$arResult["TOPIC"] = array();
$arResult["MESSAGE_LIST"] = array();
$arResult["MESSAGE"] = array(); // out of date
$arResult["SHOW_RESULT"] = "N";
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = "";
$arResult["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"]));
$arResult["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
	array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => "s"));
$arResult["URL"] = array(
	"LIST" => $arResult["list"], 
	"~LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])), 
	"READ" => $arResult["read"], 
	"~READ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => "s")), 
	"MODERATE_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_APPR"], array("FID" => $arParams["FID"], "TID" => $arParams["TID"])), 
	"~MODERATE_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE_APPR"], array("FID" => $arParams["FID"], "TID" => $arParams["TID"])), 
);

$parser = new textParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
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
if ($arParams["TID"] > 0):
	$res = CForumTopic::GetByID($arParams["TID"]);
	if ($res)
		$arResult["TOPIC"] = $res;
	else 
		$arParams["TID"] = 0;
endif;
/********************************************************************
				Action
********************************************************************/
if (check_bitrix_sessid())
{
	$arError = array(); 
	$strOKMessage = "";
	
	if ($_SERVER['REQUEST_METHOD'] == "POST"):
		$message = (empty($_POST["MID_ARRAY"]) ? $_POST["MID"] : $_POST["MID_ARRAY"]);
		$message = (empty($message) ? $_POST["message_id"] : $message);
		$action = strToUpper($_POST["ACTION"]);
	else:
		$message = (empty($_GET["MID_ARRAY"]) ? $_GET["MID"] : $_GET["MID_ARRAY"]);
		$message = (empty($message) ? $_GET["message_id"] : $message);
		$action = strToUpper($_GET["ACTION"]);
	endif;
	if (!is_array($message))
		$message = explode(",", $message);
	$message = ForumMessageExistInArray($message);
	
	
	if (!$message)
		$arError[] = array("id" => "bad_data", "text" => GetMessage("F_NO_MESSAGE"));
	if (!in_array($action, array("DEL", "SHOW", "HIDE")))
		$arError[] = array("id" => "bad_action", "text" => GetMessage("F_NO_ACTION"));
	if (empty($arError))
	{
		$strErrorMessage = "";
		switch ($action)
		{
			case "DEL":
				ForumDeleteMessageArray($message, $strErrorMessage, $strOKMessage);
			break;
			case "SHOW":
			case "HIDE":
				ForumModerateMessageArray($message, $action, $strErrorMessage, $strOKMessage);
			break;
		}
		if (empty($strErrorMessage))
		{
			$res = CForumMessage::GetList(array("ID" => "ASC"), array("APPROVED" => "N"));
			if ($res <= 0)
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])));
			else
				LocalRedirect($arResult["URL"]["MODERATE_MESSAGE"]);
		}
		else 
			$arError[] = array("id" => "bad_action", "text" => $strErrorMessage);
	}
	if (!empty($arError)):
		$e = new CAdminException(array_reverse($arError));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		$err = $GLOBALS['APPLICATION']->GetException();
		$arResult["ERROR_MESSAGE"] .= $err->GetString();
	endif;
	$arResult["OK_MESSAGE"] = $strOKMessage;
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arFilter = array("APPROVED" => "N", "FORUM_ID" => $arParams["FID"]);
if ($arParams["TID"] > 0)	
	$arFilter["TOPIC_ID"] = $arParams["TID"];
$db_Message = CForumMessage::GetListEx(array("ID" => "ASC"), $arFilter);
$db_Message->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
$db_Message->NavStart($arParams["MESSAGES_PER_PAGE"], false);
$arResult["NAV_RESULT"] = $db_Message;
$arResult["NAV_STRING"] = $db_Message->GetPageNavStringEx($navComponentObject, GetMessage("F_TITLE_NAV"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
if ($db_Message && ($res = $db_Message->GetNext()))
{
	$iCount = 1;
	$arResult["SHOW_RESULT"] = "Y";
	do
	{
		$res["NUMBER"] = $iCount++;
		// data
		$res["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat()));
		$res["EDIT_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["EDIT_DATE"], CSite::GetDateFormat()));
		// text
		$arAllow["SMILES"] = ($res["USE_SMILES"] == "Y" ? $arResult["FORUM"]["ALLOW_SMILES"] : "N");
		$res["~POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
		$res["POST_MESSAGE_TEXT"] = $parser->convert($res["~POST_MESSAGE_TEXT"], $arAllow);
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
		$res["ATTACH_IMG"] = ""; $res["FILES"] = array();
		$res["~ATTACH_FILE"] = array(); $res["ATTACH_FILE"] = array();
/************** Panels *********************************************/
	$res["PANELS"] = array(
		"DELETE" => $arResult["USER"]["RIGHTS"]["EDIT"],
		"EDIT" => $arResult["USER"]["RIGHTS"]["EDIT"]);
	if ($res["PANELS"]["EDIT"] != "Y" && $USER->IsAuthorized() && $res["AUTHOR_ID"] == $USER->GetId()):
		if (COption::GetOptionString("forum", "USER_EDIT_OWN_POST", "N") == "Y"):
			$res["PANELS"]["EDIT"] = "Y";
		else:
			// get last message in topic
			// $arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] == intVal($res["ID"])
		endif;
	endif;
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
			"MODERATE" => ForumAddPageParams($arResult["URL"]["~MODERATE_MESSAGE"], 
				array("MID" => $res["ID"], "ACTION" => "SHOW"))."&amp;".bitrix_sessid_get(), 
			"DELETE" => ForumAddPageParams($arResult["URL"]["~MODERATE_MESSAGE"], 
				array("MID" => $res["ID"], "ACTION" => "DEL"))."&amp;".bitrix_sessid_get(), 
			"EDIT" => ForumAddPageParams(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_NEW"], 
				array("FID" => $arParams["FID"])), array("TID" => $arParams["TID"], "MID" => $res["ID"], "MESSAGE_TYPE" => "EDIT")
				)."&amp;".bitrix_sessid_get()
			);
			
		$res["profile_view"] = $res["URL"]["AUTHOR"];
		$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
	}while ($res = $db_Message->GetNext());
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
/************** For custom templates *******************************/
$arResult["MESSAGE"] = $arResult["MESSAGE_LIST"];
foreach ($arResult["MESSAGE"] as $key => $val):
	if (strLen($val["AVATAR"]) > 0)
		$arResult["MESSAGE"][$key]["AVATAR"] = $val["AVATAR"]["HTML"];
	
endforeach;
$arResult["sessid"] = bitrix_sessid_post();
$arResult["PARSER"] = $parser;
/********************************************************************
				/Data
********************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
{
	$APPLICATION->AddChainItem($arResult["FORUM"]["NAME"], $arResult["URL"]["~LIST"]);
	if ($arParams["TID"] > 0)
		$APPLICATION->AddChainItem($arResult["TOPIC"]["TITLE"], $arResult["URL"]["~READ"]);
}
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("F_TITLE"));
if ($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
	CForumNew::ShowPanel($arParams["FID"], $arParams["TID"], false);
/*******************************************************************/
	$this->IncludeComponentTemplate();
/*******************************************************************/
?>