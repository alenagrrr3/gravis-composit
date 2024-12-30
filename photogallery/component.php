<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!IsModuleInstalled("photogallery"))
{
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
}

$arParams["FILTER_NAME"] = "";
$arDefaultUrlTemplates404 = array(
	"sections_top" => "",
	"section" => "#SECTION_ID#/",
	"section_edit" => "#SECTION_ID#/action/#ACTION#/",
	"section_edit_icon" => "#SECTION_ID#/icon/action/#ACTION#/",
	"upload" => "#SECTION_ID#/action/upload/",
	"detail" => "#SECTION_ID#/#ELEMENT_ID#/",
	"detail_slide_show" => "#SECTION_ID#/#ELEMENT_ID#/slide_show/",
	"detail_list" => "#SECTION_ID#/#ELEMENT_ID#/list/",
	"detail_edit" => "#SECTION_ID#/#ELEMENT_ID#/action/#ACTION#/",
	"search" => "search/"
);

$arDefaultVariableAliases404 = Array(
	"sections_top"=>array(),
	"section_edit"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "ACTION" => "ACTION", "PAGE_NAME" => "PAGE_NAME"),
	"section_edit_icon"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "ACTION" => "ACTION", "PAGE_NAME" => "PAGE_NAME"),
	"section"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "PAGE_NAME" => "PAGE_NAME"),
	"upload"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "PAGE_NAME" => "PAGE_NAME"),
	"detail"=>array("ELEMENT_ID"=>"ELEMENT_ID","ELEMENT_CODE"=>"ELEMENT_CODE", "PAGE_NAME" => "PAGE_NAME"),
	"detail_edit"=>array("ELEMENT_ID"=>"ELEMENT_ID","ELEMENT_CODE"=>"ELEMENT_CODE", "ACTION" => "ACTION", "PAGE_NAME" => "PAGE_NAME"),
	"detail_slide_show"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "ELEMENT_ID"=>"ELEMENT_ID","ELEMENT_CODE"=>"ELEMENT_CODE", "PAGE_NAME" => "PAGE_NAME"),
	"detail_list"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "ELEMENT_ID"=>"ELEMENT_ID","ELEMENT_CODE"=>"ELEMENT_CODE", "PAGE_NAME" => "PAGE_NAME"),
);

$arComponentVariables = Array(
	"SECTION_ID","SECTION_CODE",
	"ELEMENT_ID","ELEMENT_CODE",
	"ACTION", "PAGE_NAME"
);

$arDefaultVariableAliases = Array(
	"SECTION_ID" => "SECTION_ID",
	"ELEMENT_ID" => "ELEMENT_ID",
	"ACTION" => "ACTION", "PAGE_NAME" => "PAGE_NAME"
);

if($arParams["SEF_MODE"] == "Y")
{
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);
	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);
	
	if(!$componentPage)
		$componentPage = "sections_top";
	elseif ($arVariables["ACTION"] == "upload")
		$componentPage = "upload";

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	$arResult = array(
			"~URL_TEMPLATES" => $arUrlTemplates,
			"VARIABLES" => $arVariables,
			"ALIASES" => $arVariableAliases,
	);
	
	foreach ($arDefaultUrlTemplates404 as $url => $value)
	{
		if (empty($arUrlTemplates[$url]))
			$arResult["URL_TEMPLATES"][$url] = $arParams["SEF_FOLDER"].$arDefaultUrlTemplates404[$url];
		elseif (substr($arUrlTemplates[$url], 0, 1) == "/")
			$arResult["URL_TEMPLATES"][$url] = $arUrlTemplates[$url];
		else
			$arResult["URL_TEMPLATES"][$url] = $arParams["SEF_FOLDER"].$arUrlTemplates[$url];
	}
}
else
{
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";
	if (!empty($arVariables["PAGE_NAME"]))
		$componentPage = $arVariables["PAGE_NAME"];
	else 
		$componentPage = "sections_top";
}
if (!in_array($componentPage, array_keys($arDefaultUrlTemplates404)))
	$componentPage = "sections_top";

$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
$arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"] == "Y" ? "Y" : "N");

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
$arParams["WATERMARK"] = ($arParams["WATERMARK"] == "N" ? "N" : "Y");

// SECTION
$arParams["SECTION_PAGE_ELEMENTS"] = (is_set($arParams["SECTION_PAGE_ELEMENTS"]) ? intVal($arParams["SECTION_PAGE_ELEMENTS"]) : 10);
$arParams["SECTION_SORT_BY"] = (is_set($arParams["SECTION_SORT_BY"]) ? $arParams["SECTION_SORT_BY"] : "UF_DATE");
$arParams["SECTION_SORT_ORD"] = (strToUpper($arParams["SECTION_SORT_ORD"]) != "DESC" ? "ASC" : "DESC");

// ELEMENTS
$arParams["ELEMENTS_PAGE_ELEMENTS"] = (is_set($arParams["ELEMENTS_PAGE_ELEMENTS"]) ? intVal($arParams["ELEMENTS_PAGE_ELEMENTS"]) : 100);
$arParams["ELEMENT_SORT_FIELD"] = (is_set($arParams["ELEMENT_SORT_FIELD"]) ? $arParams["ELEMENT_SORT_FIELD"] : "NAME");
$arParams["ELEMENT_SORT_ORDER"] = (strToUpper($arParams["ELEMENT_SORT_ORDER"]) != "DESC" ? "ASC" : "DESC");
$arParams["ELEMENTS_USE_DESC_PAGE"] = ($arParams["ELEMENTS_USE_DESC_PAGE"] == "N" ? "N" : "Y");

