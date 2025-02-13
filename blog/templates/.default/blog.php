<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="body-blog">
<?
$APPLICATION->IncludeComponent(
	"bitrix:blog.menu",
	"",
	Array(
			"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
			"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
			"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
			"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
			"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
			"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
			"PATH_TO_BLOG_EDIT"		=> $arResult["PATH_TO_BLOG_EDIT"],
			"PATH_TO_BLOG_INDEX"	=> $arResult["PATH_TO_BLOG_INDEX"],
			"PATH_TO_DRAFT"			=> $arResult["PATH_TO_DRAFT"],
			"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
			"PATH_TO_USER_FRIENDS"	=> $arResult["PATH_TO_USER_FRIENDS"],
			"PATH_TO_USER_SETTINGS"	=> $arResult["PATH_TO_USER_SETTINGS"],
			"PATH_TO_GROUP_EDIT"	=> $arResult["PATH_TO_GROUP_EDIT"],
			"PATH_TO_CATEGORY_EDIT"	=> $arResult["PATH_TO_CATEGORY_EDIT"],
			"PATH_TO_RSS_ALL"		=> $arResult["PATH_TO_RSS_ALL"],
			"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
			"SET_NAV_CHAIN"			=> $arResult["SET_NAV_CHAIN"],
		),
	$component
);
?>
		
	<div class="blog-sidebar">
		<?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.info",
				"avatar",
				Array(
						"BLOG_VAR"		=> $arResult["ALIASES"]["blog"],
						"USER_VAR"		=> $arResult["ALIASES"]["user_id"],
						"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
						"PATH_TO_BLOG"	=> $arResult["PATH_TO_BLOG"],
						"PATH_TO_POST"	=> $arResult["PATH_TO_POST"],
						"PATH_TO_USER"	=> $arResult["PATH_TO_USER"],
						"PATH_TO_BLOG_CATEGORY"	=> $arResult["PATH_TO_BLOG_CATEGORY"],
						"BLOG_URL"		=> $arResult["VARIABLES"]["blog"],
						"CATEGORY_ID"	=> $arResult["VARIABLES"]["category"],
						"CACHE_TYPE"	=> $arResult["CACHE_TYPE"],
						"CACHE_TIME"	=> $arResult["CACHE_TIME"],
						"BLOG_PROPERTY_LIST" =>  $arParams["BLOG_PROPERTY_LIST"],
					),
				$component 
			);
		?>
		<?
		$APPLICATION->IncludeComponent(
			"bitrix:blog.menu",
			"settings",
			Array(
					"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
					"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
					"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
					"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
					"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
					"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
					"PATH_TO_BLOG_EDIT"		=> $arResult["PATH_TO_BLOG_EDIT"],
					"PATH_TO_BLOG_INDEX"	=> $arResult["PATH_TO_BLOG_INDEX"],
					"PATH_TO_DRAFT"			=> $arResult["PATH_TO_DRAFT"],
					"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
					"PATH_TO_USER_FRIENDS"	=> $arResult["PATH_TO_USER_FRIENDS"],
					"PATH_TO_USER_SETTINGS"	=> $arResult["PATH_TO_USER_SETTINGS"],
					"PATH_TO_GROUP_EDIT"	=> $arResult["PATH_TO_GROUP_EDIT"],
					"PATH_TO_CATEGORY_EDIT"	=> $arResult["PATH_TO_CATEGORY_EDIT"],
					"PATH_TO_RSS_ALL"		=> $arResult["PATH_TO_RSS_ALL"],
					"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
					"SET_NAV_CHAIN"			=> $arResult["SET_NAV_CHAIN"],
				),
			$component
		);
		?>
	
		<?
		if(IsModuleInstalled("search"))
		{
			$arBlog = CBlog::GetByUrl($arResult["VARIABLES"]["blog"]);
			if(!empty($arBlog))
			{
				?>
			<ul>
				<li class="blog-tags-cloud">
					<h3 class="blog-sidebar-title"><?=GetMessage("BC_SEARCH_TAG")?></h3>
					<div align="center">
					<?
					$APPLICATION->IncludeComponent(
						"bitrix:search.tags.cloud",
						"",
						Array(
							"FONT_MAX" => 18, 
							"FONT_MIN" => 10,
							"COLOR_NEW" => $arParams["COLOR_NEW"],
							"COLOR_OLD" => $arParams["COLOR_OLD"],
							"ANGULARITY" => $arParams["ANGULARITY"], 
							"PERIOD_NEW_TAGS" => $arParams["PERIOD_NEW_TAGS"], 
							"SHOW_CHAIN" => "N", 
							"COLOR_TYPE" => $arParams["COLOR_TYPE"], 
							"WIDTH" => $arParams["WIDTH"], 
							"SEARCH" => "", 
							"TAGS" => "", 
							"SORT" => "NAME", 
							"PAGE_ELEMENTS" => "30", 
							"PERIOD" => $arParams["PERIOD"], 
							"URL_SEARCH" => $arResult["PATH_TO_SEARCH"], 
							"TAGS_INHERIT" => "N", 
							"CHECK_DATES" => "Y", 
							"arrFILTER" => Array("blog"), 
							"arrFILTER_blog" => Array($arBlog["ID"]),
							"CACHE_TYPE" => "A", 
							"CACHE_TIME" => "3600" 
						)
					);
					?>
					</div>
				</li>
			</ul>
				<?
			}
		}
		?>
		
		<?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.blog.favorite", 
				"", 
				Array(
						"MESSAGE_COUNT"			=> $arResult["MESSAGE_COUNT"],
						"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
						"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
						"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
						"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
						"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
						"PATH_TO_BLOG_CATEGORY"	=> $arResult["PATH_TO_BLOG_CATEGORY"],
						"PATH_TO_POST"			=> $arResult["PATH_TO_POST"],
						"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
						"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
						"PATH_TO_SMILE"			=> $arResult["PATH_TO_SMILE"],
						"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
						"YEAR"					=> $arResult["VARIABLES"]["year"],
						"MONTH"					=> $arResult["VARIABLES"]["month"],
						"DAY"					=> $arResult["VARIABLES"]["day"],
						"CATEGORY_ID"			=> $arResult["VARIABLES"]["category"],
						"CACHE_TYPE"			=> $arResult["CACHE_TYPE"],
						"CACHE_TIME"			=> $arResult["CACHE_TIME"],
						"CACHE_TIME_LONG"		=> $arResult["CACHE_TIME_LONG"],
						"SET_NAV_CHAIN"			=> $arResult["SET_NAV_CHAIN"],
						"SET_TITLE"				=> $arResult["SET_TITLE"],
						"POST_PROPERTY_LIST"	=> $arParams["POST_PROPERTY_LIST"],
						"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
						"NAV_TEMPLATE"	=> $arParams["NAV_TEMPLATE"],
					),
				$component 
			);
		?>
		<?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.rss.link",
				"",
				Array(
						"RSS1"				=> "N",
						"RSS2"				=> "Y",
						"ATOM"				=> "N",
						"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
						"POST_VAR"			=> $arResult["ALIASES"]["post_id"],
						"GROUP_VAR"			=> $arResult["ALIASES"]["group_id"],
						"PATH_TO_RSS"		=> $arResult["PATH_TO_RSS"],
						"PATH_TO_RSS_ALL"	=> $arResult["PATH_TO_RSS_ALL"],
						"BLOG_URL"			=> $arResult["VARIABLES"]["blog"],
						"GROUP_ID"			=> $arResult["VARIABLES"]["group_id"],
						"MODE"				=> "B",
					),
				$component 
			);
		?>
		<?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.calendar",
				"",
				Array(
						"BLOG_VAR"		=> $arResult["ALIASES"]["blog"],
						"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
						"PATH_TO_BLOG"	=> $arResult["PATH_TO_BLOG"],
						"BLOG_URL"		=> $arResult["VARIABLES"]["blog"],
						"YEAR"			=> $arResult["VARIABLES"]["year"],
						"MONTH"			=> $arResult["VARIABLES"]["month"],
						"DAY"			=> $arResult["VARIABLES"]["day"],
						"CACHE_TYPE"	=> $arResult["CACHE_TYPE"],
						"CACHE_TIME"	=> $arResult["CACHE_TIME"],
					),
				$component 
			);
		?>
		<?
		if(IsModuleInstalled("search"))
		{
			$APPLICATION->IncludeComponent(
					"bitrix:blog.search", 
					"form", 
					Array(
							"PAGE_RESULT_COUNT"	=> 0,
							"SEARCH_PAGE"		=> $arResult["PATH_TO_SEARCH"],
							"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
							"POST_VAR"			=> $arResult["ALIASES"]["post_id"],
							"USER_VAR"			=> $arResult["ALIASES"]["user_id"],
							"PAGE_VAR"			=> $arResult["ALIASES"]["page"],
							"PATH_TO_BLOG"		=> $arResult["PATH_TO_BLOG"],
							"PATH_TO_POST"		=> $arResult["PATH_TO_POST"],
							"PATH_TO_USER"		=> $arResult["PATH_TO_USER"],
							"SET_TITLE"			=> "N",
						),
					$component 
				);

		}
		?>
		<?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.info",
				"",
				Array(
						"BLOG_VAR"		=> $arResult["ALIASES"]["blog"],
						"USER_VAR"		=> $arResult["ALIASES"]["user_id"],
						"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
						"PATH_TO_BLOG"	=> $arResult["PATH_TO_BLOG"],
						"PATH_TO_POST"	=> $arResult["PATH_TO_POST"],
						"PATH_TO_USER"	=> $arResult["PATH_TO_USER"],
						"PATH_TO_BLOG_CATEGORY"	=> $arResult["PATH_TO_BLOG_CATEGORY"],
						"BLOG_URL"		=> $arResult["VARIABLES"]["blog"],
						"CATEGORY_ID"	=> $arResult["VARIABLES"]["category"],
						"CACHE_TYPE"	=> $arResult["CACHE_TYPE"],
						"CACHE_TIME"	=> $arResult["CACHE_TIME"],
						"BLOG_PROPERTY_LIST" =>  $arParams["BLOG_PROPERTY_LIST"],
					),
				$component 
			);
		?>
	</div>
	<div class="blog-posts">
		<?
		$APPLICATION->IncludeComponent(
			"bitrix:blog.blog", 
			"", 
			Array(
					"MESSAGE_COUNT"			=> $arResult["MESSAGE_COUNT"],
					"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
					"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
					"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
					"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
					"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
					"PATH_TO_BLOG_CATEGORY"	=> $arResult["PATH_TO_BLOG_CATEGORY"],
					"PATH_TO_POST"			=> $arResult["PATH_TO_POST"],
					"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
					"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
					"PATH_TO_SMILE"			=> $arResult["PATH_TO_SMILE"],
					"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
					"YEAR"					=> $arResult["VARIABLES"]["year"],
					"MONTH"					=> $arResult["VARIABLES"]["month"],
					"DAY"					=> $arResult["VARIABLES"]["day"],
					"CATEGORY_ID"			=> $arResult["VARIABLES"]["category"],
					"CACHE_TYPE"			=> $arResult["CACHE_TYPE"],
					"CACHE_TIME"			=> $arResult["CACHE_TIME"],
					"CACHE_TIME_LONG"		=> $arResult["CACHE_TIME_LONG"],
					"SET_NAV_CHAIN"			=> $arResult["SET_NAV_CHAIN"],
					"SET_TITLE"				=> $arResult["SET_TITLE"],
					"POST_PROPERTY_LIST"	=> $arParams["POST_PROPERTY_LIST"],
					"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
					"NAV_TEMPLATE"	=> $arParams["NAV_TEMPLATE"],
				),
			$component 
		);
		?>
		</div>	
<div class="blog-clear-float"></div>
</div>