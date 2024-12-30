<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
require_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/functions.php");

//Menu depth level
if (isset($arParams["MAX_LEVEL"]) && 1 < intval($arParams["MAX_LEVEL"]) && intval($arParams["MAX_LEVEL"]) < 5)
	$arParams["MAX_LEVEL"] = intval($arParams["MAX_LEVEL"]);
else
	$arParams["MAX_LEVEL"] = 1;

//Root menu type
if (isset($arParams["ROOT_MENU_TYPE"]) && strlen($arParams["ROOT_MENU_TYPE"]) > 0)
	$arParams["ROOT_MENU_TYPE"] = htmlspecialchars(trim($arParams["ROOT_MENU_TYPE"]));
else
	$arParams["ROOT_MENU_TYPE"] = "left";

//Child menu type
if (isset($arParams["CHILD_MENU_TYPE"]) && strlen($arParams["CHILD_MENU_TYPE"]) > 0)
	$arParams["CHILD_MENU_TYPE"] = htmlspecialchars(trim($arParams["CHILD_MENU_TYPE"]));
else
	$arParams["CHILD_MENU_TYPE"] = "left";

//Include menu_ext.php
$arParams["USE_EXT"] = (isset($arParams["USE_EXT"]) && $arParams["USE_EXT"] == "Y" ? true : false);

$curDir = $APPLICATION->GetCurDir();

if(
	$arParams["MENU_CACHE_TYPE"] === "Y"
	|| (
		$arParams["MENU_CACHE_TYPE"] === "A"
		&& COption::GetOptionString("main", "component_cache_on", "Y") === "Y"
	)
)
{
	$arParams["MENU_CACHE_TIME"] = intval($arParams["MENU_CACHE_TIME"]);
}
else
{
	$arParams["MENU_CACHE_TIME"] = 0;
}

if($arParams["MENU_CACHE_TIME"])
{
	$strCacheID = $APPLICATION->GetCurPage().
		":".$arParams["USE_EXT"].
		":".$arParams["MAX_LEVEL"].
		":".$arParams["ROOT_MENU_TYPE"].
		":".$arParams["CHILD_MENU_TYPE"].
		":".LANG.
		":".SITE_ID.
		""
	;

	if($arParams["MENU_CACHE_USE_GROUPS"] === "Y")
		$strCacheID .= ":".$USER->GetGroups();

	if(is_array($arParams["MENU_CACHE_GET_VARS"]))
	{
		foreach($arParams["MENU_CACHE_GET_VARS"] as $varname)
		{
			$varname = trim($varname);
			if(strlen($varname) && array_key_exists($varname, $_GET))
				$strCacheID .= ":".$varname."=".$_GET[$varname];
		}
	}

	$strCacheID = md5($strCacheID);
}
else
{
	$strCacheID = "";
}

global $CACHE_MANAGER;

if($arParams["MENU_CACHE_TIME"] && $CACHE_MANAGER->Read($arParams["MENU_CACHE_TIME"], $strCacheID, "menu"))
{
	$arResult = $CACHE_MANAGER->Get($strCacheID);
}
else
{
	//Read root menu
	$menu = new CMenu($arParams["ROOT_MENU_TYPE"]);
	$menu->Init($curDir, $arParams["USE_EXT"], $componentPath."/stub.php");

//	foreach($this->arMenu as $MenuItem)
//	{
//		if(strlen($MenuItem[4]) > 0)
//		{
//			$arParams["MENU_CACHE_TIME"] = 0;
//			break;
//		}
//	}

	$menu->RecalcMenu();

	$arResult = Array();

	//Read child menu recursive
	if ($arParams["MAX_LEVEL"] > 1)
	{
		_GetChildMenuRecursive(
			$menu->arMenu,
			$arResult,
			$arParams["CHILD_MENU_TYPE"],
			$arParams["USE_EXT"],
			$menu->template,
			$currentLevel = 1,
			$arParams["MAX_LEVEL"]
		);
	}
	else
	{
		$arResult = $menu->arMenu;
		for ($menuIndex = 0, $menuCount = count($menu->arMenu); $menuIndex < $menuCount; $menuIndex++)
		{
			//Menu from iblock (bitrix:menu.sections)
			if (is_array($arResult[$menuIndex]["PARAMS"]) && isset($arResult[$menuIndex]["PARAMS"]["FROM_IBLOCK"]))
			{
				$arResult[$menuIndex]["DEPTH_LEVEL"] = $arResult[$menuIndex]["PARAMS"]["DEPTH_LEVEL"];
				$arResult[$menuIndex]["IS_PARENT"] = $arResult[$menuIndex]["PARAMS"]["IS_PARENT"];
			}
			else
			{
				//Menu from files
				$arResult[$menuIndex]["DEPTH_LEVEL"] = 1;
				$arResult[$menuIndex]["IS_PARENT"] = false;
			}
		}
	}

	unset($menu->arMenu);

	$arResult["menuDir"] = $menu->MenuDir;
	$arResult["menuType"] = $menu->type;

	if($arParams["MENU_CACHE_TIME"])
		$CACHE_MANAGER->Set($strCacheID, $arResult);

}

