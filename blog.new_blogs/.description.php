<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BMN_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("BMN_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"SORT" => 120,
	"PATH" => array(
		"ID" => "communication",
		"CHILD" => array(
			"ID" => "blog",
			"NAME" => GetMessage("BMN_NAME")
		)
	),
);
?>