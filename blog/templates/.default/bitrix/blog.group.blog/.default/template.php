<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?
if(strlen($arResult["FATAL_ERROR"])>0)
{
	?>
	<div class="blog-errors">
		<div class="blog-error-text">
			<?=$arResult["FATAL_ERROR"]?>
		</div>
	</div>
	<?
}
else
{
	if(count($arResult["BLOG"])>0)
	{
		foreach($arResult["BLOG"] as $arBlog)
		{
			if(IntVal($arBlog["LAST_POST_ID"])>0 || $arParams["SHOW_BLOG_WITHOUT_POSTS"] == "Y")
			{
				?>
			
			<div class="blog-mainpage-item">
			<?if(IntVal($arBlog["OWNER_ID"]) > 0)
			{
				?>
				<div class="blog-author">
				<a class="blog-author-icon" href="<?=$arBlog["urlToAuthor"]?>"></a><a href="<?=$arBlog["urlToBlog"]?>"><?=$arBlog["AuthorName"]?></a>
				</div>
				<?
			}
			?>

			<div class="blog-mainpage-title"><a href="<?=$arBlog["urlToBlog"]?>"><?echo $arBlog["NAME"]; ?></a></div>
			<?if(strlen($arBlog["DESCRIPTION"]) > 0)
			{
				?>
				<div class="blog-mainpage-content">
					<?=$arBlog["DESCRIPTION"]?>
				</div>
				<?
			}
			?>
			<?if(IntVal($arBlog["LAST_POST_ID"])>0):?>
				<div class="blog-mainpage-meta"><?=GetMessage("B_B_GR_LAST_M")?> <a href="<?=$arBlog["urlToPost"]?>"><?=$arBlog["LAST_POST_DATE_FORMATED"]?></a></div>
			<?endif;?>

			<div class="blog-clear-float"></div>
			</div>
			<div class="blog-line"></div>
					
				<?
			}
		}
		if(strlen($arResult["NAV_STRING"])>0)
			echo $arResult["NAV_STRING"];
	}
	else
		echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
}
?>	