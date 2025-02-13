<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//Params
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));

if($this->StartResultCache(false, $USER->GetGroups()))
{
	//Module
	if (!CModule::IncludeModule("learning"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
		return;
	}

	//Course
	$rsCourse = CCourse::GetList(
		Array(),
		Array(
			"ID" => $arParams["COURSE_ID"],
			"ACTIVE" => "Y", 
			"ACTIVE_DATE" => "Y", 
			"SITE_ID" => LANG, 
			"CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"]
		)
	);

	if (!$arCourse = $rsCourse->GetNext())
	{
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_COURSE_DENIED"));
		return;
	}

	//Images
	$arCourse["PREVIEW_PICTURE_ARRAY"] = CFile::GetFileArray($arCourse["PREVIEW_PICTURE"]);

	$arResult = Array(
		"COURSE" => $arCourse
	);

	unset($rsCourse);
	unset($arCourse);

	$this->IncludeComponentTemplate();
}

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["COURSE"]["NAME"]);
?>