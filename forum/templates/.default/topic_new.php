<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arInfo = $APPLICATION->IncludeComponent(
		"bitrix:forum.topic.new",
		"",
		array(
			"FID" => $arResult["FID"],
			"MID" => $arResult["MID"],
			"MESSAGE_TYPE" => $arResult["MESSAGE_TYPE"],
			
			"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"],
			"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"],
			"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
			"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
			"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
			"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["URL_TEMPLATES_PROFILE_VIEW"],
			
			"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
			"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
			"PATH_TO_ICON"	=>	$arParams["PATH_TO_ICON"],
			"FILES_COUNT" => $arParams["FILES_COUNT"], 
			
			"SET_NAVIGATION" => $arResult["SET_NAVIGATION"],
			"AJAX_TYPE" => $arParams["AJAX_TYPE"],
			"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
			"SET_TITLE" => $arResult["SET_TITLE"],
			"CACHE_TIME" => $arResult["CACHE_TIME"],
			"CACHE_TYPE" => $arResult["CACHE_TYPE"],
		),
		$component 
	);
if ($arInfo !== false):
	$APPLICATION->IncludeComponent(
		"bitrix:forum.post_form", 
		"", 
		Array(
			"FID"	=>	$arResult["FID"],
			"TID"	=>	$arResult["TID"],
			"MID"	=>	$arResult["MID"],
			"PAGE_NAME"	=>	"topic_new",
			"MESSAGE_TYPE"	=>	$arInfo["MESSAGE_TYPE"],
			"FORUM" => $arInfo["FORUM"],
			"bVarsFromForm" => $arInfo["bVarsFromForm"],
			
			"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
			"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
			"URL_TEMPLATES_HELP" =>  $arResult["URL_TEMPLATES_HELP"],
			"URL_TEMPLATES_RULES" =>  $arResult["URL_TEMPLATES_RULES"],
			
			"PATH_TO_SMILE"	=>	$arParams["PATH_TO_SMILE"],
			"PATH_TO_ICON"	=>	$arParams["PATH_TO_ICON"],
			"SMILE_TABLE_COLS" => $arParams["SMILE_TABLE_COLS"],
			"FILES_COUNT" => $arParams["FILES_COUNT"], 
			
			"AJAX_TYPE" => $arParams["AJAX_TYPE"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			
			"SHOW_TAGS" => $arParams["SHOW_TAGS"],
			"ERROR_MESSAGE" => $arInfo["ERROR_MESSAGE"]
		),
		$component
	);
endif;
?>