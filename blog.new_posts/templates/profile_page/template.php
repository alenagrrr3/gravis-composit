<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if(count($arResult) <= 0)
	echo GetMessage("SONET_BLOG_LM_EMPTY");
	
foreach($arResult as $arPost)
{
	if($arPost["FIRST"]!="Y")
	{
		?><div class="blog-profile-line"></div><?
	}
	?>
	<span class="blog-profile-post-date"><?=$arPost["DATE_PUBLISH_FORMATED"]?></span><br />
	<a href="<?=$arPost["urlToBlog"]?>" title=""><?=$arPost["AuthorName"]?></a><br />
	<b><a href="<?=$arPost["urlToPost"]?>"><?echo $arPost["TITLE"]; ?></a></b><br /><br />
	<?
	if(strlen($arPost["IMG"]) > 0)
		echo $arPost["IMG"];
	?>
	<?=$arPost["TEXT_FORMATED"]?><br clear="left"/><br />

	<span class="blog-profile-post-info">
		<?if(IntVal($arPost["VIEWS"]) > 0):?>
			<span class="blog-eye"><?=GetMessage("SONET_BLOG_LM_VIEWS")?></span>:&nbsp;<?=$arPost["VIEWS"]?>&nbsp;
		<?endif;?>
		<?if(IntVal($arPost["NUM_COMMENTS"]) > 0):?>
			<span class="blog-comment-num"><?=GetMessage("SONET_BLOG_LM_NUM_COMMENTS")?></span>:&nbsp;<?=$arPost["NUM_COMMENTS"]?>
		<?endif;?>
	</span>
	<?
}