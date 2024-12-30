<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arGroupList = Array();
$dbGroup = CBlogGroup::GetList(Array("SITE_ID" => "ASC", "NAME" => "ASC"));
while($arGroup = $dbGroup->GetNext())
{
	$arGroupList[$arGroup["ID"]] = "(".$arGroup["SITE_ID"].") [".$arGroup["ID"]."] ".$arGroup["NAME"];
}

$arThemesMessages = array(
	"blue" => GetMessage("BLG_THEME_BLUE"), 
	"green" => GetMessage("BLG_THEME_GREEN"), 
	"red" => GetMessage("BLG_THEME_RED"), 
	"red2" => GetMessage("BLG_THEME_RED2"), 
	"orange" => GetMessage("BLG_THEME_ORANGE"), 
	);
$arThemes = array();
$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
if (is_dir($dir) && $directory = opendir($dir)):
	
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[$file] = (!empty($arThemesMessages[$file]) ? $arThemesMessages[$file] : strtoupper(substr($file, 0, 1)).strtolower(substr($file, 1)));
	}
	closedir($directory);
endif;
$arTemplateParameters = array(
	"THEME" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("BLG_THEME"),
		"TYPE" => "LIST",
		"VALUES" => $arThemes,
		"MULTIPLE" => "N",
		"DEFAULT" => "blue"),

	"GROUP_ID"=>array(
		"NAME" => GetMessage("GENERAL_PAGE_GROUP_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arGroupList,
		"MULTIPLE" => "N",
		"DEFAULT" => "",	
		"ADDITIONAL_VALUES" => "Y",
	),
	"PATH_TO_BLOG" => Array(
			"NAME" => GetMessage("GENERAL_PAGE_PATH_TO_BLOG"),
			"TYPE" => "STRING",
			"DEFAULT" => "/club/user/#user_id#/blog/",
		),		
	"PATH_TO_POST" => Array(
			"NAME" => GetMessage("GENERAL_PAGE_PATH_TO_POST"),
			"TYPE" => "STRING",
			"DEFAULT" => "/club/user/#user_id#/blog/#post_id#/",
		),		
	"PATH_TO_GROUP_BLOG" => Array(
			"NAME" => GetMessage("GENERAL_PAGE_PATH_TO_GROUP_BLOG"),
			"TYPE" => "STRING",
			"DEFAULT" => "/club/group/#group_id#/blog/",
		),		
	"PATH_TO_GROUP_BLOG_POST" => Array(
			"NAME" => GetMessage("GENERAL_PAGE_PATH_TO_GROUP_BLOG_POST"),
			"TYPE" => "STRING",
			"DEFAULT" => "/club/group/#group_id#/blog/#post_id#/",
		),		
	"PATH_TO_USER" => Array(
			"NAME" => GetMessage("GENERAL_PAGE_PATH_TO_USER"),
			"TYPE" => "STRING",
			"DEFAULT" => "/club/user/#user_id#/",
		),		
    "PERIOD_NEW_TAGS" => array(
		"NAME" => GetMessage("SEARCH_PERIOD_NEW_TAGS"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => ""
    ),
    "PERIOD" => array(
		"NAME" => GetMessage("SEARCH_PERIOD"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => ""
    ),
	"COLOR_TYPE" => array(
		"NAME" => GetMessage("SEARCH_COLOR_TYPE"),
		"TYPE" => "LIST",
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "Y",
	),
    "WIDTH" => array(
		"NAME" => GetMessage("SEARCH_WIDTH"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "100%"
    ),
);
?>