<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arInfo = $APPLICATION->IncludeComponent(
	"bitrix:forum.topic.read",
	"",
	array(
		"FID" => $arResult["FID"],
		"TID" => $arResult["TID"],
		"MID" => $arResult["MID"],
		"MESSAGES_PER_PAGE" => $arResult["MESSAGES_PER_PAGE"],
		
		"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"],
		"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"],
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"URL_TEMPLATES_MESSAGE_MOVE" => $arResult["URL_TEMPLATES_MESSAGE_MOVE"],
		"URL_TEMPLATES_TOPIC_NEW" => $arResult["URL_TEMPLATES_TOPIC_NEW"],
		"URL_TEMPLATES_SUBSCR_LIST" => $arResult["URL_TEMPLATES_SUBSCR_LIST"],
		"URL_TEMPLATES_TOPIC_MOVE" => $arResult["URL_TEMPLATES_TOPIC_MOVE"],
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"],
		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"],
		"URL_TEMPLATES_RSS" => $arResult["URL_TEMPLATES_RSS"],
		"URL_TEMPLATES_USER_POST" =>  $arResult["URL_TEMPLATES_USER_POST"],
		
		"PAGEN" => intVal($GLOBALS["NavNum"] + 1),
		"PATH_TO_SMILE" =>  $arParams["PATH_TO_SMILE"],
		"PATH_TO_ICON" => $arParams["PATH_TO_ICON"],
		"WORD_LENGTH" => $arParams["WORD_LENGTH"],
		"DATE_FORMAT" =>  $arResult["DATE_FORMAT"],
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"],
		"FILES_COUNT" => $arParams["FILES_COUNT"], 
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"], 			
		"AJAX_TYPE" => $arParams["AJAX_TYPE"],
		
		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		
		"SEND_MAIL" => $arParams["SEND_MAIL"],
		"SEND_ICQ" => "A",
		"SHOW_RSS" => $arParams["USE_RSS"],
		"HIDE_USER_ACTION" => $arParams["HIDE_USER_ACTION"]
	),
	$component
);
?><?$APPLICATION->IncludeComponent("bitrix:forum.statistic", "", 
	Array(
		"FID"	=>	($arInfo ? $arInfo["FID"] : $arResult["FID"]),
		"TID"	=>	($arInfo ? $arInfo["TID"] : $arResult["TID"]),
		"PERIOD"	=>	$arParams["TIME_INTERVAL_FOR_USER_STAT"],
		"SHOW"	=>	array("USERS_ONLINE"),
		"URL_TEMPLATES_PROFILE_VIEW"	=>	$arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"WORD_LENGTH"	=>	$arParams["WORD_LENGTH"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TIME_USER_STAT" => $arParams["CACHE_TIME_USER_STAT"], 
	), $component
);?><?
if ($arInfo == false)
	return false;
?><?$APPLICATION->IncludeComponent("bitrix:forum.post_form", "", 
	Array(
		"FID"	=>	$arInfo["FID"],
		"TID"	=>	$arInfo["TID"],
		"MID"	=>	0,
		"PAGE_NAME"	=>	"read",
		"MESSAGE_TYPE"	=>	"REPLY",
		"FORUM" => $arInfo["FORUM"],
		"bVarsFromForm" => $arInfo["bVarsFromForm"],
		
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_HELP" =>  $arResult["URL_TEMPLATES_HELP"],
		"URL_TEMPLATES_RULES" =>  $arResult["URL_TEMPLATES_RULES"],
		
		"PATH_TO_SMILE"	=>	$arParams["PATH_TO_SMILE"],
		"PATH_TO_ICON"	=>	$arParams["PATH_TO_ICON"],
		"SMILE_TABLE_COLS" => $arParams["SMILE_TABLE_COLS"],
		"FILES_COUNT" => $arParams["FILES_COUNT"], 
		
		"AJAX_TYPE"	=>	"N",
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"]
	),
	$component
);
?>