<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"]);
$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);
$arParams["ELEMENT_CODE"] = trim($arParams["ELEMENT_CODE"]);
$arParams["ELEMENT_SORT_FIELD"] = trim($arParams["ELEMENT_SORT_FIELD"]);
if($arParams["ELEMENT_SORT_ORDER"]!="desc")
	 $arParams["ELEMENT_SORT_ORDER"]="asc";

if(!is_array($arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"] = array();
foreach($arParams["FIELD_CODE"] as $key=>$val)
	if($val==="")
		unset($arParams["FIELD_CODE"][$key]);

if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $key=>$val)
	if($val==="")
		unset($arParams["PROPERTY_CODE"][$key]);

$arParams["SECTION_URL"]=trim($arParams["SECTION_URL"]);
$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);

$arParams["META_KEYWORDS"]=trim($arParams["META_KEYWORDS"]);
if(strlen($arParams["META_KEYWORDS"])<=0)
	$arParams["META_KEYWORDS"] = "-";
$arParams["META_DESCRIPTION"]=trim($arParams["META_DESCRIPTION"]);
if(strlen($arParams["META_DESCRIPTION"])<=0)
	$arParams["META_DESCRIPTION"] = "-";

$arParams["DISPLAY_PANEL"] = $arParams["DISPLAY_PANEL"]=="Y"; //Turn off by default
$arParams["SET_TITLE"] = $arParams["SET_TITLE"]!="N"; //Turn on by default

$arParams["SHOW_WORKFLOW"] = $_REQUEST["show_workflow"]=="Y";

$arParams["USE_PERMISSIONS"] = $arParams["USE_PERMISSIONS"]=="Y";
if(!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);

$bUSER_HAVE_ACCESS = !$arParams["USE_PERMISSIONS"];
if($arParams["USE_PERMISSIONS"] && isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]))
{
	$arUserGroupArray = $GLOBALS["USER"]->GetUserGroupArray();
	foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
	{
		if(in_array($PERM, $arUserGroupArray))
		{
			$bUSER_HAVE_ACCESS = true;
			break;
		}
	}
}
if(!$bUSER_HAVE_ACCESS)
{
	ShowError(GetMessage("T_DETAIL_PERM_DEN"));
	return 0;
}

/*************************************************************************
			Start caching
*************************************************************************/

