<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("PM_AUTH"));
	return 0;
endif;
// *****************************************************************************************
if(!function_exists("GetUserName"))
{
	function GetUserName($USER_ID)
	{
		$ar_res = false;
		if (IntVal($USER_ID)>0)
		{
			$db_res = CUser::GetByID(IntVal($USER_ID));
			$ar_res = $db_res->Fetch();
		}

		if (!$ar_res)
		{
			$db_res = CUser::GetByLogin($USER_ID);
			$ar_res = $db_res->Fetch();
		}

		$USER_ID = IntVal($ar_res["ID"]);
		$f_LOGIN = htmlspecialcharsex($ar_res["LOGIN"]);

		$forum_user = CForumUser::GetByUSER_ID($USER_ID);
		if (($forum_user["SHOW_NAME"]=="Y") && (strlen(trim($ar_res["NAME"]))>0 || strlen(trim($ar_res["LAST_NAME"]))>0))
		{
			return trim(htmlspecialcharsex($ar_res["NAME"])." ". htmlspecialcharsex($ar_res["LAST_NAME"]));
		}
		else
			return $f_LOGIN;
	}
}

	InitSorting();
	global $by, $order;

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["MID"] = intVal($arParams["MID"] > 0 ? $arParams["MID"] : $_REQUEST["MID"]);
	$mode = (!empty($arParams["mode"]) ? $arParams["mode"] : $_REQUEST["mode"]);
	if ($arParams["MID"] <= 0)
		$mode = "new";
	elseif (empty($mode))
		$mode = "edit";
	$arParams["UID"] = intVal(empty($arParams["UID"]) ? $_REQUEST["UID"] : $arParams["UID"]);
	$arParams["FID"] = intVal(empty($arParams["FID"]) ? $_REQUEST["FID"] : $arParams["FID"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"pm_list" => "PAGE_NAME=pm_list&FID=#FID#",
		"pm_read" => "PAGE_NAME=pm_read&FID=#FID#&MID=#MID#",
		"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&mode=#mode#",
		"pm_search" => "PAGE_NAME=pm_search",
		"pm_folder" => "PAGE_NAME=pm_folder",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "FID", "TID", "UID", "MID", "mode", BX_AJAX_PARAM_ID));
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		if (!empty($by))
		{
			$arParams["~URL_TEMPLATES_".strToUpper($URL)] = ForumAddPageParams($arParams["URL_TEMPLATES_".strToUpper($URL)], 
				array("by" => $by, "order" => $order), false, false);
		}
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);
/***************** STANDART ****************************************/
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "Y" ? "Y" : "N");
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

$arResult["MESSAGE"] = array();
if ($mode != "new"):
	if (!CForumPrivateMessage::CheckPermissions($arParams["MID"])):
		ShowError(GetMessage("F_ACCESS_DENIED"));
		return false;
	endif;
	$db_res = CForumPrivateMessage::GetById($arParams["MID"]);
	if ($db_res && ($res = $db_res->GetNext())):
		$arResult["MESSAGE"] = $res;
	else:
		ShowError(GetMessage("F_MESSAGE_NOT_FOUND"));
		return false;
	endif;
endif;

ForumSetLastVisit();

/********************************************************************
				Default params
********************************************************************/
$bVarsFromForm = false;
$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"], 
	array("FID" => $arParams["FID"], "MID" => $arParams["MID"], "mode" => $mode, "UID" => $arParams["UID"]));
$arResult["pm_list"] = CComponentEngine::MakePathFromTemplate(
	$arParams["URL_TEMPLATES_PM_LIST"], array("FID" => $arParams["FID"]));
$arResult["pm_search"] = CComponentEngine::MakePathFromTemplate(
	$arParams["URL_TEMPLATES_PM_SEARCH"], array());
$arResult["pm_search_for_js"] = ForumAddPageParams(CComponentEngine::MakePathFromTemplate(
	$arParams["~URL_TEMPLATES_PM_SEARCH"], array()), array("search_by_login"=>"#LOGIN#"), false, false);
