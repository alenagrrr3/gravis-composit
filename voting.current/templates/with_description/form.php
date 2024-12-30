<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->IncludeComponent(
	"bitrix:voting.form", 
	"with_description", 
	Array(
		"VOTE_ID" => $arResult["VOTE_ID"],
		"VOTE_RESULT_TEMPLATE" => $arResult["VOTE_RESULT_TEMPLATE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"ADDITIONAL_CACHE_ID" => $arResult["ADDITIONAL_CACHE_ID"],
	),
	$component
);
?>