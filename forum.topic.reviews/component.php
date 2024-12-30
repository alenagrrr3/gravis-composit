<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (!CModule::IncludeModule("iblock")):
 	ShowError(GetMessage("F_NO_MODULE_IBLOCK"));
	return 0;
elseif (intVal($arParams["FORUM_ID"]) <= 0):
 	ShowError(GetMessage("F_ERR_FID_EMPTY"));
	return 0;
elseif (intVal($arParams["ELEMENT_ID"]) <= 0):
 	ShowError(GetMessage("F_ERR_EID_EMPTY"));
	return 0;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FORUM_ID"] = intVal($arParams["FORUM_ID"]);
	$arParams["IBLOCK_ID"] = intVal($arParams["IBLOCK_ID"]);
	$arParams["ELEMENT_ID"] = intVal(intVal($arParams["ELEMENT_ID"])<=0 ? $GLOBALS["ID"] : $arParams["ELEMENT_ID"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"detail" => "PAGE_NAME=detail&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (empty($arParams["URL_TEMPLATES_".strToUpper($URL)]))
			continue;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
$arParams["POST_FIRST_MESSAGE"] = ($arParams["POST_FIRST_MESSAGE"] == "Y" ? "Y" : "N");
$arParams["POST_FIRST_MESSAGE_TEMPLATE"] = trim($arParams["POST_FIRST_MESSAGE_TEMPLATE"]);
if (empty($arParams["POST_FIRST_MESSAGE_TEMPLATE"]))
	$arParams["POST_FIRST_MESSAGE_TEMPLATE"] = "#IMAGE# \n [url=#LINK#]#TITLE#[/url]\n\n#BODY#";
$arParams["SUBSCRIBE_AUTHOR_ELEMENT"] = ($arParams["SUBSCRIBE_AUTHOR_ELEMENT"] == "Y" ? "Y" : "N");
$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);
$arParams["MESSAGES_PER_PAGE"] = intVal($arParams["MESSAGES_PER_PAGE"] > 0 ? $arParams["MESSAGES_PER_PAGE"] : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")):$arParams["DATE_TIME_FORMAT"]);
$arParams["USE_CAPTCHA"] = ($arParams["USE_CAPTCHA"] == "Y" ? "Y" : "N");
$arParams["PREORDER"] = ($arParams["PREORDER"] == "Y" ? "Y" : "N");
$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
/***************** STANDART ****************************************/
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
$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");
$arError = array();
$arNote = array();
$arResult["FORUM"] = CForumNew::GetByIDEx($arParams["FORUM_ID"], SITE_ID);
$arResult["ELEMENT"] = array();
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = ($_REQUEST["result"] == "reply" ? GetMessage("COMM_COMMENT_OK") : (
	$_REQUEST["result"] == "not_approved" ? GetMessage("COMM_COMMENT_OK_AND_NOT_APPROVED") : ""));
unset($_GET["result"]); unset($GLOBALS["HTTP_GET_VARS"]["result"]);
DeleteParam(array("result"));
$arResult["USER"] = array(
	"PERMISSION" => ForumCurrUserPermissions($arParams["FORUM_ID"]), 
	"SHOWED_NAME" => GetMessage("F_GUEST"));
if ($USER->IsAuthorized()):
	$arResult["USER"]["SHOWED_NAME"] = trim($_SESSION["FORUM"]["SHOW_NAME"] == "Y" ? $GLOBALS["USER"]->GetFullName() :	$GLOBALS["USER"]->GetLogin());
	$arResult["USER"]["SHOWED_NAME"] = trim(!empty($arResult["USER"]["SHOWED_NAME"]) ? $arResult["USER"]["SHOWED_NAME"] : $GLOBALS["USER"]->GetLogin());
endif;
$arResult["MESSAGES"] = array();
$arResult["MESSAGE_VIEW"] = array();
$arResult["MESSAGE"] = array();
$arResult["FILES"] = array();
// PARSER
$parser = new textParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"], $arParams["CACHE_TIME"]);
$parser->image_params["width"] = $arParams["IMAGE_SIZE"];
$parser->image_params["height"] = $arParams["IMAGE_SIZE"];
$arResult["PARSER"] = $parser;
// FORUM
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
$_REQUEST["FILES"] = is_array($_REQUEST["FILES"]) ? $_REQUEST["FILES"] : array();
$_REQUEST["FILES_TO_UPLOAD"] = is_array($_REQUEST["FILES_TO_UPLOAD"]) ? $_REQUEST["FILES_TO_UPLOAD"] : array();
CPageOption::SetOptionString("main", "nav_page_in_session", "N");

// ELEMENT 
$arSelectedFields = array("IBLOCK_ID", "ID", "NAME", "TAGS", "CODE", "IBLOCK_SECTION_ID", "DETAIL_PAGE_URL", 
		"CREATED_BY", "PREVIEW_PICTURE", "PREVIEW_TEXT", "PROPERTY_FORUM_TOPIC_ID", "PROPERTY_FORUM_MESSAGE_CNT");
$arIblock = array();
$cache_id = "forum_iblock_".$arParams["ELEMENT_ID"];
$cache_path = $cache_path_main."iblock".$arParams["ELEMENT_ID"];
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	if (is_array($res["arIblock"]) && (count($res["arIblock"]) > 0) && ($res["arIblock"]["ID"] == $arParams["ELEMENT_ID"]))
		$arIblock = $res["arIblock"];
}
if (!is_array($arIblock) || ($arIblock["ID"] != $arParams["ELEMENT_ID"]))
{
	$arFilter = array("ID" => $arParams["ELEMENT_ID"]);
	if (intVal($arParams["IBLOCK_ID"]) > 0)
		$arFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
	$db_res = CIBlockElement::GetList(array(), $arFilter, 
		false, false, $arSelectedFields);
	if ($db_res && $res = $db_res->GetNext())
	{
		$arIblock = $res;
	}
	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(array("arIblock"=>$arIblock));
	}
}
$arResult["ELEMENT"] = $arIblock;
/********************************************************************
				/Default values
********************************************************************/