$arParams["version"] = intVal(COption::GetOptionString("forum", "UsePMVersion", "2"));
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = "";
/********************************************************************
				Action
********************************************************************/
$action = strToLower($_REQUEST["action"]);
$arError = array();
if ($_SERVER['REQUEST_METHOD']=="POST" && !empty($action))
{
	$APPLICATION->ResetException();
	if (!check_bitrix_sessid()):
		$arError[] = array("id" => "BAD_SESSID", "text" => GetMessage("F_ERR_SESS_FINISH"));
	elseif ($action != "save" && $action != "send"):
	elseif ($action == "save" && !CForumPrivateMessage::CheckPermissions($arParams["MID"])):
		$arError[] = array("id" => "bad_permission","text" => GetMessage("PM_NOT_RIGHT"));
	elseif ($action == "save"):
		$arrVars = array(
			"POST_SUBJ" => $_REQUEST["POST_SUBJ"],
			"POST_MESSAGE" => $_REQUEST["POST_MESSAGE"],
			"USE_SMILES" => $_REQUEST["USE_SMILES"]);
		if(!CForumPrivateMessage::Update($arParams["MID"], $arrVars)):
			$str = $APPLICATION->GetException();
			if ($str && $str->GetString())
				$arError[] = array("id" => "bad_update","text" => $str->GetString());
			else 
				$arError[] = array("id" => "bad_update","text" => "Error!");
		endif;
	elseif ($action == "send"):
		$USER_INFO = array();
		if(strLen($_REQUEST["USER_ID"])>0)
		{
			if (intVal($_REQUEST["USER_ID"]) > 0)
				$USER_INFO = CForumUser::GetByUSER_ID($_REQUEST["USER_ID"]);
			if (empty($USER_INFO))
				$USER_INFO = CForumUser::GetByLogin($_REQUEST["USER_ID"]);
		}
		if (empty($USER_INFO)):
			$arError[] = array("id" => "bad_user_info","text" => str_replace("##", htmlspecialcharsEx($_REQUEST["USER_ID"]), GetMessage("PM_USER_NOT_FOUND")));
		else:
			$arrVars = array(
				"AUTHOR_ID" => $USER->GetID(),
				"POST_SUBJ" => $_REQUEST["POST_SUBJ"],
				"POST_MESSAGE" => $_REQUEST["POST_MESSAGE"],
				"USE_SMILES" => $_REQUEST["USE_SMILES"],
				"USER_ID" => $USER_INFO["USER_ID"],
				"COPY_TO_OUTBOX" => $_REQUEST["COPY_TO_OUTBOX"],
				"REQUEST_IS_READ" => $_REQUEST["REQUEST_IS_READ"]);
				
			$arParams["MID"] = CForumPrivateMessage::Send($arrVars);
			if (intVal($arParams["MID"]) <= 0)
			{
				$err = $APPLICATION->GetException();
				$arError[] = array("id" => "bad_send","text" => $err->GetString());
			}
			elseif ($arParams["version"] == 2)
			{
				$db_res = CForumPrivateMessage::GetListEx(array(), array("ID" => $arParams["MID"]));
				if (!($db_res && $res = $db_res->GetNext())):
					"";
				elseif (!empty($res["RECIPIENT_EMAIL"])):
					$event = new CEvent;
					$arSiteInfo = $event->GetSiteFieldsArray(SITE_ID);
					if (!isset(${"parser_".LANGUAGE_ID}))
						${"parser_".LANGUAGE_ID} = new textParser(LANGUAGE_ID);
						
					$POST_MESSAGE = ${"parser_".LANGUAGE_ID}->convert4mail(str_replace("#SERVER_NAME#", SITE_SERVER_NAME, $_REQUEST["POST_MESSAGE"]));
					$arFields = Array(
						"FROM_NAME" => $res["AUTHOR_NAME"],
						"FROM_USER_ID" => $USER->GetID(),
						"FROM_EMAIL" => $arSiteInfo["DEFAULT_EMAIL_FROM"],
						"TO_NAME" => $res["RECIPIENT_NAME"],
						"TO_USER_ID" => $res["RECIPIENT_ID"],
						"TO_EMAIL" => $res["RECIPIENT_EMAIL"],
						"SUBJECT" => $_REQUEST["POST_SUBJ"],
						"MESSAGE" => $POST_MESSAGE,
						"MESSAGE_DATE" => date("d.m.Y H:i:s"),
						"MESSAGE_LINK" => "http://".SITE_SERVER_NAME.CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_READ"], 
							array("FID" => "1", "MID" => $arParams["MID"]))
					);
					$event->Send("NEW_FORUM_PRIVATE_MESSAGE", SITE_ID, $arFields, "N");
				endif;
			}
			// Clear cache.
			BXClearCache(true, "/bitrix/forum/user/".$res["RECIPIENT_ID"]."/");
			$arComponentPath = array("bitrix:forum", "bitrix:forum.menu");
			foreach ($arComponentPath as $path)
			{
				$componentRelativePath = CComponentEngine::MakeComponentPath($path);
				$arComponentDescription = CComponentUtil::GetComponentDescr($path);
				if (strLen($componentRelativePath) <= 0 || !is_array($arComponentDescription)):
					continue;
				elseif (!array_key_exists("CACHE_PATH", $arComponentDescription)):
					continue;
				endif;
				$path = str_replace("//", "/", $componentRelativePath."/user".$res["RECIPIENT_ID"]);
				if ($arComponentDescription["CACHE_PATH"] == "Y")
					$path = "/".SITE_ID.$path;
				if (!empty($path))
					BXClearCache(true, $path);
			}
		endif;
	endif;

	if (empty($arError))
	{
		if ($action == "save")
		{
			LocalRedirect(
				CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_PM_READ"], 
					array("FID" => $arParams["FID"], "MID" => $arParams["MID"])));
		}
		elseif ($action == "send")
		{
			LocalRedirect(ForumAddPageParams(
				CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_LIST"], array("FID" => "2")),
				array("result" => "sent")));
		}
	}
	else 
	{
		$e = new CAdminException(array_reverse($arError));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		$err = $GLOBALS['APPLICATION']->GetException();
		$arResult["ERROR_MESSAGE"] = $err->GetString();
		$bVarsFromForm = true;
	}
}
/********************************************************************
				Action
********************************************************************/