$menuDir = $arResult["menuDir"];
unset($arResult["menuDir"]);
$menuType = $arResult["menuType"];
unset($arResult["menuType"]);

//echo "<pre>",htmlspecialchars(print_r($arResult, true)),"</pre>";

if (IsModuleInstalled('fileman'))
{
	//Icons
	$menuExists = (strlen($menuDir) > 0);

	$bMenuAdd =
		$APPLICATION->GetPublicShowMode() == 'configure'
		&&
		(
			$APPLICATION->GetCurDir() != $menuDir
			||
			!$menuExists
		)
		&& $USER->CanDoOperation('fileman_add_element_to_menu')
		&& $USER->CanDoOperation('fileman_edit_menu_elements')
		&& $USER->CanDoFileOperation('fm_add_to_menu',  Array(SITE_ID, $menuDir.".".$menuType.".menu.php"));
		// this one is checking l8r
		//&& $USER->CanDoFileOperation('fm_create_new_file', Array(SITE_ID, $menuDir.".".$menuType.".menu.php"));

	$bMenuEdit =
		$menuExists
		&& $USER->CanDoOperation('fileman_add_element_to_menu')
		&& $USER->CanDoOperation('fileman_edit_menu_elements')
		&& $USER->CanDoFileOperation('fm_add_to_menu',  Array(SITE_ID, $menuDir.".".$menuType.".menu.php"))
		&& $USER->CanDoFileOperation('fm_edit_existent_file', Array(SITE_ID, $menuDir.".".$menuType.".menu.php"));

	/*
	$displayIcons = (
		$USER->CanDoOperation('fileman_edit_menu_elements') &&
		(
			($menuExists && $USER->CanDoFileOperation('fm_edit_existent_file', Array(SITE_ID, $menuDir.".".$menuType.".menu.php")))
			||
			(!$menuExists && $USER->CanDoOperation('fileman_add_element_to_menu'))
		)
	);
	*/

	if ($bMenuAdd)
	{
		$bMenuAdd = false;
		$currentAddDir = $APPLICATION->GetCurDir();

		while (strlen($currentAddDir)>0)
		{
			$currentAddDir = rtrim($currentAddDir, "/");

			if (is_dir($_SERVER["DOCUMENT_ROOT"].$currentAddDir) && $USER->CanDoFileOperation('fm_create_new_file', Array(SITE_ID, $currentAddDir."/.".$menuType.".menu.php")))
			{
				$bMenuAdd = true;
				$menuDirAdd = $currentAddDir;
				break;
			}

			$position = strrpos($currentAddDir, "/");
			if ($position === false)
				break;

			$currentAddDir = substr($currentAddDir, 0, $position+1);
		}
	}

	$arIcons = array();

	if ($bMenuEdit)
	{
		$menu_edit_url = $APPLICATION->GetPopupLink(array(
			"URL"=> "/bitrix/admin/public_menu_edit.php?lang=".LANGUAGE_ID.
				"&site=".SITE_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"]).
				"&path=".urlencode($menuDir)."&name=".$menuType
			)
		);

		//Icons
		$arIcons[] = Array(
			"URL"		=> 'javascript:'.$menu_edit_url,
			"ICON"		=> "menu-edit",
			"TITLE"		=> ($menuExists? GetMessage("MAIN_MENU_EDIT") : GetMessage("MAIN_MENU_ADD")),
			"DEFAULT"	=> ($APPLICATION->GetPublicShowMode() != 'configure' ? true : false),
		);

		//panel
		$static_var_name = 'BX_TOPPANEL_MENU_EDIT_'.$menuType;

		if (!defined($static_var_name))
		{
			define($static_var_name, 1);

			$curDir = $APPLICATION->GetCurDir();
			$bDefaultItem = ($curDir == "/" && $menuType == "top" || $curDir <> "/" && $menuType == "left");
			$arMenuTypes = GetMenuTypes(SITE_ID);
			$buttonID = "menus";

			$APPLICATION->AddPanelButton(array(
				"HREF"		=> ($bDefaultItem ? 'javascript:'.$menu_edit_url : ''),
				"ID"		=> $buttonID,
				"ICON"		=> "icon-menu",
				"ALT"		=> GetMessage('MAIN_MENU_TOP_PANEL_BUTTON_ALT')
					.($bDefaultItem ? ' '.'&quot;'.(isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType]:$menuType).'&quot;' : ''),
				"TEXT"		=> GetMessage("MAIN_MENU_TOP_PANEL_BUTTON_TEXT"),
				"MAIN_SORT"	=> "300",
				"SORT"		=> 10,
				"RESORT_MENU"=>true,
				//"MODE"		=> array("view", "edit"),
			), $bDefaultItem);

			$aMenuItem =  array(
				"TEXT"		=> GetMessage(
					'MAIN_MENU_TOP_PANEL_ITEM_TEXT',
					array('#MENU_TITLE#' => (isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType] : $menuType))
				),
				"TITLE"		=> GetMessage(
					'MAIN_MENU_TOP_PANEL_ITEM_ALT',
					array('#MENU_TITLE#' => (isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType] : $menuType))
				),
				"SORT" => "100",
				"ICON"		=> "menu-edit",
				"ACTION"	=> $menu_edit_url,
				"DEFAULT"	=> $bDefaultItem,
			);
			$APPLICATION->AddPanelButtonMenu($buttonID, $aMenuItem);
		}
	}

	if ($bMenuAdd)
	{
		$menu_edit_url = $APPLICATION->GetPopupLink(array(
			"URL" => "/bitrix/admin/public_menu_edit.php?new=Y&lang=".LANGUAGE_ID.
				"&site=".SITE_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"]).
				"&path=".urlencode($menuDirAdd)."&name=".$menuType
			)
		);

		//Icons
		$arIcons[] = Array(
			"URL"		=> 'javascript:'.$menu_edit_url,
			"ICON"		=> "menu-edit",
			"TITLE"		=> GetMessage('MAIN_MENU_ADD_NEW'),
			"DEFAULT"	=> (!$bMenuEdit && $APPLICATION->GetPublicShowMode() != 'configure' ? true : false),
		);

		//panel
		$static_var_name = 'BX_TOPPANEL_MENU_ADD_'.$menuType;

		if (!defined($static_var_name))
		{
			define($static_var_name, 1);

			$curDir = $APPLICATION->GetCurDir();
			$bDefaultItem = ($curDir == "/" && $menuType == "top" || $curDir <> "/" && $menuType == "left");
			$arMenuTypes = GetMenuTypes(SITE_ID);
			$buttonID = "menus";

			$APPLICATION->AddPanelButton(array(
				"HREF"		=> ($bDefaultItem ? 'javascript:'.$menu_edit_url : ''),
				"ID"		=> $buttonID,
				"ICON"		=> "icon-menu",
				"ALT"		=> GetMessage('MAIN_MENU_TOP_PANEL_BUTTON_ALT')
					.($bDefaultItem ? ' '.'&quot;'.(isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType]:$menuType).'&quot;' : ''),
				"TEXT"		=> GetMessage("MAIN_MENU_TOP_PANEL_BUTTON_TEXT"),
				"MAIN_SORT"	=> "300",
				"SORT"		=> 10,
				"RESORT_MENU"=>true,
				//"MODE"		=> array("configure"),
			), false);

			$aMenuItem =  array(
				"TEXT"		=> GetMessage(
					'MAIN_MENU_ADD_TOP_PANEL_ITEM_TEXT',
					array('#MENU_TITLE#' => (isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType] : $menuType))
				),
				"TITLE"		=> GetMessage(
					'MAIN_MENU_ADD_TOP_PANEL_ITEM_ALT',
					array('#MENU_TITLE#' => (isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType] : $menuType))
				),
				"SORT" => "200",
				"ICON"		=> "menu-edit",
				"ACTION"	=> $menu_edit_url,
				"DEFAULT"	=> false,
			);

			if (!defined('BX_TOPPANEL_MENU_SEPARATOR_INCLUDED'))
			{
				$APPLICATION->AddPanelButtonMenu($buttonID, array('SEPARATOR' => "Y", "SORT" => "150"));
				define('BX_TOPPANEL_MENU_SEPARATOR_INCLUDED', 1);
			}

			$APPLICATION->AddPanelButtonMenu($buttonID, $aMenuItem);
		}
	}

	if ($bMenuAdd || $bMenuEdit)
			$this->AddIncludeAreaIcons($arIcons);
}

$this->IncludeComponentTemplate();

?>