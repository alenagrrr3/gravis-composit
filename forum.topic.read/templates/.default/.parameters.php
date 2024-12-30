<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arTemplateParameters = array(
	"SEND_MAIL" => CForumParameters::GetSendMessageRights(GetMessage("F_SEND_MAIL"), "BASE", "E"),
/*	"SEND_ICQ" => CForumParameters::GetSendMessageRights(GetMessage("F_SEND_ICQ"), "BASE", "A", "ICQ"),
	"HIDE_USER_ACTION" => array(
        "NAME" => GetMessage("F_HIDE_USER_ACTION"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"),
*/
    "SHOW_RSS" => array(
        "NAME" => GetMessage("F_SHOW_RSS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),

);
?>