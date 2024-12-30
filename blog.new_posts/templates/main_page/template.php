<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
?>
<div class="bx-new-layout-include">
<?
if(empty($arResult))
	echo GetMessage("BLOG_BLOG_EMPTY");

foreach($arResult as $arPost)
{
	?>
	<div class="blg-mp-info">
		<div class="blg-mp-info-inner">
			<div class="blg-mp-date intranet-date"><?echo $arPost["DATE_PUBLISH_FORMATED"];?></div>
			<div class="blg-mp-name"><a href="<?=$arPost["urlToBlog"]?>" title=""><?=$arPost["AuthorName"]?></a></div>
			<div class="blg-mp-post"><a href="<?=$arPost["urlToPost"]?>"><?echo $arPost["TITLE"]?></a></div>
			<?if(IntVal($arPost["VIEWS"]) > 0):?>
				<div class="blg-mp-post"><?=GetMessage("BLOG_BLOG_M_VIEWS")?> <?=$arPost["VIEWS"]?></div>
			<?endif;?>
			<?if(IntVal($arPost["NUM_COMMENTS"]) > 0):?>
				<div class="blg-mp-post"><?=GetMessage("BLOG_BLOG_M_NUM_COMMENTS")?> <?=$arPost["NUM_COMMENTS"]?></div>
			<?endif;?>
			<div class="bx-users-delimiter"></div>
		</div>
	</div>
	<?
}
?>	
</div>