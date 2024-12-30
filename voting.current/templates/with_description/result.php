<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->IncludeComponent(
	"bitrix:voting.result", 
	"with_description", 
	Array(
		"VOTE_ID" => $arResult["VOTE_ID"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"ADDITIONAL_CACHE_ID" => $arResult["ADDITIONAL_CACHE_ID"],
	),
	$component
);
?>