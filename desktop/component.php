<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/desktop/include.php');
if(!class_exists('CUserOptions'))
	include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/classes/".$GLOBALS['DBType']."/favorites.php");

$arParams["ID"] = (isset($arParams["ID"])?$arParams["ID"]:"gdholder1");

$arResult = Array();

if($USER->IsAuthorized() && $APPLICATION->GetFileAccessPermission($APPLICATION->GetCurPage())>"R")
	$arResult["PERMISSION"] = "X";
elseif($USER->IsAuthorized() && $arParams["CAN_EDIT"]=="Y")
	$arResult["PERMISSION"] = "W";
else
	$arResult["PERMISSION"] = "R";

$arParams["PERMISSION"] = $arResult["PERMISSION"];

if($USER->IsAuthorized() && $arResult["PERMISSION"]>"R")
{
	if($_SERVER['REQUEST_METHOD']=='POST')
	{
		if($_POST['holderid'] == $arParams["ID"])
		{
			$gdid = $_POST['gid'];
			$p = strpos($gdid, "@");
			if($p === false)
			{
				$gadget_id = $gdid;
				$gdid = $gdid."@".rand();
			}
			else
			{
				$gadget_id = substr($gdid, 0, $p);
			}

			$arGadget = BXGadget::GetById($gadget_id);
			if($arGadget && !is_array($arParams["GADGETS"]) || in_array($arGadget["ID"], $arParams["GADGETS"]) || in_array("ALL", $arParams["GADGETS"]))
			{
				if($_POST['action']=='add')
				{
					$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], false);
					if(!is_array($arUserOptions["GADGETS"]))
						$arUserOptions["GADGETS"] = Array();

					foreach($arUserOptions["GADGETS"] as $tempid=>$tempgadget)
						if($tempgadget["COLUMN"]==0)
							$arUserOptions["GADGETS"][$tempid]["ROW"]++;

			   		$arUserOptions["GADGETS"][$gdid] = Array("COLUMN"=>0, "ROW"=>0);
					CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions);

					LocalRedirect($_SERVER['REQUEST_URI']);
				}
				elseif($_POST['action']=='update')
				{
					$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], false);
					if(!is_array($arUserOptions["GADGETS"]))
						$arUserOptions["GADGETS"] = Array();

			   		$arUserOptions["GADGETS"][$gdid]["SETTINGS"] = $_POST["settings"];
					CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions);

					LocalRedirect($_SERVER['REQUEST_URI']);
				}
			}
		}
	}
}


//CUserOptions::DeleteOption("intranet", "~gadgets_".$arParams["ID"], true);
if($_REQUEST['gd_ajax']==$arParams["ID"])
{
	if($USER->IsAuthorized() && $arResult["PERMISSION"]>"R")
	{
		$APPLICATION->RestartBuffer();
		switch($_REQUEST['gd_ajax_action'])
		{
			case 'get_settings':
				$gdid = $_REQUEST['gid'];

				$p = strpos($gdid, "@");
				if($p === false)
					break;

				$gadget_id = substr($gdid, 0, $p);

				// запрещенные администратором
				if(is_array($arParams["GADGETS"]) && !in_array($gadget_id, $arParams["GADGETS"]) && !in_array("ALL", $arParams["GADGETS"]))
					break;

				// получим пользовательские параметры гаджета
				$arGadget = BXGadget::GetById($gadget_id, true, $arParams);
				if($arGadget)
				{
					// получим значения параметров
					$arGadgetParams = $arGadget["USER_PARAMETERS"];
					foreach($arParams as $id=>$p)
					{
						$pref = "GU_".$gadget_id."_";
						if(strpos($id, $pref)===0)
							$arGadgetParams[substr($id, strlen($pref))]["VALUE"] = $p;
					}

					$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], false);
					if(is_array($arUserOptions) && is_array($arUserOptions["GADGETS"]) && is_array($arUserOptions["GADGETS"][$gdid]) && is_array($arUserOptions["GADGETS"][$gdid]["SETTINGS"]))
					{
						foreach($arUserOptions["GADGETS"][$gdid]["SETTINGS"] as $p=>$v)
							$arGadgetParams[$p]["VALUE"] = $v;
					}

					// вернем пользователю
					echo CUtil::PhpToJSObject($arGadgetParams);
				}
				break;

			case 'clear_settings':
				CUserOptions::DeleteOption("intranet", "~gadgets_".$arParams["ID"]);
				break;

			case 'save_default':
				GDCSaveSettings($arParams, $_REQUEST['POS']);
				$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], false);
				CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions, true);
				break;

			case 'update_position':
				GDCSaveSettings($arParams, $_REQUEST['POS']);
				break;
		}
	}
	else
		echo GetMessage("CMDESKTOP_AUTH_ERR");
	die();
}


