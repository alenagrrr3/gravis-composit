<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

$arParams["SECTION_SORT_FIELD"]=trim($arParams["SECTION_SORT_FIELD"]);
if($arParams["SECTION_SORT_ORDER"]!="desc")
	 $arParams["SECTION_SORT_ORDER"]="asc";
$arParams["SECTION_COUNT"] = intval($arParams["SECTION_COUNT"]);
if($arParams["SECTION_COUNT"]<=0)
	$arParams["SECTION_COUNT"]=20;

$arParams["ELEMENT_COUNT"] = intval($arParams["ELEMENT_COUNT"]);
if($arParams["ELEMENT_COUNT"]<=0)
	$arParams["ELEMENT_COUNT"]=9;
$arParams["LINE_ELEMENT_COUNT"] = intval($arParams["LINE_ELEMENT_COUNT"]);
if($arParams["LINE_ELEMENT_COUNT"]<=0)
	$arParams["LINE_ELEMENT_COUNT"]=3;
$arParams["ELEMENT_SORT_FIELD"]=trim($arParams["ELEMENT_SORT_FIELD"]);
if($arParams["ELEMENT_SORT_ORDER"]!="desc")
	 $arParams["ELEMENT_SORT_ORDER"]="asc";

if(strlen($arParams["FILTER_NAME"])<=0 || !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["FILTER_NAME"]))
{
	$arrFilter = array();
}
else
{
	global $$arParams["FILTER_NAME"];
	$arrFilter = ${$arParams["FILTER_NAME"]};
	if(!is_array($arrFilter))
		$arrFilter = array();
}

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

$arParams["DISPLAY_PANEL"] = $arParams["DISPLAY_PANEL"]=="Y"; //Turn off by default

$arParams["CACHE_FILTER"]=$arParams["CACHE_FILTER"]=="Y";
if(!$arParams["CACHE_FILTER"] && count($arrFilter)>0)
	$arParams["CACHE_TIME"] = 0;
//"hidden" parameter
$arParams["USE_RATING"] = $arParams["USE_RATING"]=="Y";

$arResult["SECTIONS"]=array();

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
$arResult["USER_HAVE_ACCESS"] = $bUSER_HAVE_ACCESS;

if($this->StartResultCache(false, array($arrFilter,$USER->GetGroups(),$bUSER_HAVE_ACCESS)))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	//WHERE
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y",
	);
	//ORDER BY
	$arSort = array(
		$arParams["SECTION_SORT_FIELD"] => $arParams["SECTION_SORT_ORDER"],
		"ID" => "ASC",
	);
	//EXECUTE
	$rsSections = CIBlockSection::GetList($arSort, $arFilter);
	$rsSections->SetUrlTemplates("", $arParams["SECTION_URL"]);

	//SELECT
	$arSelect = array_merge($arParams["FIELD_CODE"], array(
		"ID",
		"CODE",
		"IBLOCK_ID",
		"NAME",
		"PREVIEW_PICTURE",
		"DETAIL_PAGE_URL",
		"PREVIEW_TEXT_TYPE",
		"DETAIL_TEXT_TYPE",
	));
	$bGetProperty = $arParams["USE_RATING"] || count($arParams["PROPERTY_CODE"])>0;
	if($bGetProperty)
		$arSelect[]="PROPERTY_*";
	//WHERE
	$arrFilter["ACTIVE"] = "Y";
	$arrFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
	$arrFilter["ACTIVE_DATE"] = "Y";
	$arrFilter["CHECK_PERMISSIONS"] = "Y";
	//ORDER BY
	$arSort = array(
		$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
		"ID" => "ASC",
	);

	while($arSection = $rsSections->GetNext())
	{
		$arSection["ITEMS"] = array();

		//WHERE
		$arrFilter["SECTION_ID"] = $arSection["ID"];
		//EXECUTE
		$rsElements = CIBlockElement::GetList($arSort, $arrFilter, false, array("nTopCount"=>$arParams["ELEMENT_COUNT"]), $arSelect);
		$rsElements->SetUrlTemplates($arParams["DETAIL_URL"]);
		$rsElements->SetSectionContext($arSection);
		while($obElement = $rsElements->GetNextElement())
		{
			$arItem = $obElement->GetFields();
			if($bGetProperty)
				$arItem["PROPERTIES"] = $obElement->GetProperties();
			$arItem["DISPLAY_PROPERTIES"]=array();
			foreach($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arItem["PROPERTIES"][$pid];
				if((is_array($prop["VALUE"]) && count($prop["VALUE"])>0) ||
				   (!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0))
				{
					$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "photo_out");
				}
			}

			$arItem["PREVIEW_PICTURE"] = CFile::GetFileArray($arItem["PREVIEW_PICTURE"]);
			$arItem["DETAIL_PICTURE"] = CFile::GetFileArray($arItem["DETAIL_PICTURE"]);
			if(is_array($arItem["PREVIEW_PICTURE"]))
				$arItem["PICTURE"] = $arItem["PREVIEW_PICTURE"];
			elseif(is_array($arItem["DETAIL_PICTURE"]))
				$arItem["PICTURE"] = $arItem["DETAIL_PICTURE"];
			$arSection["ITEMS"][]=$arItem;
		}
		$arResult["SECTIONS"][]=$arSection;
		if(count($arResult["SECTIONS"])>=$arParams["SECTION_COUNT"])
			break;
	}
	//echo "<pre>",htmlspecialchars(print_r($arResult,true)),"</pre>";
	$this->SetResultCacheKeys(array(
	));
	$this->IncludeComponentTemplate();
}

if($USER->IsAuthorized())
{
	if($GLOBALS["APPLICATION"]->GetShowIncludeAreas() && CModule::IncludeModule("iblock"))
		$this->AddIncludeAreaIcons(CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, 0, $arParams["IBLOCK_TYPE"], true));
	if($arParams["DISPLAY_PANEL"] && CModule::IncludeModule("iblock"))
		CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, 0, $arParams["IBLOCK_TYPE"], false, $this->GetName());
}
?>