// SEARCH
$arParams["FONT_MAX"] = (empty($arParams["TAGS_FONT_MAX"]) ? "35" : $arParams["TAGS_FONT_MAX"]);
$arParams["FONT_MIN"] = (empty($arParams["TAGS_FONT_MIN"]) ? "10" : $arParams["TAGS_FONT_MIN"]);
$arParams["COLOR_NEW"] = (empty($arParams["TAGS_COLOR_NEW"]) ? "3E74E6" : $arParams["TAGS_COLOR_NEW"]);
$arParams["COLOR_OLD"] = (empty($arParams["TAGS_COLOR_OLD"]) ? "C0C0C0" : $arParams["TAGS_COLOR_OLD"]);
$arParams["PAGE_RESULT_COUNT"] = (intVal($arParams["ELEMENTS_PAGE_ELEMENTS"]) > 0 ? $arParams["ELEMENTS_PAGE_ELEMENTS"] : 50);

//MAIN PAGE
$arParams["SHOW_LINK_ON_MAIN_PAGE"] = (isset($arParams["SHOW_LINK_ON_MAIN_PAGE"]) ? $arParams["SHOW_LINK_ON_MAIN_PAGE"] : array("id", "rating", "comments", "shows"));
$arParams["SHOW_ON_MAIN_PAGE"] = (in_array($arParams["SHOW_ON_MAIN_PAGE"], array("rating", "id", "comments", "shows")) ? $arParams["SHOW_ON_MAIN_PAGE"] : "none");
$arParams["SHOW_ON_MAIN_PAGE_POSITION"] = ($arParams["SHOW_ON_MAIN_PAGE_POSITION"] == "right" ? "right" : "left");
$arParams["SHOW_ON_MAIN_PAGE_TYPE"] = (in_array($arParams["SHOW_ON_MAIN_PAGE_TYPE"], array("count", "time")) ? $arParams["SHOW_ON_MAIN_PAGE_TYPE"] : "none");
$arParams["SHOW_ON_MAIN_PAGE_COUNT"] = intVal($arParams["SHOW_ON_MAIN_PAGE_COUNT"]);
$arParams["SHOW_PHOTO_ON_DETAIL_LIST"] = (in_array($arParams["SHOW_PHOTO_ON_DETAIL_LIST"], array("none", "show_period", "show_count", "show_time")) ? $arParams["SHOW_PHOTO_ON_DETAIL_LIST"] : "show_count");
$arParams["SHOW_PHOTO_ON_DETAIL_LIST_COUNT"] = intVal($arParams["SHOW_PHOTO_ON_DETAIL_LIST_COUNT"]);

// VOTE
$arParams["USE_RATING"] = ($arParams["USE_RATING"] == "Y" ? "Y" : "N");
if ($arParams["USE_RATING"] == "Y")
{
	$arParams["VOTE_NAMES"] = (isset($arParams["VOTE_NAMES"]) ? $arParams["VOTE_NAMES"] : array("1", "2", "3", "4", "5"));
	$arParams["MAX_VOTE"] = (isset($arParams["MAX_VOTE"]) ? $arParams["MAX_VOTE"] : 5);
}
else 
{
	$arParams["SHOW_RATING"] = "N";
}
// COMMENTS
$arParams["USE_COMMENTS"] = ($arParams["USE_COMMENTS"] == "Y" ? "Y" : "N");
if ($arParams["USE_COMMENTS"] == "N")
{
	$arParams["SHOW_COMMENTS"] = "N";
	$arParams["COMMENTS_TYPE"] = "none";
}
else 
{
	$arParams["COMMENTS_TYPE"] = strToLower($arParams["COMMENTS_TYPE"]);
	$arParams["COMMENTS_TYPE"] = (in_array($arParams["COMMENTS_TYPE"], array("forum", "blog")) ? $arParams["COMMENTS_TYPE"] : "blog");
	$arParams["PATH_TO_SMILE"] = (empty($arParams["PATH_TO_SMILE"]) ? 
		"/bitrix/images/".$arParams["COMMENTS_TYPE"]."/smile/" : $arParams["PATH_TO_SMILE"]);
}

if ((intVal($arVariables["ELEMENT_ID"]) > 0 || strLen($arVariables["ELEMENT_CODE"]) > 0) && 
	intVal($arResult["VARIABLES"]["SECTION_ID"]) <= 0 && $_SERVER['REQUEST_METHOD'] == "POST")
{
	CModule::IncludeModule("iblock");
	if (intVal($arVariables["ELEMENT_ID"]) > 0)
		$rsElement = CIBlockElement::GetList(array(), array("ID" => intVal($arVariables["ELEMENT_ID"])));
	else 
		$rsElement = CIBlockElement::GetList(array(), array("CODE" => intVal($arVariables["ELEMENT_CODE"])));
		
	if($arElement = $rsElement->Fetch())
	{
		$arVariables["ELEMENT_ID"] = $arElement["ID"];
		$arVariables["ELEMENT_CODE"] = $arElement["CODE"];
		$arVariables["SECTION_ID"] = $arElement["IBLOCK_SECTION_ID"];
	}
}

// TEMPLATE TABLE
$arParams["TEMPLATE_LIST"] = ($arParams["TEMPLATE_LIST"] == "table" ? "table" : "");
$arParams["CELL_COUNT"] = intVal($arParams["CELL_COUNT"]);

$arResult = array(
		"~URL_TEMPLATES" =>  $arUrlTemplates,
		"URL_TEMPLATES" => $arResult["URL_TEMPLATES"],
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases);
$this->IncludeComponentTemplate($componentPage);
?>