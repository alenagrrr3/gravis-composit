<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = IntVal($arParams["ID"]);
if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");
$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
if(strlen($arParams["PATH_TO_USER_EDIT"])<=0)
	$arParams["PATH_TO_USER_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#&mode=edit");
$arParams["PATH_TO_SEARCH"] = trim($arParams["PATH_TO_SEARCH"]);
if(strlen($arParams["PATH_TO_SEARCH"])<=0)
	$arParams["PATH_TO_SEARCH"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search");
if(strpos($arParams["PATH_TO_SEARCH"], "?")===false)
	$arParams["PATH_TO_SEARCH"] .= "?";
else
	$arParams["PATH_TO_SEARCH"] .= "&";
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$arResult["urlToCancel"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arParams["ID"]));

$dbUser = CUser::GetByID($arParams["ID"]);
$arResult["arUser"] = $dbUser->GetNext();

$arResult["bEdit"] = ($_REQUEST['mode']=='edit' && ($USER->GetID()==$arParams["ID"] || $USER->IsAdmin())) ? "Y" : "N";

if($arParams["ID"] == ($USER->GetID()) || $USER->IsAdmin())
	$arResult["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_EDIT"], array("user_id" => $arParams["ID"]));

if(!is_array($arResult["arUser"]))
{
	$arResult["FATAL_ERROR"] = GetMessage("B_B_USER_NO_USER");
}
else
{
	$BLOG_USER_ID=intval($_POST["BLOG_USER_ID"]);
	if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["save"])>0 && check_bitrix_sessid())
	{
		if(CModule::IncludeModule("blog"))
		{
			if($BLOG_USER_ID<=0)
			{
				$BlogUser = CBlogUser::GetByID($arParams["ID"], BLOG_BY_USER_ID);
				
				if(empty($BlogUser))
				{
					$BLOG_USER_ID=CBlogUser::Add(array(
						"USER_ID" => $arParams["ID"],
						"=LAST_VISIT" => $DB->GetNowFunction(),
						"=DATE_REG" => $DB->GetNowFunction(),
						"ALLOW_POST" => "Y",
						"PATH" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arParams["ID"])),
					));
					$BlogUser = CBlogUser::GetByID($BLOG_USER_ID);
				}
				else
				{
					$BLOG_USER_ID = $BlogUser["ID"];
				}
			}
			else
			{
				$BlogUser = CBlogUser::GetByID($BLOG_USER_ID);
			}

			$BlogUser = CBlogTools::htmlspecialcharsExArray($BlogUser);
			if($BlogUser && ($USER->GetID()==$BlogUser["USER_ID"] || $USER -> IsAdmin()))
			{
				$arPICTURE = $_FILES["AVATAR"];
				$arPICTURE["old_file"] = $BlogUser["AVATAR"];
				$arPICTURE["del"] = $_POST["AVATAR_del"];
				$arHobbyDB=array();
				$arHobby=explode(",", $_POST["INTERESTS"]);
				foreach($arHobby as $Hobby)
				{
					$Hobby=trim($Hobby);
					$arHobbyDB[]=$Hobby;
				}
				$arHobbyDB=array_unique($arHobbyDB);
				if(count($arHobbyDB)>0)
					$Hobby=implode(", ", $arHobbyDB);
				else
					$Hobby="";
				$arFields = array(
					"ALIAS" => $_POST["ALIAS"],
					"DESCRIPTION" => $_POST["DESCRIPTION"],
					"AVATAR" => $arPICTURE,
					"INTERESTS" => $Hobby,
					"PATH" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arParams["ID"])),
				);
				$DB->StartTransaction();
				$res = CBlogUser::Update($BLOG_USER_ID, $arFields);
				if($res)
				{
					$arPICTURE = $_FILES["PERSONAL_PHOTO"];
					$arPICTURE["old_file"] = $arResult["arUser"]["PERSONAL_PHOTO"];
					$arPICTURE["del"] = $_POST["PERSONAL_PHOTO_del"];

					$arFields = Array(
						"PERSONAL_WWW" => $_POST["PERSONAL_WWW"],
						"PERSONAL_GENDER" => $_POST["PERSONAL_GENDER"],
						"PERSONAL_BIRTHDAY" => $_POST["PERSONAL_BIRTHDAY"],
						"PERSONAL_PHOTO" => $arPICTURE,
					);
					$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("USER", $arFields);
					$res = $USER->Update($BlogUser["USER_ID"], $arFields);
					if ($res)
						$DB->Commit();
					else
					{
						$DB->Rollback();
						$strErrorMessage .= $USER->LAST_ERROR;
					}
					$dbBlog = CBlog::GetList(Array("ID" => "DESC"), Array("OWNER_ID" => $BlogUser["USER_ID"]), false, false, Array("ID", "OWNER_ID", "URL"));
					while($arBlog = $dbBlog->Fetch())
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog['URL']);
				}
				else
				{
					$DB->Rollback();
					if($e = $APPLICATION->GetException())
						$strErrorMessage .= $e->GetString();
				}
					
			}
			else
				$strErrorMessage.= GetMessage("B_B_PU_NO_RIGHTS")."<br />";
		}

		if(strlen($strErrorMessage)<=0)
			LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arParams["ID"])));
		else
			$arResult["ERROR_MESSAGE"] = $strErrorMessage;
	}

	$arResult["BlogUser"] = CBlogUser::GetByID($arParams["ID"], BLOG_BY_USER_ID);
	$arResult["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arResult["BlogUser"]);
	$arResult["arSex"] = array(
			"M"=>GetMessage("B_B_USER_SEX_M"),
			"F"=>GetMessage("B_B_USER_SEX_F"),
		);
	$arResult["userName"] = CBlogUser::GetUserName($arResult["BlogUser"]["ALIAS"], $arResult["arUser"]["NAME"], $arResult["arUser"]["LAST_NAME"], $arResult["arUser"]["LOGIN"]);
	$arResult["User"] = $arResult["arUser"];
	
	$arResult["BlogUser"]["LAST_VISIT_FORMATED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResult["BlogUser"]["LAST_VISIT"], CSite::GetDateFormat("FULL")));
	foreach($arResult["BlogUser"] as $k=>$v)
		$arResult["User"][$k] = $v;
		
	if($arResult["bEdit"] == "Y")
	{
		foreach($_POST as $k => $v)
		{
			if(is_array($v))
			{
				foreach($v as $k1 => $v1)
				{
					$arResult["User"][$k1] = htmlspecialchars($v1);
					$arResult["User"]['~'.$k1] = $v1;
				}
			}
			else
			{
				$arResult["User"][$k] = htmlspecialchars($v);
				$arResult["User"]['~'.$k] = $v;						
			}
		}
	}
	if($arParams["SET_TITLE"]=="Y")
	{
		if($arResult["bEdit"] == "Y")
			$APPLICATION->SetTitle(GetMessage("B_B_USER_TITLE")." \"".$arResult["userName"]."\"");
		else
			$APPLICATION->SetTitle(GetMessage("B_B_USER_TITLE_VIEW")." ".$arResult["userName"]);
	}

	$arFilterTmp = Array("ACTIVE" => "Y", "GROUP_SITE_ID" => SITE_ID, "OWNER_ID" => $arParams["ID"]);
	if(IntVal($arParams["GROUP_ID"]) > 0)
		$arFilterTmp["GROUP_ID"] = $arParams["GROUP_ID"];

	$dbBlog = CBlog::GetList(Array(), $arFilterTmp);
	if($arBlog = $dbBlog->GetNext())
	{
		$arResult["Blog"] = $arBlog;
	}

	if(!empty($arResult["Blog"]))
		$arResult["Blog"]["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arResult["Blog"]["URL"]));
	
	if(strlen($arResult["User"]["PERSONAL_WWW"])>0)
		$arResult["User"]["PERSONAL_WWW"] = ((strpos($arResult["User"]["PERSONAL_WWW"], "http") === false)? "http://" : "").$arResult["User"]["PERSONAL_WWW"];
	
	$arHobby = explode(", ", $arResult["User"]["~INTERESTS"]);
	$arResult["User"]["Hobby"]=Array();
	foreach($arHobby as $Hobby)
	{
		if(strlen($Hobby)>0)
			$arResult["User"]["Hobby"][] = Array(
					"link" => $arParams["PATH_TO_SEARCH"].'where=USER&q='.urlencode($Hobby),
					"name" => htmlspecialcharsEx($Hobby),
				);
	}
	
	$dbFriends = CBlogUser::GetUserFriends($arParams["ID"], True);
	$arResult["User"]["friendsOf"] = Array();
	while ($arFriends = $dbFriends->GetNext())
	{
		$arResult["User"]["friendsOf"][] = Array(
				"link" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arFriends["URL"])),
				"name" => $arFriends["NAME"],
			);
	}

	$arResult["User"]["friends"] = Array();
	$dbFriends = CBlogUser::GetUserFriends($arParams["ID"], False);
	while ($arFriends = $dbFriends->GetNext())
	{
		$arResult["User"]["friends"][] = Array(
				"link" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arFriends["URL"])),
				"name" => $arFriends["NAME"],
			);
	}
	
	$arResult["User"]["PERSONAL_PHOTO_FILE"] = CFile::GetFileArray($arResult["User"]["PERSONAL_PHOTO"]);
	if ($arResult["User"]["PERSONAL_PHOTO_FILE"] !== false)
		$arResult["User"]["PERSONAL_PHOTO_IMG"] = CFile::ShowImage($arResult["User"]["PERSONAL_PHOTO_FILE"]["SRC"], 150, 150, "border=0", "", true);
		
	$arResult["User"]["AVATAR_FILE"] = CFile::GetFileArray($arResult["User"]["AVATAR"]);
	if ($arResult["User"]["AVATAR_FILE"] !== false)
		$arResult["User"]["AVATAR_IMG"] = CFile::ShowImage($arResult["User"]["AVATAR_FILE"]["SRC"], 100, 100, "border=0");
	// ********************* User properties ***************************************************
	$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
	if (!empty($arParams["USER_PROPERTY"]))
	{
		$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", $arParams["ID"], LANGUAGE_ID);
		if (count($arParams["USER_PROPERTY"]) > 0)
		{
			foreach ($arUserFields as $FIELD_NAME => $arUserField)
			{
				if (!in_array($FIELD_NAME, $arParams["USER_PROPERTY"]))
					continue;
				$arUserField["EDIT_FORM_LABEL"] = strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
				$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
				$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
				$arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
			}
		}
		if (!empty($arResult["USER_PROPERTIES"]["DATA"]))
			$arResult["USER_PROPERTIES"]["SHOW"] = "Y";
		$arResult["bVarsFromForm"] = strLen($strErrorMessage) > 0 ? true : false;
	}
	// ******************** /User properties ***************************************************
}
$this->IncludeComponentTemplate();
?>