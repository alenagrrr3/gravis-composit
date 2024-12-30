<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<div class="blog-mainpage-blogs">
<?
foreach($arResult as $arBlog)
{
	if($arBlog["FIRST_BLOG"]!="Y")
	{
		?><div class="blog-line"></div><?
	}
	?>
	
	<div class="blog-mainpage-item">
	<div class="blog-author">
		<a class="blog-author-icon" href="<?=$arBlog["urlToAuthor"]?>"></a><a href="<?=$arBlog["urlToBlog"]?>"><?=$arBlog["AuthorName"]?></a>
	</div>
	<div class="blog-clear-float"></div>
	<div class="blog-mainpage-title"><a href="<?=$arBlog["urlToBlog"]?>"><?echo $arBlog["NAME"]; ?></a></div>
	<?if($arParams["SHOW_DESCRIPTION"] == "Y" && strlen($arBlog["DESCRIPTION"]) > 0)
	{
		?>
		<div class="blog-mainpage-content">
			<?=$arBlog["DESCRIPTION"]?>
		</div>
		<?
	}
	?>
	<div class="blog-clear-float"></div>
	</div>
	<?
}
?>	
</div>