if (empty($arResult["FORUM"])):
 	ShowError(str_replace("#FORUM_ID#", $arParams["FORUM_ID"], GetMessage("F_ERR_FID_IS_NOT_EXIST")));
	return 0;
elseif (empty($arResult["ELEMENT"])):
 	ShowError(str_replace("#ELEMENT_ID#", $arParams["ELEMENT_ID"], GetMessage("F_ERR_EID_IS_NOT_EXIST")));
	return 0;
elseif ($arResult["USER"]["PERMISSION"] <= "A"):
	return 0;
endif;

/********************************************************************
				Actions
********************************************************************/
ForumSetLastVisit($arParams["FORUM_ID"], 0);
if ($_REQUEST["save_product_review"] == "Y" && empty($_REQUEST["preview_comment"]))
{
	$FORUM_TOPIC_ID = 0;
	$arProperties = array();
	$needProperty = array();
	$strErrorMessage = "";
		
	// 1.1. Check gross errors message data
	if (!check_bitrix_sessid())
	{
		$arError[] = array(
			"code" => "session time is up",
			"title" => GetMessage("F_ERR_SESSION_TIME_IS_UP"));
	}
	// 1.2 Check Post Text
	elseif (strLen($_REQUEST["REVIEW_TEXT"]) < 3)
	{
		$arError[] = array(
			"code" => "post is empty",
			"title" => GetMessage("F_ERR_NO_REVIEW_TEXT"));
	}
	// 1.3 Check Permission
	elseif (ForumCurrUserPermissions($arParams["FORUM_ID"]) <= "E")
	{
		$arError[] = array(
			"code" => "access denied",
			"title" => GetMessage("F_ERR_NOT_RIGHT_FOR_ADD"));
	}
	// 1.4 Check Captcha
	elseif (!$GLOBALS["USER"]->IsAuthorized() && $arParams["USE_CAPTCHA"]=="Y" && $arResult["FORUM"]["USE_CAPTCHA"] != "Y")
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");

		$cpt = new CCaptcha();
		if (strlen($_REQUEST["captcha_code"]) > 0)
		{
			$captchaPass = COption::GetOptionString("main", "captcha_password", "");
			if (!$cpt->CheckCodeCrypt($_POST["captcha_word"], $_POST["captcha_code"], $captchaPass))
			{
				$arError[] = array(
					"code" => "bad captcha",
					"title" => GetMessage("POSTM_CAPTCHA"));
			}
		}
		else
		{
			if (!$cpt->CheckCode($_POST["captcha_word"], 0))
				$arError[] = array(
					"code" => "captcha is empty",
					"title" => GetMessage("POSTM_CAPTCHA"));
		}
	}
	
	if (empty($arError))
	{
	// 1.4 Add iblock properties
		$needProperty = array();
		$PRODUCT_IBLOCK_ID = intVal($arResult["ELEMENT"]["IBLOCK_ID"]);
		$PRODUCT_NAME = Trim($arResult["ELEMENT"]["~NAME"]);
		$FORUM_TOPIC_ID = intVal($arResult["ELEMENT"]["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
		$FORUM_MESSAGE_CNT = intVal($arResult["ELEMENT"]["PROPERTY_FORUM_MESSAGE_CNT_VALUE"]);
		
		if ($FORUM_TOPIC_ID <= 0)
		{
			$db_res = CIBlockElement::GetProperty($arResult["ELEMENT"]["IBLOCK_ID"], $arResult["ELEMENT"]["ID"], false, false, array("CODE" => "FORUM_TOPIC_ID"));
			if (!($db_res && $res = $db_res->Fetch()))
				$needProperty[] = "FORUM_TOPIC_ID";	
		}
		if ($FORUM_MESSAGE_CNT <= 0)
		{
			$db_res = CIBlockElement::GetProperty($arResult["ELEMENT"]["IBLOCK_ID"], $arResult["ELEMENT"]["ID"], false, false, array("CODE" => "FORUM_MESSAGE_CNT"));
			if (!($db_res && $res = $db_res->Fetch()))
				$needProperty[] = "FORUM_MESSAGE_CNT";	
		}

		if (!empty($needProperty))
		{
			$obProperty = new CIBlockProperty;
			$res = true;
			foreach ($needProperty as $nameProperty)
			{
				$sName = trim($sName == "FORUM_TOPIC_ID" ? GetMessage("F_FORUM_TOPIC_ID") : GetMessage("F_FORUM_MESSAGE_CNT"));
				$sName = (empty($sName) ? $nameProperty : $sName);
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $PRODUCT_IBLOCK_ID,
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "N",
					"MULTIPLE" => "N",
					"NAME" => $sName,
					"CODE" => $nameProperty));

				if($res)
					${strToUpper($nameProperty)} = 0;
			}
		}
	// 1.5 Set NULL for topic_id if it was deleted
		if ($FORUM_TOPIC_ID > 0)
		{
			$arTopic = CForumTopic::GetByID($FORUM_TOPIC_ID);
			if (!$arTopic || !is_array($arTopic) || count($arTopic) <= 0 || $arTopic["FORUM_ID"] != $arParams["FORUM_ID"])
			{
				CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, 0, "FORUM_TOPIC_ID");
				$FORUM_TOPIC_ID = 0;
			}
		}
	// 1.6 Create New topic and add messages
		$DB->StartTransaction();
		$MID = 0; $TID = 0;
		if ($FORUM_TOPIC_ID <= 0)
		{
			if ($arParams["POST_FIRST_MESSAGE"] == "Y")
			{
	// 1.6.a Create New topic
				$arUserStart = array(
					"ID" => intVal($arResult["ELEMENT"]["~CREATED_BY"]),
					"NAME" => GetMessage("F_GUEST"));
				if ($arUserStart["ID"] > 0)
				{
					$res = array();
					$db_res = CForumUser::GetListEx(array(), array("USER_ID" => $arResult["ELEMENT"]["~CREATED_BY"]));
					if ($db_res && $res = $db_res->Fetch())
					{
						$res["FORUM_USER_ID"] = intVal($res["ID"]);
						$res["ID"] = $res["USER_ID"];
					}
					else
					{
						$db_res = CUser::GetByID($arResult["ELEMENT"]["~CREATED_BY"]);
						if ($db_res && $res = $db_res->Fetch())
						{
							$res["SHOW_NAME"] = "Y"; 
							$res["USER_PROFILE"] = "N"; 
						}
					}
					if (!empty($res))
					{
						$arUserStart = $res;
						$sName = ($res["SHOW_NAME"] == "Y" ? trim($res["NAME"]." ".$res["LAST_NAME"]) : "");
						$arUserStart["NAME"] = (empty($sName) ? trim($res["LOGIN"]) : $sName);
					}
				}
				if (empty($arUserStart["NAME"]))
					$arUserStart["NAME"] = GetMssage("F_GUEST");
					
				$arFields = Array(
					"TITLE"			=> $arResult["ELEMENT"]["~NAME"],
					"TAGS"			=> $arResult["ELEMENT"]["~TAGS"],
					"FORUM_ID"		=> $arParams["FORUM_ID"],
					"USER_START_ID"	=> $arUserStart["ID"],
					"USER_START_NAME" => $arUserStart["NAME"],
					"LAST_POSTER_NAME" => $arUserStart["NAME"],
					"APPROVED" => "Y");
				$TID = CForumTopic::Add($arFields);
	// 1.6.b Add post as new message 
				if (intVal($TID)<=0)
				{
					$arError[] = array(
						"code" => "topic is not created",
						"title" => GetMessage("F_ERR_ADD_TOPIC"));
				}
				else 
				{
					$sImage = "";
					$url = (empty($arParams["URL_TEMPLATES_DETAIL"]) ? $arResult["ELEMENT"]["DETAIL_PAGE_URL"] : $arParams["URL_TEMPLATES_DETAIL"]);
					$arSection = array();
					if (strpos($arParams["URL_TEMPLATES_DETAIL"], "#SECTION_CODE#") !== false && intVal($arResult["ELEMENT"]["IBLOCK_SECTION_ID"]) > 0)
					{
						$db_res = CIBlockSection::GetList(array(), array("ID" => $arResult["ELEMENT"]["IBLOCK_SECTION_ID"]), false, array("ID", "NAME", "CODE"));
						if ($db_res && $res = $db_res->Fetch())
							$arSection = $res;
					}
					$url = str_replace(
						array("#ELEMENT_ID#", "#ID#", "#ELEMENT_CODE#", "#SECTION_ID#", "#SECTION_CODE#"), 
						array($arResult["ELEMENT"]["ID"], $arResult["ELEMENT"]["ID"], $arResult["ELEMENT"]["CODE"], 
									$arResult["ELEMENT"]["IBLOCK_SECTION_ID"], $arSection["CODE"]), $url);
					if (intVal($arResult["ELEMENT"]["PREVIEW_PICTURE"]) > 0)
					{
						$arImage = CFile::GetFileArray($arResult["ELEMENT"]["PREVIEW_PICTURE"]);
						if (!empty($arImage))
						{
							$sImage = ($arResult["FORUM"]["ALLOW_IMG"] == "Y" ? "[IMG]".$arImage["SRC"]."[/IMG]" : $arImage["SRC"]);
						}
					}

					$arFields = Array(
						"POST_MESSAGE" => str_replace(
							array("#IMAGE#", "#TITLE#", "#BODY#", "#LINK#"),
							array($sImage, $arResult["ELEMENT"]["~NAME"], $arResult["ELEMENT"]["~PREVIEW_TEXT"], $url), 
							$arParams["POST_FIRST_MESSAGE_TEMPLATE"]),
						"AUTHOR_ID" => $arUserStart["ID"],
						"AUTHOR_NAME" => $arUserStart["NAME"],
						"FORUM_ID" => $arParams["FORUM_ID"],
						"TOPIC_ID" => $TID,
						"APPROVED" => "Y",
						"NEW_TOPIC" => "Y",
						"PARAM1" => "IB", 
						"PARAM2" => intVal($arParams["ELEMENT_ID"]));

					$MID = CForumMessage::Add($arFields, false, array("SKIP_INDEXING" => "Y", "SKIP_STATISTIC" => "Y"));
					
					if (intVal($MID) <= 0)
					{
						$arError[] = array(
							"code" => "message is not added",
							"title" => GetMessage("F_ERR_ADD_MESSAGE"));
						CForumTopic::Delete($TID);
						$TID = 0;
					}
					elseif ($arParams["SUBSCRIBE_AUTHOR_ELEMENT"] == "Y" && intVal($arResult["ELEMENT"]["~CREATED_BY"]) > 0)
					{
						if ($arUserStart["USER_PROFILE"] == "N")
						{
							$arUserStart["FORUM_USER_ID"] = CForumUser::Add(array("USER_ID" => $arResult["ELEMENT"]["~CREATED_BY"]));
						}
						if (intVal($arUserStart["FORUM_USER_ID"]) > 0)
						{
							CForumSubscribe::Add(array(
								"USER_ID" => $arResult["ELEMENT"]["~CREATED_BY"],
								"FORUM_ID" => $arParams["FORUM_ID"],
								"SITE_ID" => SITE_ID,
								"TOPIC_ID" => $TID, 
								"NEW_TOPIC_ONLY" => "N"));
							BXClearCache(true, "/bitrix/forum/user/".$arResult["ELEMENT"]["~CREATED_BY"]."/subscribe/"); // Sorry, Max.
						}
					}
				}
	// 1.6.c Add comments
				if ($TID > 0 && $MID > 0 && empty($arError))
				{
					$arFieldsG = array(
						"POST_MESSAGE" => $_POST["REVIEW_TEXT"],
						"AUTHOR_NAME" => $_POST["REVIEW_AUTHOR"],
						"AUTHOR_EMAIL" => $_POST["REVIEW_EMAIL"],
						"USE_SMILES" => $_POST["REVIEW_USE_SMILES"],
						"PARAM2" => intVal($arParams["ELEMENT_ID"]));
					if (!empty($_FILES["REVIEW_ATTACH_IMG"]))
					{
						$arFieldsG["ATTACH_IMG"] = $_FILES["REVIEW_ATTACH_IMG"]; 
					}
					else
					{
						$arFiles = array();
						if (!empty($_REQUEST["FILES"]))
						{
							foreach ($_REQUEST["FILES"] as $key):
								$arFiles[$key] = array("FILE_ID" => $key);
								if (!in_array($key, $_REQUEST["FILES_TO_UPLOAD"]))
									$arFiles[$key]["del"] = "Y";
							endforeach;
						}
						if (!empty($_FILES))
						{
							$res = array();
							foreach ($_FILES as $key => $val):
								if (substr($key, 0, strLen("FILE_NEW")) == "FILE_NEW" && !empty($val["name"])):
									$arFiles[] = $_FILES[$key];
								endif;
							endforeach;
						}
						if (!empty($arFiles))
							$arFieldsG["FILES"] = $arFiles; 
					}
					$MID = ForumAddMessage("REPLY", $arParams["FORUM_ID"], $TID, 0, $arFieldsG, $strErrorMessage, $strOKMessage, false, $_POST["captcha_word"], 0, $_POST["captcha_code"]);
				}
			}
			else
			{
	// 1.6.0.a Sipmly add message & create new topic
				$arFieldsG = array(
					"POST_MESSAGE" => $_POST["REVIEW_TEXT"],
					"AUTHOR_NAME" => $_POST["REVIEW_AUTHOR"],
					"AUTHOR_EMAIL" => $_POST["REVIEW_EMAIL"],
					"USE_SMILES" => $_POST["REVIEW_USE_SMILES"],
					"PARAM2" => intVal($arParams["ELEMENT_ID"]), 
					"TITLE" => $PRODUCT_NAME);
					if (!empty($_FILES["REVIEW_ATTACH_IMG"]))
					{
						$arFieldsG["ATTACH_IMG"] = $_FILES["REVIEW_ATTACH_IMG"]; 
					}
					else
					{
						$arFiles = array();
						if (!empty($_REQUEST["FILES"]))
						{
							foreach ($_REQUEST["FILES"] as $key):
								$arFiles[$key] = array("FILE_ID" => $key);
								if (!in_array($key, $_REQUEST["FILES_TO_UPLOAD"]))
									$arFiles[$key]["del"] = "Y";
							endforeach;
						}
						if (!empty($_FILES))
						{
							$res = array();
							foreach ($_FILES as $key => $val):
								if (substr($key, 0, strLen("FILE_NEW")) == "FILE_NEW" && !empty($val["name"])):
									$arFiles[] = $_FILES[$key];
								endif;
							endforeach;
						}
						if (!empty($arFiles))
							$arFieldsG["FILES"] = $arFiles; 
					}
				$MID = ForumAddMessage("NEW", $arParams["FORUM_ID"], 0, 0, $arFieldsG, $strErrorMessage, $strOKMessage, false, $_POST["captcha_word"], 0, $_POST["captcha_code"]);
				if ($MID > 0 && empty($strErrorMessage))
				{
					$res = CForumMessage::GetByID($MID);
					if (!empty($res) && is_array($res))
						$TID = intVal($res["TOPIC_ID"]);
				}
			}
			$FORUM_TOPIC_ID = $TID;
		}
		else 
		{
			$arFieldsG = array(
				"POST_MESSAGE" => $_POST["REVIEW_TEXT"],
				"AUTHOR_NAME" => trim($_POST["REVIEW_AUTHOR"]),
				"AUTHOR_EMAIL" => $_POST["REVIEW_EMAIL"],
				"USE_SMILES" => $_POST["REVIEW_USE_SMILES"],
				"PARAM2" => intVal($arParams["ELEMENT_ID"]));
			if (!empty($_FILES["REVIEW_ATTACH_IMG"]))
			{
				$arFieldsG["ATTACH_IMG"] = $_FILES["REVIEW_ATTACH_IMG"]; 
			}
			else
			{
				$arFiles = array();
				if (!empty($_REQUEST["FILES"]))
				{
					foreach ($_REQUEST["FILES"] as $key):
						$arFiles[$key] = array("FILE_ID" => $key);
						if (!in_array($key, $_REQUEST["FILES_TO_UPLOAD"]))
							$arFiles[$key]["del"] = "Y";
					endforeach;
				}
				if (!empty($_FILES))
				{
					$res = array();
					foreach ($_FILES as $key => $val):
						if (substr($key, 0, strLen("FILE_NEW")) == "FILE_NEW" && !empty($val["name"])):
							$arFiles[] = $_FILES[$key];
						endif;
					endforeach;
				}
				if (!empty($arFiles))
					$arFieldsG["FILES"] = $arFiles; 
			}
			$MID = ForumAddMessage("REPLY", $arParams["FORUM_ID"], $FORUM_TOPIC_ID, 0, $arFieldsG, $strErrorMessage, $strOKMessage, false, $_POST["captcha_word"], 0, $_POST["captcha_code"]);
		}
		
		if ($MID <= 0)
		{
			$arError[] = array(
				"code" => "message is not added",
				"title" => (empty($strErrorMessage) ? GetMessage("F_ERR_ADD_MESSAGE") : $strErrorMessage));
		}
	// 1.7 Update Iblock Property
		if ($TID > 0):
			CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, intVal($TID), "FORUM_TOPIC_ID");
			ForumClearComponentCache($componentName);
		endif;
		if ($MID > 0 && empty($arError))
		{
			$FORUM_MESSAGE_CNT = CForumMessage::GetList(array(), array("TOPIC_ID" => $FORUM_TOPIC_ID, "APPROVED" => "Y", "!PARAM1" => "IB"), true);
			CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, intVal($FORUM_MESSAGE_CNT), "FORUM_MESSAGE_CNT");
	// 1.8 Commit
			$DB->Commit();
			$strOKMessage = GetMessage("COMM_COMMENT_OK");
			ForumClearComponentCache($componentName);
			// SUBSCRIBE
			if ($_REQUEST["TOPIC_SUBSCRIBE"] == "Y")
				ForumSubscribeNewMessagesEx($arParams["FORUM_ID"], $FORUM_TOPIC_ID, "N", $strErrorMessage, $strOKMessage);
			if ($_REQUEST["FORUM_SUBSCRIBE"] == "Y")
				ForumSubscribeNewMessagesEx($arParams["FORUM_ID"], 0, "N", $strErrorMessage, $strOKMessage);
				
			if ($_REQUEST["TOPIC_SUBSCRIBE"] == "Y" || $_REQUEST["FORUM_SUBSCRIBE"] == "Y")
				BXClearCache(true, "/bitrix/forum/user/".$GLOBALS["USER"]->GetID()."/subscribe/");
			$arResult["FORUM_TOPIC_ID"] = intVal($FORUM_TOPIC_ID);
		}
		else 
		{
			$DB->Rollback();
		}
	}

	if (empty($arError))
	{
		$strURL = (!empty($_REQUEST["back_page"]) ? $_REQUEST["back_page"] : $APPLICATION->GetCurPageParam("", 
			array("MID", "SEF_APPLICATION_CUR_PAGE_URL", BX_AJAX_PARAM_ID, "result")));
		$strURL = ForumAddPageParams($strURL, array("MID" => $MID, "result" => 
			($arResult["FORUM"]["MODERATION"] != "Y" || CForumNew::CanUserModerateForum($arParams["FORUM_ID"], $USER->GetUserGroupArray()) ? "reply" : "not_approved")
			))."#message".$MID;
	
		LocalRedirect($strURL);
	}
}
elseif ($_REQUEST["save_product_review"] == "Y" && !empty($_REQUEST["preview_comment"]) && check_bitrix_sessid())
{
	$arAllow["SMILES"] = ($_POST["REVIEW_USE_SMILES"] !="Y" ? "N" : $arResult["FORUM"]["ALLOW_SMILES"]);
	$arResult["MESSAGE_VIEW"] = array(
		"POST_MESSAGE_TEXT" => $parser->convert($_POST["REVIEW_TEXT"], $arAllow), 
		"AUTHOR_NAME" => htmlspecialcharsEx($arResult["USER"]["SHOWED_NAME"]), 
		"AUTHOR_ID" => intVal($USER->GetID()),
		"AUTHOR_URL" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $USER->GetID())), 
		"POST_DATE" => CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], time()), 
		"FILES" => array());
	

	$arFields = array(
			"FORUM_ID" => intVal($arParams["FORUM_ID"]), 
			"TOPIC_ID" => 0, 
			"MESSAGE_ID" => 0, 
			"USER_ID" => intVal($GLOBALS["USER"]->GetID()));
	$arFiles = array();
	$arFilesExists = array();
	$res = array();
	
	foreach ($_FILES as $key => $val):
		if ((substr($key, 0, strLen("FILE_NEW")) == "FILE_NEW") && !empty($val["name"])):
			$arFiles[] = $_FILES[$key];
		endif;
	endforeach;
	foreach ($_REQUEST["FILES"] as $key => $val) 
	{
		if (!in_array($val, $_REQUEST["FILES_TO_UPLOAD"]))
		{
			$arFiles[$val] = array("FILE_ID" => $val, "del" => "Y");
			unset($_REQUEST["FILES"][$key]);
			unset($_REQUEST["FILES_TO_UPLOAD"][$key]);
		}
		else 
		{
			$arFilesExists[$val] = array("FILE_ID" => $val);
		}
	}
	if (!empty($arFiles))
	{
		$res = CForumFiles::Save($arFiles, $arFields);
		$res1 = $GLOBALS['APPLICATION']->GetException();
		if ($res1):
			$arError[] = array(
				"code" => "file upload error",
				"title" => $res1->GetString());
		endif;
	}
	$res = is_array($res) ? $res : array();
	foreach ($res as $key => $val)
		$arFilesExists[$key] = $val;
	$arFilesExists = array_keys($arFilesExists);
	sort($arFilesExists);
	$arResult["MESSAGE_VIEW"]["FILES"] = $_REQUEST["FILES"] = $arFilesExists;	
}

