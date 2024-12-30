<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BU_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("BU_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"SORT" => 240,
	"PATH" => array(
		"ID" => "communication",
		"CHILD" => array(
			"ID" => "blog",
			"NAME" => GetMessage("BU_NAME")
		)
	),
);
?>