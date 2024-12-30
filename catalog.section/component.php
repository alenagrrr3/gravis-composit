<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
$arParams["INCLUDE_SUBSECTIONS"] = $arParams["INCLUDE_SUBSECTIONS"]!="N"? "Y": "N";
$arParams["SHOW_ALL_WO_SECTION"] = $arParams["SHOW_ALL_WO_SECTION"]==="Y";

if(strlen($arParams["ELEMENT_SORT_FIELD"])<=0)
	$arParams["ELEMENT_SORT_FIELD"]="sort";
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

$arParams["SECTION_URL"]=trim($arParams["SECTION_URL"]);
$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);
$arParams["BASKET_URL"]=trim($arParams["BASKET_URL"]);
if(strlen($arParams["BASKET_URL"])<=0)
	$arParams["BASKET_URL"] = "/personal/basket.php";

$arParams["ACTION_VARIABLE"]=trim($arParams["ACTION_VARIABLE"]);
if(strlen($arParams["ACTION_VARIABLE"])<=0|| !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["ACTION_VARIABLE"]))
	$arParams["ACTION_VARIABLE"] = "action";
$arParams["PRODUCT_ID_VARIABLE"]=trim($arParams["PRODUCT_ID_VARIABLE"]);
if(strlen($arParams["PRODUCT_ID_VARIABLE"])<=0|| !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["PRODUCT_ID_VARIABLE"]))
	$arParams["PRODUCT_ID_VARIABLE"] = "id";
$arParams["SECTION_ID_VARIABLE"]=trim($arParams["SECTION_ID_VARIABLE"]);
if(strlen($arParams["SECTION_ID_VARIABLE"])<=0|| !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["SECTION_ID_VARIABLE"]))
	$arParams["SECTION_ID_VARIABLE"] = "SECTION_ID";

$arParams["SET_TITLE"] = $arParams["SET_TITLE"]!="N";
$arParams["DISPLAY_PANEL"] = $arParams["DISPLAY_PANEL"]=="Y";
$arParams["DISPLAY_COMPARE"] = $arParams["DISPLAY_COMPARE"]=="Y";

$arParams["PAGE_ELEMENT_COUNT"] = intval($arParams["PAGE_ELEMENT_COUNT"]);
if($arParams["PAGE_ELEMENT_COUNT"]<=0)
	$arParams["PAGE_ELEMENT_COUNT"]=20;
$arParams["LINE_ELEMENT_COUNT"] = intval($arParams["LINE_ELEMENT_COUNT"]);
if($arParams["LINE_ELEMENT_COUNT"]<=0)
	$arParams["LINE_ELEMENT_COUNT"]=3;

if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["PROPERTY_CODE"][$k]);

if(!is_array($arParams["PRICE_CODE"]))
	$arParams["PRICE_CODE"] = array();
$arParams["USE_PRICE_COUNT"] = $arParams["USE_PRICE_COUNT"]=="Y";
$arParams["SHOW_PRICE_COUNT"] = intval($arParams["SHOW_PRICE_COUNT"]);
if($arParams["SHOW_PRICE_COUNT"]<=0)
	$arParams["SHOW_PRICE_COUNT"]=1;

$arParams["DISPLAY_TOP_PAGER"] = $arParams["DISPLAY_TOP_PAGER"]=="Y";
$arParams["DISPLAY_BOTTOM_PAGER"] = $arParams["DISPLAY_BOTTOM_PAGER"]!="N";
$arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"]);
$arParams["PAGER_SHOW_ALWAYS"] = $arParams["PAGER_SHOW_ALWAYS"]!="N";
$arParams["PAGER_TEMPLATE"] = trim($arParams["PAGER_TEMPLATE"]);
$arParams["PAGER_DESC_NUMBERING"] = $arParams["PAGER_DESC_NUMBERING"]=="Y";
$arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] = intval($arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]);

$arNavParams = array("nPageSize"=>$arParams["PAGE_ELEMENT_COUNT"], "bDescPageNumbering"=>$arParams["PAGER_DESC_NUMBERING"]);
$arNavigation = CDBResult::GetNavParams($arNavParams);
if($arNavigation["PAGEN"]==0 && $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]>0)
	$arParams["CACHE_TIME"] = $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"];

$arParams["CACHE_FILTER"]=$arParams["CACHE_FILTER"]=="Y";
if(!$arParams["CACHE_FILTER"] && count($arrFilter)>0)
	$arParams["CACHE_TIME"] = 0;

$arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";

