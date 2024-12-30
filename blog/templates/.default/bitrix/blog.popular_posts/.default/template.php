<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?
if(empty($arResult))
	echo GetMessage("SONET_BLOG_EMPTY");
	
foreach($arResult as $arPost)
{
	if($arPost["FIRST"]!="Y")
	{
		?><div class="blog-line"></div><?
	}
	?>
	
	<div class="blog-mainpage-item">
	<div class="blog-author"><a class="blog-author-icon" href="<?=$arPost["urlToAuthor"]?>" title="<?=GetMessage("BLOG_BLOG_M_TITLE_BLOG")?>"></a><a href="<?=$arPost["urlToBlog"]?>"><?=$arPost["AuthorName"]?></a></div>
<div class="blog-clear-float"></div>
	<div class="blog-mainpage-title"><a href="<?=$arPost["urlToPost"]?>"><?echo $arPost["TITLE"]; ?></a></div>
	<div class="blog-mainpage-content">
	<?=$arPost["TEXT_FORMATED"]?>
	</div>
	<div class="blog-mainpage-meta">
		<a href="<?=$arPost["urlToPost"]?>" title="<?=GetMessage("BLOG_BLOG_M_DATE")?>"><?=$arPost["DATE_PUBLISH_FORMATED"]?></a>
		<?if(IntVal($arPost["VIEWS"]) > 0):?>
			<span class="blog-vert-separator">|</span> <a href="<?=$arPost["urlToPost"]?>" title="<?=GetMessage("BLOG_BLOG_M_VIEWS")?>"><?=GetMessage("BLOG_BLOG_M_VIEWS")?>:&nbsp;<?=$arPost["VIEWS"]?></a>
		<?endif;?>
		<?if(IntVal($arPost["NUM_COMMENTS"]) > 0):?>
			<span class="blog-vert-separator">|</span> <a href="<?=$arPost["urlToPost"]?>#comment" title="<?=GetMessage("BLOG_BLOG_M_NUM_COMMENTS")?>"><?=GetMessage("BLOG_BLOG_M_NUM_COMMENTS")?>:&nbsp;<?=$arPost["NUM_COMMENTS"]?></a>
		<?endif;?>
	</div>
	<div class="blog-clear-float"></div>
	</div>
	<?
}
?>	
