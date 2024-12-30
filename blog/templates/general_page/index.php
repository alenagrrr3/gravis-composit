<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="body-blog">
<div class="blog-mainpage">
<script>
<!--
function BXBlogTabShow(id, type)
{
	if(type == 'post')
	{
		
		document.getElementById('new-posts').style.display = 'inline';
		document.getElementById('popular-posts').style.display = 'inline';
		document.getElementById('commented-posts').style.display = 'inline';
		
		document.getElementById('new-posts-title').style.display = 'none';
		document.getElementById('popular-posts-title').style.display = 'none';
		document.getElementById('commented-posts-title').style.display = 'none';
		
		document.getElementById('new-posts-content').style.display = 'none';
		document.getElementById('popular-posts-content').style.display = 'none';
		document.getElementById('commented-posts-content').style.display = 'none';

		document.getElementById(id).style.display = 'none';
		document.getElementById(id+'-title').style.display = 'inline';
		document.getElementById(id+'-content').style.display = 'block';
	}
	else if(type == 'blog')
	{
		document.getElementById('new-blogs').style.display = 'inline-block';
		document.getElementById('popular-blogs').style.display = 'inline-block';
		
		document.getElementById('new-blogs-title').style.display = 'none';
		document.getElementById('popular-blogs-title').style.display = 'none';
		
		document.getElementById('new-blogs-content').style.display = 'none';
		document.getElementById('popular-blogs-content').style.display = 'none';

		document.getElementById(id).style.display = 'none';
		document.getElementById(id+'-title').style.display = 'inline-block';
		document.getElementById(id+'-content').style.display = 'block';
	}
	
}
//-->
</script>
<div class="blog-mainpage-side-left">
<div class="blog-tab-container">
	<div class="blog-tab-left"></div>
	<div class="blog-tab-right"></div>
	<div class="blog-tab">
		<div class="blog-tab-title">
			<span id="new-posts-title"><?=GetMessage("BC_NEW_POSTS_MES")?></span>
			<span id="commented-posts-title" style="display:none;"><?=GetMessage("BC_COMMENTED_POSTS_MES")?></span>
			<span id="popular-posts-title" style="display:none;"><?=GetMessage("BC_POPULAR_POSTS_MES")?></span>
		</div>		
		<div class="blog-tab-items">
			<span id="new-posts" style="display:none;"><a href="javascript:BXBlogTabShow('new-posts', 'post');"><?=GetMessage("BC_NEW_POSTS")?></a></span>
			<span id="commented-posts"><a href="javascript:BXBlogTabShow('commented-posts', 'post');"><?=GetMessage("BC_COMMENTED_POSTS")?></a></span>
			<span id="popular-posts"><a href="javascript:BXBlogTabShow('popular-posts', 'post');"><?=GetMessage("BC_POPULAR_POSTS")?></a></span>
		</div>
	</div>	