/*************************************************************************
			Processing of the Buy link
*************************************************************************/
$strError = "";
if (array_key_exists($arParams["ACTION_VARIABLE"], $_REQUEST) && array_key_exists($arParams["PRODUCT_ID_VARIABLE"], $_REQUEST))
{
	$action = strtoupper($_REQUEST[$arParams["ACTION_VARIABLE"]]);
	$productID = intval($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]]);
	if(($action == "ADD2BASKET" || $action == "BUY") && $productID > 0)
	{
		if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
		{
			if(Add2BasketByProductID($productID))
			{
				if($action == "BUY")
					LocalRedirect($arParams["BASKET_URL"]);
				else
					LocalRedirect($APPLICATION->GetCurPageParam("", array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			}
			else
			{
				if($ex = $GLOBALS["APPLICATION"]->GetException())
					$strError = $ex->GetString();
				else
					$strError = GetMessage("CATALOG_ERROR2BASKET").".";
			}
		}
	}
}
if(strlen($strError)>0)
{
	ShowError($strError);
	return;
}
/*************************************************************************
			Work with cache
*************************************************************************/
if($this->StartResultCache(false, array($arrFilter, $USER->GetGroups(), $arNavigation)))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}

	$arSelect = array(
		$arParams["META_KEYWORDS"],
		$arParams["META_DESCRIPTION"],
	);
	$arFilter = array(
		"ACTIVE"=>"Y",
		"GLOBAL_ACTIVE"=>"Y",
		"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE"=>"Y",
	);

	$bSectionFound = false;
	//Hidden triky parameter USED to display linked
	//by default it is not set
	if($arParams["BY_LINK"]==="Y")
	{
		$arResult = array(
			"ID" => 0,
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		);
		$bSectionFound = true;
	}
	elseif(strlen($arParams["SECTION_CODE"]) > 0)
	{
		$arFilter["CODE"]=$arParams["SECTION_CODE"];
		$rsSection = CIBlockSection::GetList(Array(), $arFilter, false, $arSelect);
		$rsSection->SetUrlTemplates("", $arParams["SECTION_URL"]);
		$arResult = $rsSection->GetNext();
		if($arResult)
			$bSectionFound = true;
	}
	elseif($arParams["SECTION_ID"])
	{
		$arFilter["ID"]=$arParams["SECTION_ID"];
		$rsSection = CIBlockSection::GetList(Array(), $arFilter, false, $arSelect);
		$rsSection->SetUrlTemplates("", $arParams["SECTION_URL"]);
		$arResult = $rsSection->GetNext();
		if($arResult)
			$bSectionFound = true;
	}
	else
	{
		//Root section (no section filter)
		$arResult = array(
			"ID" => 0,
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		);
		$bSectionFound = true;
	}

	if(!$bSectionFound)
	{
		$this->AbortResultCache();
		ShowError(GetMessage("CATALOG_SECTION_NOT_FOUND"));
		@define("ERROR_404", "Y");
		if($arParams["SET_STATUS_404"]==="Y")
			CHTTP::SetStatus("404 Not Found");
		return;
	}

	//This function returns array with prices description and access rights
	//in case catalog module n/a prices get values from element properties
	$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices($arParams["IBLOCK_ID"], $arParams["PRICE_CODE"]);

	$arResult["PICTURE"] = CFile::GetFileArray($arResult["PICTURE"]);
	$arResult["DETAIL_PICTURE"] = CFile::GetFileArray($arResult["DETAIL_PICTURE"]);

	// list of the element fields that will be used in selection
	$arSelect = array(
		"ID",
		"NAME",
		"CODE",
		"DATE_CREATE",
		"ACTIVE_FROM",
		"CREATED_BY",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"DETAIL_PAGE_URL",
		"DETAIL_TEXT",
		"DETAIL_TEXT_TYPE",
		"DETAIL_PICTURE",
		"PREVIEW_TEXT",
		"PREVIEW_TEXT_TYPE",
		"PREVIEW_PICTURE",
		"PROPERTY_*",
	);
	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_LID" => SITE_ID,
		"IBLOCK_ACTIVE" => "Y",
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
		"INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
	);

	if($arParams["BY_LINK"]!=="Y")
	{
		if($arResult["ID"])
			$arFilter["SECTION_ID"] = $arResult["ID"];
		elseif(!$arParams["SHOW_ALL_WO_SECTION"])
			$arFilter["SECTION_ID"] = 0;
	}

	//PRICES
	if(!$arParams["USE_PRICE_COUNT"])
	{
		foreach($arResult["PRICES"] as $key => $value)
		{
			$arSelect[] = $value["SELECT"];
			$arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $arParams["SHOW_PRICE_COUNT"];
		}
	}

	$arSort = array(
		$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
		"ID" => "DESC",
	);
	//EXECUTE
	$rsElements = CIBlockElement::GetList($arSort, array_merge($arrFilter, $arFilter), false, $arNavParams, $arSelect);
	$rsElements->SetUrlTemplates($arParams["DETAIL_URL"]);
	$rsElements->SetSectionContext($arResult);
	$arResult["ITEMS"] = array();
	while($obElement = $rsElements->GetNextElement())
	{
		$arItem = $obElement->GetFields();

		if($arResult["ID"])
			$arItem["IBLOCK_SECTION_ID"] = $arResult["ID"];

		$arItem["PREVIEW_PICTURE"] = CFile::GetFileArray($arItem["PREVIEW_PICTURE"]);
		$arItem["DETAIL_PICTURE"] = CFile::GetFileArray($arItem["DETAIL_PICTURE"]);

		if(count($arParams["PROPERTY_CODE"]) > 0)
			$arItem["PROPERTIES"] = $obElement->GetProperties();

		$arItem["DISPLAY_PROPERTIES"] = array();
		foreach($arParams["PROPERTY_CODE"] as $pid)
		{
			$prop = &$arItem["PROPERTIES"][$pid];
			if(
				(is_array($prop["VALUE"]) && count($prop["VALUE"]) > 0)
				|| (!is_array($prop["VALUE"]) && strlen($prop["VALUE"]) > 0)
			)
			{
				$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "catalog_out");
			}
		}

		if($arParams["USE_PRICE_COUNT"])
		{
			if(CModule::IncludeModule("catalog"))
			{
				$arItem["PRICE_MATRIX"] = CatalogGetPriceTableEx($arItem["ID"]);
				foreach($arItem["PRICE_MATRIX"]["COLS"] as $keyColumn=>$arColumn)
					$arItem["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"] = htmlspecialcharsex($arColumn["NAME_LANG"]);
			}
			else
			{
				$arItem["PRICE_MATRIX"] = false;
			}
			$arItem["PRICES"] = array();
		}
		else
		{
			$arItem["PRICE_MATRIX"] = false;
			$arItem["PRICES"] = CIBlockPriceTools::GetItemPrices($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem, $arParams['PRICE_VAT_INCLUDE']);
		}
		$arItem["CAN_BUY"] = CIBlockPriceTools::CanBuy($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem);

		$arItem["BUY_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=BUY&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
		$arItem["ADD_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=ADD2BASKET&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
		$arItem["COMPARE_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam("action=ADD_TO_COMPARE_LIST&id=".$arItem["ID"], array("action", "id")));

		$arItem["SECTION"]["PATH"] = array();
		if($arParams["BY_LINK"]==="Y")
		{
			$rsPath = GetIBlockSectionPath($arItem["IBLOCK_ID"], $arItem["IBLOCK_SECTION_ID"]);
			$rsPath->SetUrlTemplates("", $arParams["SECTION_URL"]);
			while($arPath = $rsPath->GetNext())
			{
				$arItem["SECTION"]["PATH"][]=$arPath;
			}
		}

		$arResult["ITEMS"][]=$arItem;
	}

	$arResult["NAV_STRING"] = $rsElements->GetPageNavStringEx($navComponentObject, $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"], $arParams["PAGER_SHOW_ALWAYS"]);
	$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
	$arResult["NAV_RESULT"] = $rsElements;

	$this->SetResultCacheKeys(array(
		"ID",
		"NAV_CACHED_DATA",
		$arParams["META_KEYWORDS"],
		$arParams["META_DESCRIPTION"],
		"NAME",
	));
	$this->IncludeComponentTemplate();
}

if($USER->IsAuthorized())
{
	if($GLOBALS["APPLICATION"]->GetShowIncludeAreas() && CModule::IncludeModule("iblock"))
		$this->AddIncludeAreaIcons(CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arResult["ID"], $arParams["IBLOCK_TYPE"], true));
	if($arParams["DISPLAY_PANEL"] && CModule::IncludeModule("iblock"))
		CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arResult["ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
}

$this->SetTemplateCachedData($arResult["NAV_CACHED_DATA"]);

if(isset($arResult[$arParams["META_KEYWORDS"]]))
{
	$val = $arResult[$arParams["META_KEYWORDS"]];
	if(is_array($val))
		$val = implode(" ", $val);
	$APPLICATION->SetPageProperty("keywords", $val);
}

if(isset($arResult[$arParams["META_DESCRIPTION"]]))
{
	$val = $arResult[$arParams["META_DESCRIPTION"]];
	if(is_array($val))
		$val = implode(" ", $val);
	$APPLICATION->SetPageProperty("description", $val);
}

if($arParams["SET_TITLE"])
	$APPLICATION->SetTitle($arResult["NAME"]);

?>
