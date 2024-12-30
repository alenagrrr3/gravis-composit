<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<div class="blog-mainpage-comment">
<?
foreach($arResult as $arComment)
{
	if($arComment["FIRST"]!="Y")
	{
		?><div class="blog-line"></div><?
	}
	?>
	
	<div class="blog-mainpage-item">
	<div class="blog-author">
	<?if(strlen($arComment["urlToBlog"])>0)
	{
		?>
		<a class="blog-author-icon" href="<?=$arComment["urlToAuthor"]?>"></a><a href="<?=$arComment["urlToBlog"]?>"><?=$arComment["AuthorName"]?></a>
		<?
	}
	elseif(strlen($arComment["urlToAuthor"])>0)
	{
		?>
		<a class="blog-author-icon" href="<?=$arComment["urlToAuthor"]?>"></a><a href="<?=$arComment["urlToAuthor"]?>"><?=$arComment["AuthorName"]?></a>
		<?
	}
	else
	{
		?>
		<span class="blog-author-icon"></span><?=$arComment["AuthorName"]?>
		<?
	}?>
	</div>
	<div class="blog-mainpage-meta">
		<a href="<?=$arComment["urlToComment"]?>" title="<?=GetMessage("BLOG_BLOG_M_DATE")?>"><?=$arComment["DATE_CREATE_FORMATED"]?></a>
	</div>
	<div class="blog-clear-float"></div>
	<!--<div class="blog-mainpage-title"><a href="<?=$arComment["urlToComment"]?>"><?echo $arComment["POST_TITLE_FORMATED"]; ?></a></div>//-->
	<div class="blog-mainpage-content">
		<a href="<?=$arComment["urlToComment"]?>"><?=$arComment["TEXT_FORMATED"]?></a>
	</div>

	<div class="blog-clear-float"></div>
	</div>
	<?
}
?>	
</div>