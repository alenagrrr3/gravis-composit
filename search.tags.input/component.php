<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
// *******************************************************************
// *******************************************************************
if (CModule::IncludeModule("search")):

	if (!function_exists("GetTagsId"))
	{
		function GetTagsId($sName)
		{
			static $arPostfix = array();
			$sPostfix = rand();
			while (in_array($sPostfix, $arPostfix))
			{
				$sPostfix = rand();
			}
			array_push($arPostfix, $sPostfix);
			$sId = preg_replace("/\W/", "_", $sName);
			$sId = $sId.$sPostfix;
			return $sId;
		}
	}
// *******************************************************************
	$exFILTER = array();
	if(!empty($arParams["arrFILTER"]))
	{
		$strFILTER = $arParams["arrFILTER"];

		$arParams["arrFILTER_".$strFILTER] = trim($arParams["arrFILTER_".$strFILTER]);

		if($strFILTER=="main")
		{
			$sURL = "/%";
			if (!empty($arParams["arrFILTER_".$strFILTER]))
				$sURL = $arParams["arrFILTER_".$strFILTER]."%";
			$exFILTER=array(
				"MODULE_ID" => "main",
				"URL" => $sURL,
			);
		}
		elseif($strFILTER=="forum" && IsModuleInstalled("forum"))
		{
			if (!empty($arParams["arrFILTER_".$strFILTER]) && ($arParams["arrFILTER_".$strFILTER]<>"all"))
			{
				$exFILTER=array(
					"MODULE_ID" => "forum",
					"PARAM1" => $arParams["arrFILTER_".$strFILTER],
				);
			}
			else
			{
				$exFILTER=array(
					"MODULE_ID" => "forum",
				);
			}
		}
		elseif(strpos($strFILTER,"iblock_")===0)
		{
			if (!empty($arParams["arrFILTER_".$strFILTER]) && ($arParams["arrFILTER_".$strFILTER]<>"all"))
			{
				$exFILTER=array(
					"MODULE_ID" => "iblock",
					"PARAM1" => substr($strFILTER, 7),
					"PARAM2" => intVal($arParams["arrFILTER_".$strFILTER]),
				);
			}
			else
			{
				$exFILTER=array(
					"MODULE_ID" => "iblock",
					"PARAM1" => substr($strFILTER, 7),
				);
			}
		}
		elseif($strFILTER=="blog")
		{
			if (!empty($arParams["arrFILTER_".$strFILTER]) && ($arParams["arrFILTER_".$strFILTER]<>"all"))
			{
				$exFILTER=array(
					"MODULE_ID" => "blog",
					"PARAM1" => "POST",
					"PARAM2" => intVal($arParams["arrFILTER_".$strFILTER]),
				);
			}
			else
			{
				$exFILTER=array(
					"MODULE_ID" => "blog",
				);
			}
		}
		elseif($strFILTER=="socialnetwork")
		{
			$exFILTER=array(
				"MODULE_ID" => "socialnetwork",
			);
		}
		elseif($strFILTER=="intranet")
		{
			$exFILTER=array(
				"MODULE_ID" => "intranet",
			);
		}
	}
	$exFILTER["SITE_ID"] = (!empty($arParams["SITE_ID"]) ? $arParams["SITE_ID"] : SITE_ID);
	$arResult["exFILTER"] = $exFILTER;

	if (empty($arParams["NAME"]))
	{
		$arParams["NAME"] = "TAGS";
		$arParams["~NAME"] = "TAGS";
	}
	$arResult["ID"] = GetTagsId($arParams["NAME"]);
	$arResult["NAME"] = $arParams["NAME"];
	$arResult["~NAME"] = $arParams["~NAME"];
	$arResult["VALUE"] = $arParams["VALUE"];
	$arResult["~VALUE"] = $arParams["~VALUE"];
	$this->IncludeComponentTemplate();
else:
	ShowError(GetMessage("BSF_C_MODULE_NOT_INSTALLED"));
	return;
endif;?>