</div>
	<div class="blog-clear-float"></div>
	<div class="blog-tab-content">
	<div id="new-posts-content" style="display:block;">
		<?
		$APPLICATION->IncludeComponent("bitrix:blog.new_posts", ".default", Array(
			"MESSAGE_COUNT"		=> $arParams["MESSAGE_COUNT_MAIN"],
			"MESSAGE_LENGTH"	=>	$arParams["MESSAGE_LENGTH"],
			"PATH_TO_BLOG"		=>	$arParams["PATH_TO_BLOG"],
			"PATH_TO_POST"		=>	$arParams["PATH_TO_POST"],
			"PATH_TO_GROUP_BLOG_POST"		=>	$arParams["PATH_TO_GROUP_BLOG_POST"],
			"PATH_TO_USER"		=>	$arParams["PATH_TO_USER"],
			"PATH_TO_SMILE"		=>	$arParams["PATH_TO_SMILE"],
			"CACHE_TYPE"		=>	$arParams["CACHE_TYPE"],
			"CACHE_TIME"		=>	$arParams["CACHE_TIME"],
			"BLOG_VAR"			=>	$arParams["VARIABLE_ALIASES"]["blog"],
			"POST_VAR"			=>	$arParams["VARIABLE_ALIASES"]["post_id"],
			"USER_VAR"			=>	$arParams["VARIABLE_ALIASES"]["user_id"],
			"PAGE_VAR"			=>	$arParams["VARIABLE_ALIASES"]["page"],
			"DATE_TIME_FORMAT"	=> $arParams["DATE_TIME_FORMAT"],
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			),
			$component 
		);
		?>
	</div>
	<div id="commented-posts-content" style="display:none;">
		<?
		$APPLICATION->IncludeComponent("bitrix:blog.commented_posts", ".default", Array(
			"MESSAGE_COUNT"		=> $arParams["MESSAGE_COUNT_MAIN"],
			"MESSAGE_LENGTH"	=>	$arParams["MESSAGE_LENGTH"],
			"PERIOD_DAYS"		=>	$arParams["PERIOD_DAYS"],
			"PATH_TO_BLOG"		=>	$arParams["PATH_TO_BLOG"],
			"PATH_TO_POST"		=>	$arParams["PATH_TO_POST"],
			"PATH_TO_USER"		=>	$arParams["PATH_TO_USER"],
			"PATH_TO_GROUP_BLOG_POST"		=>	$arParams["PATH_TO_GROUP_BLOG_POST"],
			"PATH_TO_SMILE"		=>	$arParams["PATH_TO_SMILE"],
			"CACHE_TYPE"		=>	$arParams["CACHE_TYPE"],
			"CACHE_TIME"		=>	$arParams["CACHE_TIME"],
			"BLOG_VAR"			=>	$arParams["VARIABLE_ALIASES"]["blog"],
			"POST_VAR"			=>	$arParams["VARIABLE_ALIASES"]["post_id"],
			"USER_VAR"			=>	$arParams["VARIABLE_ALIASES"]["user_id"],
			"PAGE_VAR"			=>	$arParams["VARIABLE_ALIASES"]["page"],
			"DATE_TIME_FORMAT"	=> $arParams["DATE_TIME_FORMAT"],
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			),
			$component 
		);
		?>
	</div>
	<div id="popular-posts-content" style="display:none;">
		<?
		$APPLICATION->IncludeComponent("bitrix:blog.popular_posts", ".default", Array(
			"MESSAGE_COUNT"		=> $arParams["MESSAGE_COUNT_MAIN"],
			"MESSAGE_LENGTH"	=>	$arParams["MESSAGE_LENGTH"],
			"PERIOD_DAYS"		=>	$arParams["PERIOD_DAYS"],
			"PATH_TO_BLOG"		=>	$arParams["PATH_TO_BLOG"],
			"PATH_TO_POST"		=>	$arParams["PATH_TO_POST"],
			"PATH_TO_USER"		=>	$arParams["PATH_TO_USER"],
			"PATH_TO_GROUP_BLOG_POST"		=>	$arParams["PATH_TO_GROUP_BLOG_POST"],
			"PATH_TO_SMILE"		=>	$arParams["PATH_TO_SMILE"],
			"CACHE_TYPE"		=>	$arParams["CACHE_TYPE"],
			"CACHE_TIME"		=>	$arParams["CACHE_TIME"],
			"BLOG_VAR"			=>	$arParams["VARIABLE_ALIASES"]["blog"],
			"POST_VAR"			=>	$arParams["VARIABLE_ALIASES"]["post_id"],
			"USER_VAR"			=>	$arParams["VARIABLE_ALIASES"]["user_id"],
			"PAGE_VAR"			=>	$arParams["VARIABLE_ALIASES"]["page"],
			"DATE_TIME_FORMAT"	=> $arParams["DATE_TIME_FORMAT"],
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			),
			$component 
		);
		?>
	</div>
	<?
	if(strlen($arResult["PATH_TO_HISTORY"]) <= 0)
		$arResult["PATH_TO_HISTORY"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arResult["ALIASES"]["page"]."=history");
	?>
	<div style="text-align:right;"><a href="<?=$arResult["PATH_TO_HISTORY"]?>"><?=GetMessage("BC_ALL_POSTS")?></a></div>

	</div>
	
</div>
<div class="blog-mainpage-side-right">

<div class="blog-tab-container">
	<div class="blog-tab-left"></div>
	<div class="blog-tab-right"></div>
	<div class="blog-tab">
		<span class="blog-tab-title"><?=GetMessage("BC_NEW_COMMENTS")?></span>
	</div>	
</div>
	<div class="blog-tab-content">
		<?
		$APPLICATION->IncludeComponent("bitrix:blog.new_comments", ".default", Array(
	"COMMENT_COUNT"		=> $arParams["MESSAGE_COUNT_MAIN"],
	"MESSAGE_LENGTH"	=>	$arParams["MESSAGE_LENGTH"],
	"PATH_TO_BLOG"		=>	$arParams["PATH_TO_BLOG"],
	"PATH_TO_POST"		=>	$arParams["PATH_TO_POST"],
	"PATH_TO_USER"		=>	$arParams["PATH_TO_USER"],
	"PATH_TO_GROUP_BLOG_POST"		=>	$arParams["PATH_TO_GROUP_BLOG_POST"],
	"PATH_TO_SMILE"		=>	$arParams["PATH_TO_SMILE"],
	"CACHE_TYPE"		=>	$arParams["CACHE_TYPE"],
	"CACHE_TIME"		=>	$arParams["CACHE_TIME"],
	"BLOG_VAR"			=>	$arParams["VARIABLE_ALIASES"]["blog"],
	"POST_VAR"			=>	$arParams["VARIABLE_ALIASES"]["post_id"],
	"USER_VAR"			=>	$arParams["VARIABLE_ALIASES"]["user_id"],
	"PAGE_VAR"			=>	$arParams["VARIABLE_ALIASES"]["page"],
	"DATE_TIME_FORMAT"	=> $arParams["DATE_TIME_FORMAT"],
	"GROUP_ID" 			=> $arParams["GROUP_ID"],
	),
	$component 
);
		?>
	</div>
