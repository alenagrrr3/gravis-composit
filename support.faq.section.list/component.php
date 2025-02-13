<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return false;

//prepare params
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
if($arParams['IBLOCK_ID']<=0)
	return false;
	
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
$arParams["SECTION"] = intval($arParams["SECTION"]);
$arParams["SECTION_URL"] = trim($arParams["SECTION_URL"]);
$arParams["EXPAND_LIST"] = $arParams["EXPAND_LIST"]=="Y";

if(isset($arParams["IBLOCK_TYPE"]) && $arParams["IBLOCK_TYPE"]!='')
	$arFilter['IBLOCK_TYPE'] = $arParams["IBLOCK_TYPE"];

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

//WHERE
$arFilter = Array(
	'IBLOCK_ID' => $arParams['IBLOCK_ID'],
	'GLOBAL_ACTIVE' => 'Y',
	'IBLOCK_ACTIVE' => 'Y',
	'ELEMENT_SUBSECTIONS' => 'N',
);
//join filter
if($arParams['SECTION'] > 0) //if don't join then will be selected sections from root
	$arFilter = array_merge($arFilter, array('ID' => $arParams['SECTION']));

$arAddCacheParams = array(
	"MODE" => $_REQUEST['bitrix_show_mode']?$_REQUEST['bitrix_show_mode']:'view',
	"SESS_MODE" => $_SESSION['SESS_PUBLIC_SHOW_MODE']?$_SESSION['SESS_PUBLIC_SHOW_MODE']:'view',
);

//**work body**//
if($this->StartResultCache(false, array($USER->GetGroups(), serialize($arFilter), serialize($arAddCacheParams))))
{			
		//get info for sections
		$arSec = CIBlockSection::GetList(Array('left_margin'=>'asc'), $arFilter, true);
		while($arRes = $arSec->Fetch())
		{
			//echo '<pre>'; print_r($arRes); echo '</pre>';
			$arResult['SECTIONS'][$arRes['ID']] = $arRes;

			if($arParams['SECTION']==0) //Depth from root section
				$arResult['SECTIONS'][$arRes['ID']]['REAL_DEPTH'] = --$arRes['DEPTH_LEVEL'];
			else //Depth for subsections
			{
				$arResult['SECTIONS'][$arRes['ID']]['REAL_DEPTH'] = 0;
				$tmpParentDepth = $arRes['DEPTH_LEVEL'];
			}
			
			//get info from sections and subsections
			if($arParams['EXPAND_LIST'] && $arParams['SECTION']>0)
			{
				//correct filter
				unset($arFilter['ID']);
				$arFilter["LEFT_MARGIN"] = $arRes["LEFT_MARGIN"] + 1;
				$arFilter["RIGHT_MARGIN"] = $arRes["RIGHT_MARGIN"];
					
				$arSecInc = CIBlockSection::GetList(Array('left_margin'=>'asc'), $arFilter, true);
				while($arResInc = $arSecInc->Fetch())
				{
					if($arResInc['ID'] == $arParams['SECTION'] && !isset($arResult['CURRENT_SECTION']))
						$arResult['CURRENT_SECTION'] = $arResInc;
							
					$arResult['SECTIONS'][$arResInc['ID']] = $arResInc;
					$arResult['SECTIONS'][$arResInc['ID']]['REAL_DEPTH'] = $arResInc['DEPTH_LEVEL']-$tmpParentDepth;
					
					//detail url
					$arResult['SECTIONS'][$arResInc['ID']]['SECTION_PAGE_URL'] = htmlspecialchars(str_replace(
						array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#", "#ELEMENT_ID#"),
						array(SITE_SERVER_NAME, SITE_DIR, $arParams["IBLOCK_ID"], $arResInc["ID"], ""),
						(strlen($arParams["SECTION_URL"])>0?$arParams["SECTION_URL"]:$arResInc["SECTION_PAGE_URL"])
					));

				}
			}
			//detail url
			$arResult['SECTIONS'][$arRes['ID']]['SECTION_PAGE_URL'] = htmlspecialchars(str_replace(
				array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#", "#ELEMENT_ID#"),
				array(SITE_SERVER_NAME, SITE_DIR, $arParams["IBLOCK_ID"], $arRes["ID"], ""),
				(strlen($arParams["SECTION_URL"])>0?$arParams["SECTION_URL"]:$arRes["SECTION_PAGE_URL"])
			));
		}
		
		//no sections to display
		if(count($arResult['SECTIONS'])<=0)
		{
			$this->AbortResultCache();
			@define("ERROR_404", "Y");
			return false;
		}
		
		//add buttons common
		if($USER->IsAuthorized())
		{
			if($APPLICATION->GetShowIncludeAreas())
				$this->AddIncludeAreaIcons(CIBlock::ShowPanel($arParams['IBLOCK_ID'], 0, $arParams['SECTION'], $arParams["IBLOCK_TYPE"], true));
		}

	//include template
	$this->IncludeComponentTemplate();
}
?>