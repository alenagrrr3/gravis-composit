<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = Array(
	"Y" => GetMessage("SUP_DESC_YES"),
	"N" => GetMessage("SUP_DESC_NO"),
);


$arComponentParameters = array(
	"PARAMETERS" => array(

		"VARIABLE_ALIASES" => Array(
			"ID" => Array("NAME" => GetMessage("SUP_TICKET_ID_DESC"))
		),

		"SEF_MODE" => Array(
			"ticket_list" => Array(
				"NAME" => GetMessage("SUP_TICKET_LIST_DESC"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array()
			),

			"ticket_edit" => Array(
				"NAME" => GetMessage("SUP_TICKET_EDIT_DESC"),
				"DEFAULT" => "#ID#.php",
				"VARIABLES" => array("ID")
			),
		),

		"TICKETS_PER_PAGE" => Array(
			"NAME" => GetMessage("SUP_LIST_TICKETS_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "50"
		),

		"MESSAGES_PER_PAGE" => Array(
			"NAME" => GetMessage("SUP_EDIT_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "20"
		),


		"SET_PAGE_TITLE" => Array(
			"NAME"=>GetMessage("SUP_SET_PAGE_TITLE"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"N", 
			"DEFAULT"=>"Y", 
			"PARENT" => "ADDITIONAL_SETTINGS",
			"VALUES"=>$arYesNo, 
			"ADDITIONAL_VALUES"=>"N"
		),
		
		"SHOW_COUPON_FIELD" => Array(
			"NAME" => GetMessage("SUP_SHOW_COUPON_FIELD"),
			"TYPE" => "CHECKBOX",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "N",
		),

	)
);
?>
