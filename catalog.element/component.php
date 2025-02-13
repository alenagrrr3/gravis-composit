<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return 0;
}
/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);

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
$arParams["META_KEYWORDS"]=trim($arParams["META_KEYWORDS"]);
if(strlen($arParams["META_KEYWORDS"])<=0)
	$arParams["META_KEYWORDS"] = "-";
$arParams["META_DESCRIPTION"]=trim($arParams["META_DESCRIPTION"]);
if(strlen($arParams["META_DESCRIPTION"])<=0)
	$arParams["META_DESCRIPTION"] = "-";

$arParams["SET_TITLE"] = $arParams["SET_TITLE"]!="N";
$arParams["DISPLAY_PANEL"] = $arParams["DISPLAY_PANEL"]=="Y";
$arParams["ADD_SECTIONS_CHAIN"] = $arParams["ADD_SECTIONS_CHAIN"]!="N"; //Turn on by default

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

$arParams["LINK_IBLOCK_TYPE"] = trim($arParams["LINK_IBLOCK_TYPE"]);
$arParams["LINK_IBLOCK_ID"] = intval($arParams["LINK_IBLOCK_ID"]);
$arParams["LINK_PROPERTY_SID"] = trim($arParams["LINK_PROPERTY_SID"]);
$arParams["LINK_ELEMENTS_URL"]=trim($arParams["LINK_ELEMENTS_URL"]);
if(strlen($arParams["LINK_ELEMENTS_URL"])<=0)
	$arParams["LINK_ELEMENTS_URL"] = "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#";

$arParams["SHOW_WORKFLOW"] = $_REQUEST["show_workflow"]=="Y";
if($arParams["SHOW_WORKFLOW"])
	$arParams["CACHE_TIME"] = 0;

$arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";
$arParams["PRICE_VAT_SHOW_VALUE"] = $arParams["PRICE_VAT_SHOW_VALUE"] === "Y";
/*************************************************************************
			Processing of the Buy link
*************************************************************************/
$strError = "";
if (array_key_exists($arParams["ACTION_VARIABLE"], $_REQUEST) && array_key_exists($arParams["PRODUCT_ID_VARIABLE"], $_REQUEST))
{
	$action = strtoupper($_REQUEST[$arParams["ACTION_VARIABLE"]]);
	$productID = intval($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]]);
	if (($action == "ADD2BASKET" || $action == "BUY") && $productID > 0)
	{
		if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
		{
			if (Add2BasketByProductID($productID))
			{
				if ($action == "BUY")
					LocalRedirect($arParams["BASKET_URL"]);
				else
					LocalRedirect($APPLICATION->GetCurPageParam("", array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			}
			else
			{
				if ($ex = $GLOBALS["APPLICATION"]->GetException())
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
	return 0;
}

CIBlockElement::CounterInc($arParams["ELEMENT_ID"]);

/*************************************************************************
			Work with cache
*************************************************************************/
if($this->StartResultCache(false, $USER->GetGroups()))
{

	//Handle case when ELEMENT_CODE used
	if($arParams["ELEMENT_ID"] <= 0)
		$arParams["ELEMENT_ID"] = CIBlockFindTools::GetElementID(
			$arParams["ELEMENT_ID"],
			$arParams["ELEMENT_CODE"],
			false,
			false,
			array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"IBLOCK_LID" => SITE_ID,
				"IBLOCK_ACTIVE" => "Y",
				"ACTIVE_DATE" => "Y",
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "Y",
			)
		);

	if($arParams["ELEMENT_ID"] > 0)
	{
		//This function returns array with prices description and access rights
		//in case catalog module n/a prices get values from element properties
		$arResultPrices = CIBlockPriceTools::GetCatalogPrices($arParams["IBLOCK_ID"], $arParams["PRICE_CODE"]);

		$WF_SHOW_HISTORY = "N";
		if ($arParams["SHOW_WORKFLOW"] && CModule::IncludeModule("workflow"))
		{
			$WF_ELEMENT_ID = CIBlockElement::WF_GetLast($arParams["ELEMENT_ID"]);

			$WF_STATUS_ID = CIBlockElement::WF_GetCurrentStatus($WF_ELEMENT_ID, $WF_STATUS_TITLE);
			$WF_STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($WF_STATUS_ID);

			if ($WF_STATUS_ID == 1 || $WF_STATUS_PERMISSION < 1)
				$WF_ELEMENT_ID = $arParams["ELEMENT_ID"];
			else
				$WF_SHOW_HISTORY = "Y";

			$arParams["ELEMENT_ID"] = $WF_ELEMENT_ID;
		}
		//SELECT
		$arSelect = array(
			"ID",
			"NAME",
			"CODE",
			"ACTIVE_FROM",
			"DATE_CREATE",
			"CREATED_BY",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"DETAIL_TEXT",
			"DETAIL_TEXT_TYPE",
			"DETAIL_PICTURE",
			"PREVIEW_TEXT",
			"PREVIEW_TEXT_TYPE",
			"PREVIEW_PICTURE",
			"PROPERTY_*",
		);
		//WHERE
		$arFilter = array(
			"ID" => $arParams["ELEMENT_ID"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_LID" => SITE_ID,
			"IBLOCK_ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
			"SHOW_HISTORY" => $WF_SHOW_HISTORY,
		);
		//ORDER BY
		$arSort = array(
		);
		//PRICES
		if(!$arParams["USE_PRICE_COUNT"])
		{
			foreach($arResultPrices as $key => $value)
			{
				$arSelect[] = $value["SELECT"];
				$arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $arParams["SHOW_PRICE_COUNT"];
			}
		}

		$arSection = false;
		if($arParams["SECTION_ID"] > 0 || strlen($arParams["SECTION_CODE"]) > 0)
		{
			$arSectionFilter = array(
				"IBLOCK_ID"=>$arResult["IBLOCK_ID"],
				"ACTIVE" => "Y",
			);
			if($arParams["SECTION_ID"] > 0)
				$arSectionFilter["ID"]=$arParams["SECTION_ID"];
			else
				$arSectionFilter["CODE"]=$arParams["SECTION_CODE"];

			$rsSection = CIBlockSection::GetList(array(), $arSectionFilter);
			$rsSection->SetUrlTemplates("", $arParams["SECTION_URL"]);
			$arSection = $rsSection->GetNext();
		}

		$rsElement = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
		$rsElement->SetUrlTemplates($arParams["DETAIL_URL"]);
		$rsElement->SetSectionContext($arSection);
		if($obElement = $rsElement->GetNextElement())
		{
			$arResult=$obElement->GetFields();

			$arResult["CAT_PRICES"] = $arResultPrices;

			$arResult["PREVIEW_PICTURE"] = CFile::GetFileArray($arResult["PREVIEW_PICTURE"]);
			$arResult["DETAIL_PICTURE"] = CFile::GetFileArray($arResult["DETAIL_PICTURE"]);

			$arResult["PROPERTIES"] = $obElement->GetProperties();

			$arResult["DISPLAY_PROPERTIES"] = array();
			foreach($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arResult["PROPERTIES"][$pid];
				if((is_array($prop["VALUE"]) && count($prop["VALUE"])>0) ||
				(!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0))
				{
					$arResult["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arResult, $prop, "catalog_out");
				}
			}

			$arResult["MORE_PHOTO"] = array();
			if(isset($arResult["PROPERTIES"]["MORE_PHOTO"]["VALUE"]) && is_array($arResult["PROPERTIES"]["MORE_PHOTO"]["VALUE"]))
			{
				foreach($arResult["PROPERTIES"]["MORE_PHOTO"]["VALUE"] as $FILE)
				{
					$FILE = CFile::GetFileArray($FILE);
					if(is_array($FILE))
						$arResult["MORE_PHOTO"][]=$FILE;
				}
			}

			$arResult["LINKED_ELEMENTS"] = array();
			if(strlen($arParams["LINK_PROPERTY_SID"])>0 && strlen($arParams["LINK_IBLOCK_TYPE"])>0 && $arParams["LINK_IBLOCK_ID"]>0)
			{
				$rsLinkElements = CIBlockElement::GetList(
					array("SORT" => "ASC"),
					array(
						"IBLOCK_ID" => $arParams["LINK_IBLOCK_ID"],
						"IBLOCK_ACTIVE" => "Y",
						"ACTIVE_DATE" => "Y",
						"ACTIVE" => "Y",
						"CHECK_PERMISSIONS" => "Y",
						"IBLOCK_TYPE" => $arParams["LINK_IBLOCK_TYPE"],
						"PROPERTY_".$arParams["LINK_PROPERTY_SID"] => $arResult["ID"],
					),
					false,
					false,
					Array("ID","IBLOCK_ID","NAME","DETAIL_PAGE_URL","IBLOCK_NAME")
				);
				while($ar = $rsLinkElements->GetNext())
					$arResult["LINKED_ELEMENTS"][]=$ar;
			}

			if(!$arSection && $arResult["IBLOCK_SECTION_ID"] > 0)
			{
				$arSectionFilter = array(
					"ID" => $arResult["IBLOCK_SECTION_ID"],
					"IBLOCK_ID" => $arResult["IBLOCK_ID"],
					"ACTIVE" => "Y",
				);
				$rsSection = CIBlockSection::GetList(Array(),$arSectionFilter);
				$rsSection->SetUrlTemplates("", $arParams["SECTION_URL"]);
				$arSection = $rsSection->GetNext();
			}

			if($arSection)
			{
				$arSection["PATH"] = array();
				$rsPath = GetIBlockSectionPath($arResult["IBLOCK_ID"], $arSection["ID"]);
				$rsPath->SetUrlTemplates("", $arParams["SECTION_URL"]);
				while($arPath=$rsPath->GetNext())
				{
					$arSection["PATH"][] = $arPath;
				}
				$arResult["SECTION"] = $arSection;
			}

			if($arParams["USE_PRICE_COUNT"])
			{
				if(CModule::IncludeModule("catalog"))
				{
					$arResult["PRICE_MATRIX"] = CatalogGetPriceTableEx($arResult["ID"]);
					foreach($arResult["PRICE_MATRIX"]["COLS"] as $keyColumn=>$arColumn)
						$arResult["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"] = htmlspecialchars($arColumn["NAME_LANG"]);
				}
				else
				{
					$arResult["PRICE_MATRIX"] = false;
				}
				$arResult["PRICES"] = array();
			}
			else
			{
				$arResult["PRICE_MATRIX"] = false;
				$arResult["PRICES"] = CIBlockPriceTools::GetItemPrices($arParams["IBLOCK_ID"], $arResult["CAT_PRICES"], $arResult, $arParams['PRICE_VAT_INCLUDE']);
			}
			$arResult["CAN_BUY"] = CIBlockPriceTools::CanBuy($arParams["IBLOCK_ID"], $arResult["CAT_PRICES"], $arResult);

			$arResult["BUY_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=BUY&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arResult["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			$arResult["ADD_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=ADD2BASKET&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arResult["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			$arResult["LINK_URL"] = str_replace(
						array("#ELEMENT_ID#","#SECTION_ID#"),
						array($arResult["ID"],$arResult["SECTION"]["ID"]),
						$arParams["LINK_ELEMENTS_URL"]
					);

			$this->SetResultCacheKeys(array(
				"IBLOCK_ID",
				"ID",
				"IBLOCK_SECTION_ID",
				"NAME",
				"PROPERTIES",
				"SECTION",
			));
			$this->IncludeComponentTemplate();
		}
		else
		{
			$this->AbortResultCache();
			ShowError(GetMessage("CATALOG_ELEMENT_NOT_FOUND"));
			@define("ERROR_404", "Y");
			if($arParams["SET_STATUS_404"]==="Y")
				CHTTP::SetStatus("404 Not Found");
		}
	}
	else
	{
		$this->AbortResultCache();
		ShowError(GetMessage("CATALOG_ELEMENT_NOT_FOUND"));
		@define("ERROR_404", "Y");
		if($arParams["SET_STATUS_404"]==="Y")
			CHTTP::SetStatus("404 Not Found");
	}
}

if(isset($arResult["ID"]))
{
	if($GLOBALS["APPLICATION"]->GetShowIncludeAreas())
		$this->AddIncludeAreaIcons(CIBlock::ShowPanel($arResult["IBLOCK_ID"], $arResult["ID"], $arResult["IBLOCK_SECTION_ID"], $arParams["IBLOCK_TYPE"], true));
	if($arParams["DISPLAY_PANEL"])
		CIBlock::ShowPanel($arResult["IBLOCK_ID"], $arResult["ID"], $arResult["IBLOCK_SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());

	if($arParams["SET_TITLE"])
		$APPLICATION->SetTitle($arResult["NAME"]);

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

	if($arParams["ADD_SECTIONS_CHAIN"] && is_array($arResult["SECTION"]))
	{
		foreach($arResult["SECTION"]["PATH"] as $arPath)
		{
			$APPLICATION->AddChainItem($arPath["NAME"], $arPath["~SECTION_PAGE_URL"]);
		}
	}
	return $arResult["ID"];
}
else
{
	return 0;
}
?>