$strErrorMessage = "";
foreach ($arError as $res)
	$strErrorMessage .= (empty($res["title"]) ? $res["code"] : $res["title"]);
	
$arResult["ERROR_MESSAGE"] = $strErrorMessage;
$arResult["OK_MESSAGE"] .= $strOKMessage;
/********************************************************************
				/Actions
********************************************************************/

/********************************************************************
				Input params
********************************************************************/
/************** URL ************************************************/
if (empty($arParams["~URL_TEMPLATES_READ"]) && !empty($arResult["FORUM"]["PATH2FORUM_MESSAGE"]))
	$arParams["~URL_TEMPLATES_READ"] = $arResult["FORUM"]["PATH2FORUM_MESSAGE"];
elseif (empty($arParams["~URL_TEMPLATES_READ"]))
	$arParams["~URL_TEMPLATES_READ"] = $APPLICATION->GetCurPage()."?PAGE_NAME=read&FID=#FID#&TID=#TID#&MID=#MID#";
$arParams["~URL_TEMPLATES_READ"] = str_replace(array("#FORUM_ID#", "#TOPIC_ID#", "#MESSAGE_ID#"),
		array("#FID#", "#TID#", "#MID#"), $arParams["~URL_TEMPLATES_READ"]);
$arParams["URL_TEMPLATES_READ"] = htmlspecialcharsEx($arParams["~URL_TEMPLATES_READ"]);
/************** ADDITIONAL *****************************************/
$arParams["USE_CAPTCHA"] = $arResult["FORUM"]["USE_CAPTCHA"] == "Y" ? "Y" : $arParams["USE_CAPTCHA"];
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["FORUM_TOPIC_ID"] = intVal($arResult["ELEMENT"]["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
/************** 3. Get inormation about USER ***********************/
if ($GLOBALS["USER"]->IsAuthorized())
{
	$arResult["USER"]["SUBSCRIBE"] = array();
	$arResult["USER"]["FORUM_SUBSCRIBE"] = "N";
	$arResult["USER"]["TOPIC_SUBSCRIBE"] = "N";
// USER subscribes
	if ($arResult["USER"]["PERMISSION"] > "E")
	{
		$arUserSubscribe = array();
		$arFields = array("USER_ID" => $GLOBALS["USER"]->GetID(), "FORUM_ID" => $arParams["FORUM_ID"]);
		$db_res = CForumSubscribe::GetList(array(), $arFields);
		if ($db_res && ($res = $db_res->Fetch()))
		{
			do
			{
				$arUserSubscribe[] = $res;
			} while ($res = $db_res->Fetch());
		}
		$arResult["USER"]["SUBSCRIBE"] = $arUserSubscribe;
		foreach ($arUserSubscribe as $res)
		{
			if (intVal($res["TOPIC_ID"]) <= 0)
				$arResult["USER"]["FORUM_SUBSCRIBE"] = "Y";
			elseif(intVal($res["TOPIC_ID"]) == intVal($arResult["FORUM_TOPIC_ID"]))
				$arResult["USER"]["TOPIC_SUBSCRIBE"] = "Y";
		}
	}
}
/************** 4. Get message list ********************************/
if ($arResult["FORUM_TOPIC_ID"] > 0)
{	
	$page_number = $GLOBALS["NavNum"] + 1;
	$arMessages = array();
	$ar_cache_id = array($arParams["FORUM_ID"], $arParams["ELEMENT_ID"], $arResult["FORUM_TOPIC_ID"],
		$arParams["MESSAGES_PER_PAGE"], $arParams["DATE_TIME_FORMAT"], $arParams["PREORDER"], $_REQUEST["MID"], $_GET["PAGEN_".$page_number]);
	$cache_id = "forum_message_".serialize($ar_cache_id);
	$cache_path = $cache_path_main."forum".$arParams["FORUM_ID"]."/topic".$arResult["FORUM_TOPIC_ID"];
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		if (is_array($res["arMessages"]))
		{
			$arMessages = $res["arMessages"];
			$arResult["NAV_RESULT"] = $db_res;
			if (is_array($res["Nav"]))
			{
				$arResult["NAV_RESULT"] = $res["Nav"]["NAV_RESULT"];
				$arResult["NAV_STRING"] = $res["Nav"]["NAV_STRING"];
			}
		}
	}
	
	if (empty($arMessages))
	{
		$arOrder = array("ID" => ($arParams["PREORDER"] == "N" ? "DESC" : "ASC"));
		$db_res = CForumMessage::GetList($arOrder, 
			array("FORUM_ID"=>$arParams["FORUM_ID"], "TOPIC_ID"=>$arResult["FORUM_TOPIC_ID"], "APPROVED" => "Y", "!PARAM1" => "IB"));
		if ($db_res)
		{
			$MID = intVal($_REQUEST["MID"]);
			unset($_GET["MID"]); unset($GLOBALS["MID"]);
			if (intVal($MID) > 0)
			{
				$page_number = CForumMessage::GetMessagePage($MID, $arParams["MESSAGES_PER_PAGE"], $GLOBALS["USER"]->GetUserGroupArray(), $arResult["FORUM_TOPIC_ID"], array("ORDER_DIRECTION" => $arOrder["ID"]));
				$db_res->NavStart($arParams["MESSAGES_PER_PAGE"], false, $page_number);
			}
			else 
			{
				$db_res->NavStart($arParams["MESSAGES_PER_PAGE"], false);
			}
			$arResult["NAV_RESULT"] = $db_res;
			$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("NAV_OPINIONS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
			
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
	$res["AUTHOR_URL"] = "";
	if (!empty($arParams["URL_TEMPLATES_PROFILE_VIEW"]))
	{
		$res["AUTHOR_URL"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["AUTHOR_ID"]));
	}
/************** Author info/****************************************/
	// For quote JS	
	$res["FOR_JS"]["AUTHOR_NAME"] = Cutil::JSEscape($res["AUTHOR_NAME"]);
	$res["FOR_JS"]["POST_MESSAGE_TEXT"] = Cutil::JSEscape(htmlspecialchars($res["POST_MESSAGE_TEXT"]));
	$arMessages[$res["ID"]] = $res;
			} while ($res = $db_res->GetNext());
		}
/************** Attach files ***************************************/
if (!empty($arMessages))
{
	$res = array_keys($arMessages);
	$arFilter = array("FORUM_ID" => $arParams["FORUM_ID"], "TOPIC_ID" => $arResult["FORUM_TOPIC_ID"], 
		"APPROVED" => "Y", ">MESSAGE_ID" => intVal($res[0]) - 1, "<MESSAGE_ID" => intVal($res[count($res) - 1]) + 1);
	$src = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/";
	if (defined("BX_IMG_SERVER"))
		$src = BX_IMG_SERVER.$src;
	$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"), $arFilter);
	if ($db_files && $res = $db_files->Fetch())
	{
		do 
		{
			$res["SRC"] = str_replace("//", "/" , $src.$res["SUBDIR"]."/".$res["FILE_NAME"]);
			if ($arMessages[$res["MESSAGE_ID"]]["~ATTACH_IMG"] == $res["FILE_ID"])
			{
			// attach for custom 
				$arMessages[$res["MESSAGE_ID"]]["~ATTACH_FILE"] = $res;
				$arMessages[$res["MESSAGE_ID"]]["ATTACH_IMG"] = CFile::ShowFile($res["FILE_ID"], 0, 
					$arParams["IMAGE_SIZE"], $arParams["IMAGE_SIZE"], true, "border=0", false);
				$arMessages[$res["MESSAGE_ID"]]["ATTACH_FILE"] = $arMessages[$res["MESSAGE_ID"]]["ATTACH_IMG"];
			}
			$arMessages[$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
			$arResult["FILES"][$res["FILE_ID"]] = $res;
		}while ($res = $db_files->Fetch());
	}
}
/************** Message List/***************************************/
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array(
				"arMessages" => $arMessages, 
				"Nav" => array(
					"NAV_RESULT" => $arResult["NAV_RESULT"],
					"NAV_STRING" => $arResult["NAV_STRING"])));
		}
	}
	else 
	{
		$GLOBALS["NavNum"]++;
	}
	
	$arResult["MESSAGES"] = $arMessages;
	// Link to forum
	if (!empty($arResult["MESSAGES"]))
	{
		$arResult["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
			array("FID" => $arParams["FORUM_ID"], "TID" => $arResult["FORUM_TOPIC_ID"], "MID" => "s", 
				"PARAM1" => "IB", "PARAM2" => $arParams["ELEMENT_ID"]));
	}
}
/************** 5. Show post form **********************************/
$arResult["SHOW_POST_FORM"] = (($arResult["USER"]["PERMISSION"] > "I" || $arResult["PERMISSION"] > "E" && count($arResult["MESSAGES"]) > 0) ? "Y" : "N");
if ($arResult["SHOW_POST_FORM"] == "Y")
{
	// Author name
	$arResult["~REVIEW_AUTHOR"] = $arResult["USER"]["SHOWED_NAME"];
	$arResult["~REVIEW_USE_SMILES"] = ($arResult["FORUM"]["ALLOW_SMILES"] == "Y" ? "Y" : "N");
	
	if (!empty($arError) || !empty($arResult["MESSAGE_VIEW"]))
	{
		if (!empty($_POST["REVIEW_AUTHOR"]))
			$arResult["~REVIEW_AUTHOR"] = $_POST["REVIEW_AUTHOR"];
		$arResult["~REVIEW_EMAIL"] = $_POST["REVIEW_EMAIL"];
		$arResult["~REVIEW_TEXT"] = $_POST["REVIEW_TEXT"];
		$arResult["~REVIEW_USE_SMILES"] = ($_POST["REVIEW_USE_SMILES"] == "Y" ? "Y" : "N");
	}
	$arResult["REVIEW_AUTHOR"] = htmlspecialcharsEx($arResult["~REVIEW_AUTHOR"]);
	$arResult["REVIEW_EMAIL"] = htmlspecialcharsEx($arResult["~REVIEW_EMAIL"]);
	$arResult["REVIEW_TEXT"] = htmlspecialcharsEx($arResult["~REVIEW_TEXT"]);
	$arResult["REVIEW_USE_SMILES"] = $arResult["~REVIEW_USE_SMILES"];
	$arResult["REVIEW_FILES"] = array();
	foreach ($_REQUEST["FILES"] as $key => $val):
		if (intVal($val) <= 0)
			return false;
		$arResult["REVIEW_FILES"][$val] = CFile::GetFileArray($val);
	endforeach;

	// Form Info
	$arResult["SHOW_PANEL_ATTACH_IMG"] = (in_array($arResult["FORUM"]["ALLOW_UPLOAD"], array("A", "F", "Y")) ? "Y" : "N");
	$arResult["TRANSLIT"] = (LANGUAGE_ID=="ru" ? "Y" : " N");
	$arResult["ForumPrintSmilesList"] = ($arResult["FORUM"]["ALLOW_SMILES"] == "Y" ? 
		ForumPrintSmilesList(3, LANGUAGE_ID, $arParams["PATH_TO_SMILE"], $arParams["CACHE_TIME"]) : "");

	$arResult["CAPTCHA_CODE"] = "";
	if ($arParams["USE_CAPTCHA"] == "Y" && !$GLOBALS["USER"]->IsAuthorized())
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
		$cpt = new CCaptcha();
		$captchaPass = COption::GetOptionString("main", "captcha_password", "");
		if (strLen($captchaPass) <= 0)
		{
			$captchaPass = randString(10);
			COption::SetOptionString("main", "captcha_password", $captchaPass);
		}
		$cpt->SetCodeCrypt($captchaPass);
		$arResult["CAPTCHA_CODE"] = htmlspecialchars($cpt->GetCodeCrypt());
	}
}

