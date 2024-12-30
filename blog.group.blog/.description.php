<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BGB_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("BGB_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"SORT" => 220,
	"PATH" => array(
		"ID" => "communication",
		"CHILD" => array(
			"ID" => "blog",
			"NAME" => GetMessage("BGB_NAME")
		)
	),
);
?>