if($arParams["SHOW_WORKFLOW"] || $this->StartResultCache(false, $USER->GetGroups()))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}

	if($arParams["ELEMENT_ID"] > 0)
		$ELEMENT_ID = $arParams["ELEMENT_ID"];
	else//Handle case when ELEMENT_CODE used
		$ELEMENT_ID = CIBlockFindTools::GetElementID(
			$arParams["ELEMENT_ID"],
			$arParams["ELEMENT_CODE"],
			false,
			false,
			array(
				"IBLOCK_ACTIVE" => "Y",
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"ACTIVE_DATE" => "Y",
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "Y",
			)
		);

	if($ELEMENT_ID)
	{
		$WF_SHOW_HISTORY = "N";
		if ($arParams["SHOW_WORKFLOW"] && CModule::IncludeModule("workflow"))
		{
			$WF_ELEMENT_ID = CIBlockElement::WF_GetLast($ELEMENT_ID);

			$WF_STATUS_ID = CIBlockElement::WF_GetCurrentStatus($WF_ELEMENT_ID, $WF_STATUS_TITLE);
			$WF_STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($WF_STATUS_ID);

			if ($WF_STATUS_ID == 1 || $WF_STATUS_PERMISSION < 1)
				$WF_ELEMENT_ID = $ELEMENT_ID;
			else
				$WF_SHOW_HISTORY = "Y";

			$ELEMENT_ID = $WF_ELEMENT_ID;
		}
		//SELECT
		$arSelect = array_merge($arParams["FIELD_CODE"], array(
			"ID",
			"CODE",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"SECTION_PAGE_URL",
			"NAME",
			"DETAIL_PICTURE",
			"PREVIEW_PICTURE",
			"DETAIL_TEXT",
			"DETAIL_PAGE_URL",
			"PREVIEW_TEXT_TYPE",
			"DETAIL_TEXT_TYPE",
		));
		$bGetProperty = count($arParams["PROPERTY_CODE"])>0 || $arParams["META_KEYWORDS"]!="-" || $arParams["META_DESCRIPTION"]!="-";
		if($bGetProperty)
			$arSelect[]="PROPERTY_*";
		//WHERE
		$arFilter = array(
			"ID" => $ELEMENT_ID,
			"IBLOCK_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
			"SHOW_HISTORY" => $WF_SHOW_HISTORY,
		);
		if($arParams["SECTION_ID"])
			$arFilter["SECTION_ID"]=$arParams["SECTION_ID"];
		elseif($arParams["SECTION_CODE"])
			$arFilter["SECTION_CODE"]=$arParams["SECTION_CODE"];
		//ORDER BY
		$arSort = array(
		);
		//EXECUTE
		$rsElement = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
		$rsElement->SetUrlTemplates($arParams["DETAIL_URL"], $arParams["SECTION_URL"]);
		if($obElement = $rsElement->GetNextElement())
		{
			$arResult = $obElement->GetFields();

			if($bGetProperty)
				$arResult["PROPERTIES"] = $obElement->GetProperties();
			$arResult["DISPLAY_PROPERTIES"]=array();
			foreach($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arResult["PROPERTIES"][$pid];
				if((is_array($prop["VALUE"]) && count($prop["VALUE"])>0) ||
				   (!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0))
				{
					$arResult["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arResult, $prop, "photo_out");
				}
			}

			if($arParams["SECTION_ID"])
				$arResult["IBLOCK_SECTION_ID"]=$arParams["SECTION_ID"];
			elseif($arParams["SECTION_CODE"])
				$arResult["IBLOCK_SECTION_CODE"]=$arParams["SECTION_CODE"];

			if(isset($arResult["PREVIEW_PICTURE"]))
				$arResult["PREVIEW_PICTURE"] = CFile::GetFileArray($arResult["PREVIEW_PICTURE"]);
			if(isset($arResult["DETAIL_PICTURE"]))
				$arResult["DETAIL_PICTURE"] = CFile::GetFileArray($arResult["DETAIL_PICTURE"]);
			if(is_array($arResult["DETAIL_PICTURE"]))
				$arResult["PICTURE"] = $arResult["DETAIL_PICTURE"];
			elseif(is_array($arResult["PREVIEW_PICTURE"]))
				$arResult["PICTURE"] = $arResult["PREVIEW_PICTURE"];

			$arSectionFilter = array(
				"IBLOCK_ID"=>$arResult["IBLOCK_ID"],
				"ACTIVE" => "Y",
			);
			if($arParams["SECTION_ID"])
				$arSectionFilter["ID"]=$arParams["SECTION_ID"];
			elseif($arParams["SECTION_CODE"])
				$arSectionFilter["CODE"]=$arParams["SECTION_CODE"];

			$rsSection = CIBlockSection::GetList(Array(),$arSectionFilter);
			$rsSection->SetUrlTemplates("", $arParams["SECTION_URL"]);
			if($arResult["SECTION"] = $rsSection->GetNext())
			{
				$arResult["SECTION"]["PATH"] = array();
				$rsPath = GetIBlockSectionPath($arResult["SECTION"]["IBLOCK_ID"], $arResult["SECTION"]["ID"]);
				$rsPath->SetUrlTemplates("", $arParams["SECTION_URL"]);
				while($arPath=$rsPath->GetNext())
				{
					$arResult["SECTION"]["PATH"][] = $arPath;
				}
			}
		}
	}
	// get the values for the Next and Previous links
	if(isset($arResult["ID"]))
	{
		//SELECT
		$arSelect = array(
			"ID",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"DETAIL_PAGE_URL",
			"NAME",
			"PREVIEW_PICTURE",
		);
		//WHERE
		$arFilter = array(
			"IBLOCK_ID" => $arResult["IBLOCK_ID"],
			"SECTION_ID" => $arResult["SECTION"]["ID"],
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
		);
		//ORDER BY
		$arSort = array(
			$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
			"ID" => "ASC",
		);
		//EXECUTE
		$arResult["NEXT"] = array();
		$arResult["PREV"] = array();
		$rsElement = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
		$bEndOfDataSet = true;
		$end = false;
		$i = 0;
		while($arElement = $rsElement->GetNext())
		{
			$i++;
			$arElement["DETAIL_PAGE_URL"] = htmlspecialchars(str_replace(
				array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#", "#SECTION_CODE#", "#ELEMENT_ID#", "#ELEMENT_CODE#"),
				array(SITE_SERVER_NAME, SITE_DIR, $arResult["IBLOCK_ID"], $arResult["SECTION"]["ID"], $arResult["SECTION"]["CODE"], $arElement["ID"], $arElement["CODE"]),
				$arParams["DETAIL_URL"]
			));
			if($end)
			{
				$arResult["NEXT"][] = $arElement;
			}
			if(count($arResult["NEXT"])>=2)
			{
				$bEndOfDataSet = false;
				break;
			}
			if($arElement["ID"]==$arResult["ID"])
			{
				$end = true;
				$arResult["CURRENT"]["NO"]=$i;
			}
			elseif(!$end)
			{
				array_unshift($arResult["PREV"],$arElement);
				if(count($arResult["PREV"])>2)
					array_pop($arResult["PREV"]);
			}
		}
		if(!$bEndOfDataSet)
		{
			while($rsElement->Fetch())
				$i++;
		}
		$arResult["CURRENT"]["COUNT"]=$i;

		foreach($arResult["NEXT"] as $key=>$value)
			$arResult["NEXT"][$key]["PICTURE"] = CFile::GetFileArray($value["PREVIEW_PICTURE"]);
		foreach($arResult["PREV"] as $key=>$value)
			$arResult["PREV"][$key]["PICTURE"] = CFile::GetFileArray($value["PREVIEW_PICTURE"]);
		//echo "<pre>",htmlspecialchars(print_r($arResult,true)),"</pre>";
		$this->SetResultCacheKeys(array(
			"ID",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"PROPERTIES",
			"NAME",
			"SECTION",
		));
		$this->IncludeComponentTemplate();
	}
	else
	{
		$this->AbortResultCache();
		ShowError(GetMessage("PHOTO_ELEMENT_NOT_FOUND"));
		@define("ERROR_404", "Y");
		if($arParams["SET_STATUS_404"]==="Y")
			CHTTP::SetStatus("404 Not Found");
	}
}