/********************************************************************
				Data
********************************************************************/
$arResult["action"] = $mode=="edit" ? "save" : "send";
$arResult["count"] = CForumPrivateMessage::PMSize($USER->GetID(), COption::GetOptionInt("forum", "MaxPrivateMessages", 100));
$arResult["count"] = round($arResult["count"]*100);

$arResult["sessid"] = bitrix_sessid_post();
$arResult["FID"] = intVal($arParams["FID"]);
$arResult["MID"] = intVal($arParams["MID"]);
$arResult["mode"] = $mode;
$arResult["SystemFolder"] = FORUM_SystemFolder;

$resFolder = CForumPMFolder::GetList(array(), array("USER_ID" => $USER->GetID()));
$arResult["UserFolder"] = "N";
if (($resFolder) && ($resF = $resFolder->GetNext()))
{
	$arResult["UserFolder"] = array();
	do
	{
		$arResult["UserFolder"][$resF["ID"]] = $resF;
	}
	while ($resF = $resFolder->GetNext());
}
// *****************************************************************************************
// Info about current user
$arResult["CurrUser"]["SHOW_NAME"] = (trim($USER->GetFullName()) <= 0 ? $USER->GetLogin() : $USER->GetFullName());
$arResult["ForumPrintSmilesList"] = ForumPrintSmilesList(3, LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
$arResult["SMILES"] = CForumSmile::GetByType("S", LANGUAGE_ID);
$arResult["FolderName"] = ($arParams["FID"] <= $arResult["SystemFolder"]) ? GetMessage("PM_FOLDER_ID_".$arParams["FID"]) : 
	$arResult["UserFolder"][$arParams["FID"]]["TITLE"];
// *****************************************************************************************
$arResult["POST_VALUES"] = array();
if (!$bVarsFromForm && ($mode == "edit" || $mode=="reply"))
{
	$arResult["POST_VALUES"] = $arResult["MESSAGE"];
	if ($arParams["FID"] != 2)
		$arParams["FID"] = intVal($res["FOLDER_ID"]);
	if ($mode == "reply")
	{
		$arResult["POST_VALUES"]["POST_SUBJ"] = GetMessage("PM_REPLY").$arResult["POST_VALUES"]["POST_SUBJ"];
		$arResult["POST_VALUES"]["POST_MESSAGE"] = "[QUOTE]".$arResult["POST_VALUES"]["POST_MESSAGE"]."[/QUOTE]";
		$arResult["POST_VALUES"]["USER_ID"] = $arResult["POST_VALUES"]["AUTHOR_ID"];
		$arResult["POST_VALUES"]["USER_LOGIN"] = htmlspecialcharsEx(GetUserName($arResult["POST_VALUES"]["USER_ID"]));
	}
}
elseif ($bVarsFromForm)
{
	$arResult["POST_VALUES"]["POST_SUBJ"] = htmlspecialcharsEx($_REQUEST["POST_SUBJ"]);
	$arResult["POST_VALUES"]["POST_MESSAGE"] = htmlspecialcharsEx($_REQUEST["POST_MESSAGE"]);
	$arResult["POST_VALUES"]["USER_ID"] = htmlspecialcharsEx($_REQUEST["USER_ID"]);
	$arResult["POST_VALUES"]["USE_SMILES"] = ($_POST["USE_SMILES"] != "Y" ? "Y" : "N");
}
elseif ($arParams["UID"] > 0) 
{
	$arResult["POST_VALUES"]["USER_ID"] = intVal($arParams["UID"]);
}

if (intVal($arResult["POST_VALUES"]["USER_ID"]) > 0)
{
	$db_res = CForumUser::GetList(array(), array("USER_ID" => $arResult["POST_VALUES"]["USER_ID"], "SHOW_ABC" => ""));
	if ($db_res && ($res = $db_res->GetNext()))
	{
		$arResult["POST_VALUES"]["SHOW_NAME"] = array(
			"link" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["USER_ID"])),
			"text" => $res["SHOW_ABC"]);
	}
}
/********************************************************************
				/Data
********************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
{
	$APPLICATION->AddChainItem(GetMessage("PM_TITLE_NAV"), CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_FOLDER"], array()));
	if ($mode != "new")
		$APPLICATION->AddChainItem($arResult["FolderName"], CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_LIST"], 
			array("FID" => $arParams["FID"])));
	if ($mode != "edit")
		$APPLICATION->AddChainItem(GetMessage("PM_TITLE_NEW"));
	else 
		$APPLICATION->AddChainItem($arResult["MESSAGE"]["POST_SUBJ"], CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_READ"], 
			array("FID" => $arParams["FID"], "MID" => $arParams["MID"])));
}
/*******************************************************************/
if ($arParams["SET_TITLE"] != "N")
{
	if ($mode != "edit")
		$APPLICATION->SetTitle(GetMessage("PM_TITLE_NEW"));
	else 
		$APPLICATION->SetTitle(str_replace("#TITLE#", $arResult["MESSAGE"]["POST_SUBJ"], GetMessage("PM_TITLE_EDIT")));
}
// GetMessage("PM_FOLDER_ID_0");
// GetMessage("PM_FOLDER_ID_1");
// GetMessage("PM_FOLDER_ID_2");
// GetMessage("PM_FOLDER_ID_3");
// GetMessage("PM_FOLDER_ID_4");
/*******************************************************************/
	$this->IncludeComponentTemplate();
?>