<div class="blog-tab-container">
	<div class="blog-tab-left"></div>
	<div class="blog-tab-right"></div>
	<div class="blog-tab">
		<span class="blog-tab-items">
			<span id="new-blogs"><a href="javascript:BXBlogTabShow('new-blogs', 'blog');"><?=GetMessage("BC_NEW_BLOGS")?></a></span>
			<span id="popular-blogs" style="display:none;"><a href="javascript:BXBlogTabShow('popular-blogs', 'blog');"><?=GetMessage("BC_POPULAR_BLOGS")?></a></span>
		</span>
		<span class="blog-tab-title">
			<span id="new-blogs-title" style="display:none;"><?=GetMessage("BC_NEW_BLOGS_MES")?></span>
			<span id="popular-blogs-title"><?=GetMessage("BC_POPULAR_BLOGS_MES")?></span>
		</span>
	</div>	
</div>
	<div class="blog-tab-content">
	<div id="new-blogs-content" style="display:none;">
	<?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.new_blogs", 
				"", 
				Array(
						"BLOG_COUNT"	=> $arParams["BLOG_COUNT_MAIN"],
						"BLOG_VAR"		=> $arParams["VARIABLE_ALIASES"]["blog"],
						"USER_VAR"		=> $arParams["VARIABLE_ALIASES"]["user_id"],
						"PAGE_VAR"		=> $arParams["VARIABLE_ALIASES"]["page"],
						"PATH_TO_BLOG"	=> $arParams["PATH_TO_BLOG"],
						"PATH_TO_USER"	=> $arParams["PATH_TO_USER"],
						"PATH_TO_GROUP_BLOG"		=>	$arParams["PATH_TO_GROUP_BLOG"],
						"PATH_TO_GROUP"		=>	$arParams["PATH_TO_GROUP"],
						"CACHE_TYPE"	=> $arParams["CACHE_TYPE"],
						"CACHE_TIME"	=> $arParams["CACHE_TIME"],
						"GROUP_ID" 			=> $arParams["GROUP_ID"],
					),
				$component 
			);
		?>
	</div>
	<div id="popular-blogs-content" style="display:block;">
		<?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.popular_blogs", 
				"", 
				Array(
						"BLOG_COUNT"	=> $arParams["BLOG_COUNT_MAIN"],
						"PERIOD_DAYS"	=>	$arParams["PERIOD_DAYS"],
						"BLOG_VAR"		=> $arParams["VARIABLE_ALIASES"]["blog"],
						"USER_VAR"		=> $arParams["VARIABLE_ALIASES"]["user_id"],
						"PAGE_VAR"		=> $arParams["VARIABLE_ALIASES"]["page"],
						"PATH_TO_BLOG"	=> $arParams["PATH_TO_BLOG"],
						"PATH_TO_GROUP_BLOG"		=>	$arParams["PATH_TO_GROUP_BLOG"],
						"PATH_TO_GROUP"		=>	$arParams["PATH_TO_GROUP"],
						"PATH_TO_USER"	=> $arParams["PATH_TO_USER"],
						"CACHE_TYPE"	=> $arParams["CACHE_TYPE"],
						"CACHE_TIME"	=> $arParams["CACHE_TIME"],
						"GROUP_ID" 			=> $arParams["GROUP_ID"],
					),
				$component 
			);
		?>
	</div>
	<?
		if(strlen($arResult["PATH_TO_GROUP"]) <= 0)
			$arResult["PATH_TO_GROUP"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arResult["ALIASES"]["page"]."=group&".$arResult["ALIASES"]["group_id"]."=#group_id#");
		?>
		
		<div style="text-align:right;"><a href="<?=CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_GROUP"], array("group_id" => $arParams["GROUP_ID"]))?>"><?=GetMessage("BC_ALL_BLOGS")?></a></div>

	</div>
	<div class="blog-rss-subscribe">
	<?
	$APPLICATION->IncludeComponent(
			"bitrix:blog.rss.link",
			"mainpage",
			Array(
					"RSS1"				=> "N",
					"RSS2"				=> "Y",
					"ATOM"				=> "N",
					"BLOG_VAR"			=> $arParams["VARIABLE_ALIASES"]["blog"],
					"POST_VAR"			=> $arParams["VARIABLE_ALIASES"]["post_id"],
					"GROUP_VAR"			=> $arParams["VARIABLE_ALIASES"]["group_id"],
					"PATH_TO_RSS_ALL"	=> $arParams["PATH_TO_RSS"],
					"GROUP_ID"			=> $arParams["GROUP_ID"],
					"MODE"				=> "S",
				),
			$component 
		);
	?>
	</div>
</div>
<div class="blog-clear-float"></div>

<?
if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BLOG_TITLE"));
?>
</div>
</div>