/*************************************************************************
	Any actions without cache (if there was some to display)
*************************************************************************/
if(isset($arResult["ID"]))
{
	if(CModule::IncludeModule("iblock"))
	{
		CIBlockElement::CounterInc($arResult["ID"]);
		if($USER->IsAuthorized())
		{
			if($GLOBALS["APPLICATION"]->GetShowIncludeAreas())
				$this->AddIncludeAreaIcons(CIBlock::ShowPanel($arResult["IBLOCK_ID"], $arResult["ID"], $arResult["IBLOCK_SECTION_ID"], $arParams["IBLOCK_TYPE"], true));
			if($arParams["DISPLAY_PANEL"])
				CIBlock::ShowPanel($arResult["IBLOCK_ID"], $arResult["ID"], $arResult["IBLOCK_SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
		}
	}
	if(isset($arResult["PROPERTIES"][$arParams["META_KEYWORDS"]]))
	{
		$val = $arResult["PROPERTIES"][$arParams["META_KEYWORDS"]]["VALUE"];
		if(is_array($val))
			$val = implode(" ", $val);
		$APPLICATION->SetPageProperty("keywords", $val);
	}

	if(isset($arResult["PROPERTIES"][$arParams["META_DESCRIPTION"]]))
	{
		$val = $arResult["PROPERTIES"][$arParams["META_DESCRIPTION"]]["VALUE"];
		if(is_array($val))
			$val = implode(" ", $val);
		$APPLICATION->SetPageProperty("description", $val);
	}

	if($arParams["SET_TITLE"])
		$APPLICATION->SetTitle($arResult["NAME"]);

	if(is_array($arResult["SECTION"]))
	{
		foreach($arResult["SECTION"]["PATH"] as $arPath)
		{
			$APPLICATION->AddChainItem($arPath["NAME"], $arPath["~SECTION_PAGE_URL"]);
		}
	}

	return $arResult["ID"];
}
?>
