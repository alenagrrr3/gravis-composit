<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams["IBLOCK_TYPE"]=trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_URL"]=trim($arParams["IBLOCK_URL"]);
$arParams["DISPLAY_PANEL"] = $arParams["DISPLAY_PANEL"]=="Y";

/*************************************************************************
			Work with cache
*************************************************************************/
$arResult["ITEMS"] = array();

if($this->StartResultCache(false, $USER->GetGroups()))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	//WHERE
	$arFilter = array(
		"TYPE" => $arParams["IBLOCK_TYPE"],
		"SITE_ID" => SITE_ID,
		"ACTIVE" => "Y",
	);
	//ORDER BY
	$arSort = array(
		"SORT" => "ASC",
		"NAME" => "ASC",
	);

	$rsIBlocks = CIBlock::GetList($arSort, $arFilter);

	while($arIBlock = $rsIBlocks->GetNext())
	{
		$arIBlock["PICTURE"] = CFile::GetFileArray($arIBlock["PICTURE"]);

		$arIBlock["~LIST_PAGE_URL"] = str_replace(
			array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_TYPE_ID#", "#IBLOCK_ID#", "#IBLOCK_CODE#", "#IBLOCK_EXTERNAL_ID#", "#CODE#"),
			array(SITE_SERVER_NAME, SITE_DIR, $arIBlock["IBLOCK_TYPE_ID"], $arIBlock["ID"], $arIBlock["CODE"], $arIBlock["EXTERNAL_ID"], $arIBlock["CODE"]),
			strlen($arParams["IBLOCK_URL"])? trim($arParams["~IBLOCK_URL"]): $arIBlock["~LIST_PAGE_URL"]
		);
		$arIBlock["~LIST_PAGE_URL"] = preg_replace("'/+'s", "/", $arIBlock["~LIST_PAGE_URL"]);
		$arIBlock["LIST_PAGE_URL"] = htmlspecialchars($arIBlock["~LIST_PAGE_URL"]);

		$arResult["ITEMS"][]=$arIBlock;
	}
	$this->IncludeComponentTemplate();
}

if(count($arResult["ITEMS"])>0)
{
	if($USER->IsAuthorized())
	{
		if($GLOBALS["APPLICATION"]->GetShowIncludeAreas() && CModule::IncludeModule("iblock"))
			$this->AddIncludeAreaIcons(CIBlock::ShowPanel(0, 0, 0, $arParams["IBLOCK_TYPE"], true));
		if($arParams["DISPLAY_PANEL"] && CModule::IncludeModule("iblock"))
			CIBlock::ShowPanel(0, 0, 0, $arParams["IBLOCK_TYPE"], false, $this->GetName());
	}
}
?>