$arResult["COLS"] = (intval($arParams["COLUMNS"])>0 && intval($arParams["COLUMNS"])<10)?intval($arParams["COLUMNS"]):3;
for($i=0; $i<$arResult["COLS"]; $i++)
	$arResult["COLUMN_WIDTH"][$i] = $arParams["COLUMN_WIDTH_".$i];

$arResult["GADGETS"] = Array();
$arResult["ID"] = $arParams["ID"];
$arParams["UPD_URL"] = $arResult["UPD_URL"] = POST_FORM_ACTION_URI;

$arGDList = Array();

$arUserOptions = false;
if($USER->IsAuthorized() && $arResult["PERMISSION"]>"R")
	$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], false);
else
	$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], false, 99999999);

$arGroups = Array(
		"personal" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_PERSONAL"),
				"DESCRIPTION" =>GetMessage("CMDESKTOP_GROUP_PERSONAL_DESCR"),
				"GADGETS" => Array(),
			),
		"employees" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_EMPL"),
				"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_EMPL_DESCR"),
				"GADGETS" => Array(),
			),
		"communications" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_COMMUN"),
				"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_COMMUN_DESCR"),
				"GADGETS" => Array(),
			),
		"company" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_COMPANY"),
				"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_COMPANY_DESCR"),
				"GADGETS" => Array(),
			),
		"services" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_SERVICES"),
				"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_SERVICES_DESCR"),
				"GADGETS" => Array(),
			),
		"other" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_OTHER"),
				"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_OTHER_DESCR"),
				"GADGETS" => Array(),
			),
	);


$arResult["ALL_GADGETS"] = Array();
$arGadgets = BXGadget::GetList();
foreach($arGadgets as $gadget)
{
	// если настройками запрещен этот гаджет, пропускаем
	if(is_array($arParams["GADGETS"]) && !in_array($gadget["ID"], $arParams["GADGETS"]) && !in_array("ALL", $arParams["GADGETS"]))
		continue;

	if($gadget["GROUP"]["ID"]=="")
		$gadget["GROUP"]["ID"] = "other";

	$arGroups[$gadget["GROUP"]["ID"]]["GADGETS"][] = $gadget["ID"];

	$arResult["ALL_GADGETS"][$gadget['ID']] = $gadget;
}

$arResult["GROUPS"] = Array();
foreach($arGroups as $arGroup)
	if(count($arGroup['GADGETS'])>0)
		$arResult['GROUPS'][] = $arGroup;

$arResult["GADGETS"] = Array();
for($i=0; $i<$arResult["COLS"]; $i++)
	$arResult["GADGETS"][$i] = Array();

