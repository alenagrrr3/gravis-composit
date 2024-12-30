<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
/***************** URL *********************************************/
	$res = $arResult;
	$URL_NAME_DEFAULT = array(
			"active" => "PAGE_NAME=active",
			"help" => "PAGE_NAME=help",
			"index" => "",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"rules" =>"PAGE_NAME=rules",
			"search" => "PAGE_NAME=search",
			"subscr_list" => "PAGE_NAME=subscr_list",
			"pm_folder" => "PAGE_NAME=pm_folder",
			"user_list" => "PAGE_NAME=user_list");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($res["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$res["URL_TEMPLATES_".strToUpper($URL)] = $GLOBALS["APPLICATION"]->GetCurPage()."?".$URL_VALUE;
		$res["~URL_TEMPLATES_".strToUpper($URL)] = $res["URL_TEMPLATES_".strToUpper($URL)];
		$res["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($res["~URL_TEMPLATES_".strToUpper($URL)]);
	}
	$arParams["SHOW_AUTH_FORM"] = ($arParams["SHOW_AUTH_FORM"] == "N" ? "N" : "Y");
/***************** ADDITIONAL **************************************/
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/
$res["URL"] = array(
	"ACTIVE" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_ACTIVE"], array()), 
	"PROFILE" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $GLOBALS["USER"]->GetID())), 
	"SEARCH" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_SEARCH"], array()), 
	"SUBSCRIBES" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_SUBSCR_LIST"], array()), 
	"MESSAGES" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_PM_FOLDER"], array()), 
	"USERS" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_USER_LIST"], array()), 
	"RULES" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_RULES"], array()), 
	"INDEX" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_INDEX"], array()), 
	"~INDEX" => CComponentEngine::MakePathFromTemplate($res["~URL_TEMPLATES_INDEX"], array()));
?>
<div class="forum-info-box forum-menu-box">
	<div class="forum-info-box-inner">
<?
if ($GLOBALS["USER"]->IsAuthorized()):
	$pm = "";
	$arUserPM = array();
	$cache = new CPHPCache();
	$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");
	$cache_id = "forum_user_pm_".$GLOBALS["USER"]->GetId();
	$cache_path = $cache_path_main."user".$GLOBALS["USER"]->GetId();
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$val = $cache->GetVars();
		if (is_array($val["arUserPM"]))
			$arUserPM = $val["arUserPM"];
	}
	if (!is_array($arUserPM) || empty($arUserPM))
	{
		CModule::IncludeModule("forum");
		$arUserPM = CForumPrivateMessage::GetNewPM();
		if ($arParams["CACHE_TIME"] > 0):
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("arUserPM"=>$arUserPM));
		endif;
	}
	if (intVal($arUserPM["UNREAD_PM"]) > 0)
	{
		$pm = " (".intVal($arUserPM["UNREAD_PM"]).")";
	}

?>
		<span class="forum-menu-item forum-menu-item-first forum-menu-newtopics"><?
			?><a href="<?=$res["URL"]["ACTIVE"]?>" title="<?=GetMessage("F_NEW_TOPIC_TITLE")?>"><span><?=GetMessage("F_NEW_TOPIC")?></span></a>&nbsp;</span>
		<span class="forum-menu-item forum-menu-profile"><a href="<?=$res["URL"]["PROFILE"]?>"><span><?=GetMessage("F_PROFILE")?></span></a>&nbsp;</span>
<?
if ($arParams["SHOW_SUBSCRIBE_LINK"] == "Y"):
?>
		<span class="forum-menu-item forum-menu-subscribes"><a href="<?=$res["URL"]["SUBSCRIBES"]?>"><span><?=GetMessage("F_SUBSCRIBES")?></span></a>&nbsp;</span>
<?
endif;
?>
		<span class="forum-menu-item forum-menu-messages"><a href="<?=$res["URL"]["MESSAGES"]?>"><span><?=GetMessage("F_MESSAGES")?><?=$pm?></span></a>&nbsp;</span>
<?
endif;
if (IsModuleInstalled("search")):
?>
		<span class="forum-menu-item <?
			?><?=($GLOBALS["USER"]->IsAuthorized() ? "" : "forum-menu-item-first")?><?
			?> forum-menu-search"><a href="<?=$res["URL"]["SEARCH"]?>"><span><?=GetMessage("F_SEARCH")?></span></a>&nbsp;</span>
<?	
endif;
?>
		<span class="forum-menu-item <?
			?><?=($GLOBALS["USER"]->IsAuthorized() || IsModuleInstalled("search") ? "" : "forum-menu-item-first")?><?
			?> forum-menu-users"><a href="<?=$res["URL"]["USERS"]?>"><span><?=GetMessage("F_USERS")?></span></a>&nbsp;</span>
		<span class="forum-menu-item <?
			?><?=($arParams["SHOW_AUTH_FORM"] == "Y" ? "" : "forum-menu-item-last")?><?
			?> forum-menu-rules"><a href="<?=$res["URL"]["RULES"]?>"><span><?=GetMessage("F_RULES")?></span></a>&nbsp;</span>
<?
if ($arParams["SHOW_AUTH_FORM"] == "Y"):
?>
		<span class="forum-menu-item forum-menu-item-last forum-menu-authorize">
		<?$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:system.auth.form", "", Array(), $component);?>&nbsp;</span>
<?
endif;
?>
	</div>
</div>
<?
if ($arParams["SHOW_NAVIGATION"] != "N" && $arParams["SET_NAVIGATION"] != "N" && $arResult["PAGE_NAME"] != "index"):
// text from main
	if($GLOBALS["APPLICATION"]->GetProperty("NOT_SHOW_NAV_CHAIN")=="Y")
		return false;
	CMain::InitPathVars($site, $path);
	$DOC_ROOT = CSite::GetSiteDocRoot($site);

	$path = $GLOBALS["APPLICATION"]->GetCurDir();
	$arChain = Array();
	
	while(true)
	{
		$path = rtrim($path, "/");

		$chain_file_name = $DOC_ROOT.$path."/.section.php";
		if(file_exists($chain_file_name))
		{
			$sSectionName = "";
			include($chain_file_name);
			if(strlen($sSectionName)>0)
				$arChain[] = Array("TITLE"=>$sSectionName, "LINK"=>$path."/");
		}

		if(strlen($path)<=0)
			break;
		$pos = bxstrrpos($path, "/");
		if($pos===false)
			break;
		$path = substr($path, 0, $pos+1);
	}
	if ($arResult["PAGE_NAME"] == "read")
	{
		$GLOBALS["FORUM_HIDE_LAST_BREADCRUMB"] = true;
	}
	$GLOBALS["APPLICATION"]->IncludeComponent(
	"bitrix:breadcrumb", ".default",
	Array(
		"START_FROM" => count($arChain) - 1, 
		"PATH" => "", 
		"SITE_ID" => "",  
	), $component, 
	array("HIDE_ICONS" => "Y")
);
endif;
?>