<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("learning"))
{
	ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
	return;
}

$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));
$arParams["TEST_DETAIL_TEMPLATE"] = (strlen($arParams["TEST_DETAIL_TEMPLATE"]) > 0 ? htmlspecialchars($arParams["TEST_DETAIL_TEMPLATE"]) : 'test.php?TEST_ID=#TEST#');
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");
$arParams["TESTS_PER_PAGE"] = (intval($arParams["TESTS_PER_PAGE"]) > 0 ? intval($arParams["TESTS_PER_PAGE"]) : 20);

//Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("LEARNING_TESTS_LIST"));

//arResult
$arResult = Array(
	"TESTS" => Array(),
	"TESTS_COUNT" => 0,
	"ERROR_MESSAGE" => "",
	"NAV_SRTING" => "",
	"NAV_RESULT" => null,
);

$rsTest = CTest::GetList(
	Array("SORT" => "ASC"),
	Array(
		"COURSE_ID"=>$arParams["COURSE_ID"],
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"]
	)
);

CPageOption::SetOptionString("main", "nav_page_in_session", "N");
$rsTest->NavStart($arParams["TESTS_PER_PAGE"]);
$arResult["NAV_STRING"] = $rsTest->GetPageNavString(GetMessage("LEARNING_TESTS_NAV"));
$arResult["NAV_RESULT"] = $rsTest;

while($arTest = $rsTest->GetNext())
{
	//Test URL
	$arTest["TEST_DETAIL_URL"] = CComponentEngine::MakePathFromTemplate(
		$arParams["TEST_DETAIL_TEMPLATE"],
		Array(
			"TEST_ID" => $arTest["ID"],
			"COURSE_ID" => $arTest["COURSE_ID"],
		)
	);

	if ($_SERVER['REDIRECT_STATUS'] == '404' || isset($_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"]))
		$arTest["TEST_DETAIL_URL"] = "/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL=".urlencode($arTest["TEST_DETAIL_URL"]);

	//Unfinished attempt exists?
	$arTest["ATTEMPT"] = false;

	if ($USER->IsAuthorized())
	{
		$rsAttempt = CTestAttempt::GetList(
			Array(), 
			Array(
				"TEST_ID"=>$arTest["ID"],
				"STATUS"=>"B", 
				"STUDENT_ID"=> intval($USER->GetID()),
			)
		);

		$arTest["ATTEMPT"] = $rsAttempt->GetNext();
	}

	$arResult["TESTS"][] = $arTest;
}

$arResult["TESTS_COUNT"] = count($arResult["TESTS"]);
if ($arResult["TESTS_COUNT"] <= 0)
	$arResult["ERROR_MESSAGE"] = GetMessage("LEARNING_BAD_TEST_LIST");

unset($rsTest);
unset($arTest);
unset($rsAttempt);


$this->IncludeComponentTemplate();
?>