// Уже настроенная страница
if(is_array($arUserOptions))
{
	//print_r($arUserOptions);
	$bForceRedirect = false;
	foreach($arUserOptions["GADGETS"] as $gdid=>$gadgetUserSettings)
	{
		$gadgetUserSettings = $arUserOptions["GADGETS"][$gdid];

		$p = strpos($gdid, "@");
		if($p === false)
		{
			$gadget_id = $gdid;
			$gdid = $gdid."@".rand();
		}
		else
		{
			$gadget_id = substr($gdid, 0, $p);
		}

		if($arResult["ALL_GADGETS"][$gadget_id])
		{
			$arGadgetParams = $gadgetUserSettings["SETTINGS"];

			$arGadget = $arResult["ALL_GADGETS"][$gadget_id];
			foreach($arParams as $id=>$p)
			{
				$pref = "G_".$gadget_id."_";
				if(strpos($id, $pref)===0)
					$arGadgetParams[substr($id, strlen($pref))]=$p;

				$pref = "GU_".$gadget_id."_";
				if(strpos($id, $pref)===0 && !isset($arGadgetParams[substr($id, strlen($pref))]))
					$arGadgetParams[substr($id, strlen($pref))]=$p;
			}

			if(intval($gadgetUserSettings["COLUMN"])<=0 || intval($gadgetUserSettings["COLUMN"])>=$arResult["COLS"])
				$arUserOptions["GADGETS"][$gdid]["COLUMN"] = 0;

			$arGCol = &$arResult["GADGETS"][$gadgetUserSettings["COLUMN"]];

			if(isset($arGCol[$gadgetUserSettings["ROW"]]))
			{
				ksort($arGCol, SORT_NUMERIC);
				$ks = array_keys($arGCol);
				$arUserOptions["GADGETS"][$gdid]["ROW"] = $ks[count($ks)-1] + 1;
			}

			$arGadget["ID"] = $gdid;
			$arGadget["GADGET_ID"] = $gadget_id;
			$arGadget["TITLE"] = htmlspecialchars($arGadget["NAME"]);
			$arGadget["SETTINGS"] = $arGadgetParams;
			$arGadget["HIDE"] = $gadgetUserSettings["HIDE"];
			if($arParams["PERMISSION"]>"R")
				$arGadget["USERDATA"] = &$arUserOptions["GADGETS"][$gdid]["USERDATA"];
			else
				$arGadget["USERDATA"] = $arUserOptions["GADGETS"][$gdid]["USERDATA"];
			$arGadget["CONTENT"] = BXGadget::GetGadgetContent(&$arGadget, $arParams);
			$arResult["GADGETS"][$gadgetUserSettings["COLUMN"]][$gadgetUserSettings["ROW"]] = $arGadget;
			if($arGadget["FORCE_REDIRECT"])
				$bForceRedirect = true;
		}
		else
		{
			unset($arUserOptions["GADGETS"][$gdid]);
		}
	}

	for($i=0; $i<$arResult["COLS"]; $i++)
		ksort($arResult["GADGETS"][$i], SORT_NUMERIC);
	//print_r($arUserOptions);
	CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions);
	if($bForceRedirect)
		LocalRedirect($_SERVER['REQUEST_URI']);
}
else
{
	/*
	foreach($arResult["ALL_GADGETS"] as $gadget_id=>$gd)
	{
		$arGadgetParams = Array();
		foreach($arParams as $id=>$p)
		{
			$pref = "G_".$gadget_id."_";
			if(strpos($id, $pref)===0)
				$arGadgetParams[substr($id, strlen($pref))]=$p;

			$pref = "GU_".$gadget_id."_";
			if(strpos($id, $pref)===0 && !isset($arGadgetParams[substr($id, strlen($pref))]))
			{
				$arGadgetParams[substr($id, strlen($pref))]=$p;
			}
		}

		$arGadget = Array(
					"ID"=>$gadget_id."@".rand(),
					"GADGET_ID"=>$gadget_id,
					"NAME"=>htmlspecialchars($arResult["ALL_GADGETS"][$gadget_id]["NAME"]),
					"OBJECT" => new BXGadget($arResult["ALL_GADGETS"][$gadget_id]["PATH"], $arParams, $arGadgetParams)
					);

		$min = 100;$min_i = 0;
		for($i=0; $i<$arResult["COLS"]; $i++)
		{
			if($min > count($arResult["GADGETS"][$i]))
			{
				$min = count($arResult["GADGETS"][$i]);
				$min_i = $i;
			}
		}

		$arResult["GADGETS"][$min_i][] = $arGadget;
	}
	*/
}


$js = '/bitrix/js/main/utils.js';
$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="'.$js.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$js).'"></script>');

$js = '/bitrix/js/main/popup_menu.js';
$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="'.$js.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$js).'"></script>');

$js = '/bitrix/js/main/ajax.js';
$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="'.$js.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$js).'"></script>');

$this->IncludeComponentTemplate();
?>