$arResult["SHOW_CLOSE_ALL"] = "N";
if ($arResult["FORUM"]["ALLOW_BIU"] == "Y" || $arResult["FORUM"]["ALLOW_FONT"] == "Y" || $arResult["FORUM"]["ALLOW_ANCHOR"] == "Y" || $arResult["FORUM"]["ALLOW_IMG"] == "Y" || $arResult["FORUM"]["ALLOW_QUOTE"] == "Y" || $arResult["FORUM"]["ALLOW_CODE"] == "Y" || $arResult["FORUM"]["ALLOW_LIST"] == "Y")
	$arResult["SHOW_CLOSE_ALL"] = "Y";

// *****************************************************************************************
if ($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
{
		CForumNew::ShowPanel($arParams["FORUM_ID"], 0);
}
// *****************************************************************************************

/* For custom template */
$arResult["LANGUAGE_ID"] = LANGUAGE_ID;
$arResult["IS_AUTHORIZED"] = $GLOBALS["USER"]->IsAuthorized();
$arResult["PERMISSION"] = $arResult["USER"]["PERMISSION"];
$arResult["SHOW_NAME"] = $arResult["USER"]["SHOWED_NAME"];
$arResult["sessid"] = bitrix_sessid_post();
$arResult["SHOW_SUBSCRIBE"] = ($arResult["USER"]["ID"] > 0 && $arResult["USER"]["PERMISSION"] > "E" ? "Y" : "N");
$arResult["TOPIC_SUBSCRIBE"] = $arResult["USER"]["TOPIC_SUBSCRIBE"];
$arResult["FORUM_SUBSCRIBE"] = $arResult["USER"]["FORUM_SUBSCRIBE"];
$arResult["SHOW_LINK"] = (empty($arResult["read"]) ? "N" : "Y");
$arResult["SHOW_POSTS"]	= (empty($arResult["MESSAGES"]) ? "N" : "Y");
$arResult["PARSER"] = $parser;
$arResult["CURRENT_PAGE"] = $APPLICATION->GetCurPageParam();

$arResult["ELEMENT_REAL"] = $arResult["ELEMENT"];
$arResult["ELEMENT"] = array(
	"PRODUCT" => $arResult["ELEMENT"], 
	"PRODUCT_PROPS" => array());
if (is_set($arResult["ELEMENT_REAL"], "PROPERTY_FORUM_TOPIC_ID_VALUE"))
{
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["FORUM_TOPIC_ID"] = array("VALUE" => $arResult["ELEMENT_REAL"]["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["~FORUM_TOPIC_ID"] = array("VALUE" => $arResult["ELEMENT_REAL"]["~PROPERTY_FORUM_TOPIC_ID_VALUE"]);
}
if (is_set($arResult["ELEMENT_REAL"], "PROPERTY_FORUM_MESSAGE_CNT_VALUE"))
{
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["FORUM_MESSAGE_CNT"] = array("VALUE" => $arResult["ELEMENT_REAL"]["PROPERTY_FORUM_MESSAGE_CNT_VALUE"]);
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["~FORUM_MESSAGE_CNT"] = array("VALUE" => $arResult["ELEMENT_REAL"]["~PROPERTY_FORUM_MESSAGE_CNT_VALUE"]);
}
/* For custom template */

// *****************************************************************************************
$this->IncludeComponentTemplate();
// *****************************************************************************************
?>