<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("learning"))
{
	ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
	return;
}

//Params
$arParams["SORT_BY"] = (isset($arParams["SORT_BY"]) ? trim($arParams["SORT_BY"]) : "SORT");
$arParams["SORT_ORDER"] = (isset($arParams["SORT_ORDER"]) ? trim($arParams["SORT_ORDER"]) : "ASC");
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");
$arParams["COURSE_DETAIL_TEMPLATE"] = (isset($arParams["COURSE_DETAIL_TEMPLATE"]) ? htmlspecialchars($arParams["COURSE_DETAIL_TEMPLATE"]) : "course/index.php?COURSE_ID=#COURSE_ID#");
$arParams["COURSES_PER_PAGE"] = (intval($arParams["COURSES_PER_PAGE"]) > 0 ? intval($arParams["COURSES_PER_PAGE"]) : 20);

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("LEARNING_COURSE_LIST"));

//arResult
$arResult = Array(
	"COURSES" => Array(),
	"NAV_SRTING" => "",
	"NAV_RESULT" => null,
);

$res = CCourse::GetList(
	Array($arParams["SORT_BY"] => $arParams["SORT_ORDER"]), 
	Array(
		"ACTIVE" => "Y",
		"ACTIVE_DATE" => "Y",
		"SITE_ID" => LANG,
		"CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"]
	)
);

CPageOption::SetOptionString("main", "nav_page_in_session", "N");
$res->NavStart($arParams["COURSES_PER_PAGE"]);
$arResult["NAV_STRING"] = $res->GetPageNavString(GetMessage("LEARNING_COURSES_NAV"));
$arResult["NAV_RESULT"] = $res;

while ($arCourse = $res->GetNext())
{
	$arCourse["COURSE_DETAIL_URL"] = CComponentEngine::MakePathFromTemplate(
		$arParams["COURSE_DETAIL_TEMPLATE"],
		Array("COURSE_ID" => $arCourse["ID"])
	);

	$arCourse["PREVIEW_PICTURE_ARRAY"] = CFile::GetFileArray($arCourse["PREVIEW_PICTURE"]);
	$arResult["COURSES"][] = $arCourse;
}

$res->arResult = Array();
unset($arCourse);


$this->IncludeComponentTemplate();
?>