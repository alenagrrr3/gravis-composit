<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return false;

//prepare params
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
if($arParams['IBLOCK_ID']<=0)
	return false;
	
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);
if($arParams["SECTION_ID"]<=0 || $arParams["ELEMENT_ID"]<=0)
	return false;

if(isset($arParams["IBLOCK_TYPE"]) && $arParams["IBLOCK_TYPE"]!='')
	$arFilter['IBLOCK_TYPE'] = $arParams["IBLOCK_TYPE"];

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

//SELECT
$arSelect = Array(
	"ID",
	"NAME", 
	"IBLOCK_SECTION_ID",
	"PREVIEW_TEXT_TYPE",
	"PREVIEW_TEXT",
	"DETAIL_TEXT_TYPE",
	"DETAIL_TEXT",
);
//WHERE
$arFilter = Array(
	'IBLOCK_ID' => $arParams["IBLOCK_ID"],
	'ACTIVE' => 'Y',
	'IBLOCK_ACTIVE' => 'Y',
	'SECTION_ID' => $arParams["SECTION_ID"],
	'ELEMENT_ID' => $arParams["ELEMENT_ID"],
);
//ORDER BY
$arOrder = Array(
	'SORT' => 'ASC',
	'ID' => 'DESC',
);

$arAddCacheParams = array(
	"MODE" => $_REQUEST['bitrix_show_mode']?$_REQUEST['bitrix_show_mode']:'view',
	"SESS_MODE" => $_SESSION['SESS_PUBLIC_SHOW_MODE']?$_SESSION['SESS_PUBLIC_SHOW_MODE']:'view',
);

//**work body**//
if($this->StartResultCache(false, array($USER->GetGroups(), serialize($arFilter), serialize($arAddCacheParams))))
{
	$arItem = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
	if($arResItem = $arItem->Fetch())
		$arResult['ITEM'] = $arResItem;

	if(!isset($arResult['ITEM']))
	{
		$this->AbortResultCache();
		@define("ERROR_404", "Y");
		return;
	}
	//include template
	$this->IncludeComponentTemplate();
}
?>