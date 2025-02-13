<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//Params
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );

$arParams["PAGE_WINDOW"] = (isset($arParams["PAGE_WINDOW"]) && intval($arParams["PAGE_WINDOW"]) > 0 ? intval($arParams["PAGE_WINDOW"]) : "10");
$arParams["SHOW_TIME_LIMIT"] = (isset($arParams["SHOW_TIME_LIMIT"]) && $arParams["SHOW_TIME_LIMIT"] == "N" ? "N" : "Y");
$arParams["TESTS_PER_PAGE"] = (intval($arParams["TESTS_PER_PAGE"]) > 0 ? intval($arParams["TESTS_PER_PAGE"]) : 20);

if (strlen($arParams["PAGE_NUMBER_VARIABLE"]) <=0 || !preg_match("#^[A-Za-z_][A-Za-z01-9_]*$#", $arParams["PAGE_NUMBER_VARIABLE"]))
	$arParams["PAGE_NUMBER_VARIABLE"] = "PAGE";

$arComponentVariables = Array(
	"COURSE_ID",
	"INDEX",
	"LESSON_ID",
	"CHAPTER_ID",
	"SELF_TEST_ID",
	"TEST_ID",
	"TYPE",
	"TEST_LIST",
	"GRADEBOOK",
	"FOR_TEST_ID",
	$arParams["PAGE_NUMBER_VARIABLE"],
);

if ($arParams["SEF_MODE"] == "Y")
{
	$arDefaultUrlTemplates404 = array(
		"course.detail" => "course#COURSE_ID#/index",
		"lesson.detail" => "course#COURSE_ID#/lesson#LESSON_ID#/",
		"chapter.detail" => "course#COURSE_ID#/chapter#CHAPTER_ID#/",
		"test.self" => "course#COURSE_ID#/selftest#SELF_TEST_ID#/",
		"test" => "course#COURSE_ID#/test#TEST_ID#/",
		"test.list" => "course#COURSE_ID#/examination/",
		"course.contents" => "course#COURSE_ID#/contents/",
		"gradebook" => "course#COURSE_ID#/gradebook/",
	);

	$arDefaultVariableAliases404 = Array(
		"course.detail" => Array("COURSE_ID" => "COURSE_ID"),
		"lesson.detail" => Array("LESSON_ID" => "LESSON_ID","COURSE_ID" => "COURSE_ID"),
		"chapter.detail" => Array("CHAPTER_ID" => "CHAPTER_ID", "COURSE_ID" => "COURSE_ID"),
		"test.self" => Array("SELF_TEST_ID" => "SELF_TEST_ID", "COURSE_ID" => "COURSE_ID"),
		"test" => Array("TEST_ID" => "TEST_ID", "COURSE_ID" => "COURSE_ID"),
		"test.list" => Array("COURSE_ID" => "COURSE_ID"),
		"course.contents" => Array("COURSE_ID" => "COURSE_ID"),
		"gradebook" => Array("FOR_TEST_ID" => "FOR_TEST_ID", "COURSE_ID" => "COURSE_ID"),
	);

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	if (isset($arVariables["COURSE_ID"]) && $arParams["COURSE_ID"] <= 0)
		$arParams["COURSE_ID"] = $arVariables["COURSE_ID"];


	if (!$componentPage)
		$componentPage = "course.detail";

	//echo $componentPage;
	//print_r($arVariables);

	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	//print_r($arVariables);
	//print_r($arVariableAliases);
	//print_r($arUrlTemplates);

	$arResult = Array(
		"FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates, 
		"VARIABLES" => $arVariables, 
		"ALIASES" => $arVariableAliases
	);
}
else
{
	$arDefaultVariableAliases = Array(
		"COURSE_ID" => "COURSE_ID",
		"INDEX" => "INDEX",
		"LESSON_ID" => "LESSON_ID",
		"CHAPTER_ID" => "CHAPTER_ID",
		"SELF_TEST_ID" => "SELF_TEST_ID",
		"TEST_ID" => "TEST_ID",
		"TYPE" => "TYPE",
		"TEST_LIST" => "TEST_LIST",
		"GRADEBOOK" => "GRADEBOOK",
		"FOR_TEST_ID" => "FOR_TEST_ID",
		$arParams["PAGE_NUMBER_VARIABLE"] => $arParams["PAGE_NUMBER_VARIABLE"],
	);

	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	if(isset($arVariables["LESSON_ID"]) && intval($arVariables["LESSON_ID"]) > 0)
		$componentPage = "lesson.detail";
	elseif(isset($arVariables["CHAPTER_ID"]) && intval($arVariables["CHAPTER_ID"]) > 0)
		$componentPage = "chapter.detail";
	elseif(isset($arVariables["TEST_ID"]) && intval($arVariables["TEST_ID"]) > 0)
		$componentPage = "test";
	elseif(isset($arVariables["SELF_TEST_ID"]) && intval($arVariables["SELF_TEST_ID"]) > 0)
		$componentPage = "test.self";
	elseif(isset($arVariables["TYPE"]) && $arVariables["TYPE"] == "Y")
		$componentPage = "course.contents";
	elseif(isset($arVariables["TEST_LIST"]) && $arVariables["TEST_LIST"] == "Y")
		$componentPage = "test.list";
	elseif(isset($arVariables["GRADEBOOK"]) && $arVariables["GRADEBOOK"] == "Y")
		$componentPage = "gradebook";
	else
		$componentPage = "course.detail";

	$currentPage = GetPagePath(false, false);
	$queryString= htmlspecialchars(DeleteParam(array_values($arVariableAliases)));
	$currentPage = (
		$queryString == "" ? 
		$currentPage."?" :
		$currentPage."?".$queryString."&"
	);

	$arResult = array(
		"FOLDER" => "",
		"URL_TEMPLATES" => Array(
			"course.detail" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["INDEX"]."=Y",
			"course.contents" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["TYPE"]."=Y",
			"lesson.detail" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["LESSON_ID"]."=#LESSON_ID#",
			"chapter.detail" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["CHAPTER_ID"]."=#CHAPTER_ID#",
			"test" => $currentPage.$arVariableAliases["COURSE_ID"]."=#COURSE_ID#&".$arVariableAliases["TEST_ID"]."=#TEST_ID#",
			"test.list" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["TEST_LIST"]."=Y",
			"test.self" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["SELF_TEST_ID"]."=#LESSON_ID#",
			"gradebook" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["GRADEBOOK"]."=Y",
		),
		"VARIABLES" => $arVariables, 
		"ALIASES" => $arVariableAliases
	);

}


//Page properties
$APPLICATION->SetPageProperty("learning_course_contents_url", str_replace("#COURSE_ID#", $arParams["COURSE_ID"], $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["course.contents"]));
$APPLICATION->SetPageProperty("learning_test_list_url", str_replace("#COURSE_ID#", $arParams["COURSE_ID"], $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test.list"]));
$APPLICATION->SetPageProperty("learning_gradebook_url", str_replace("#COURSE_ID#", $arParams["COURSE_ID"], $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["gradebook"]));

$this->IncludeComponentTemplate($componentPage);
?>