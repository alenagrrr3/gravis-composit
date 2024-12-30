<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<div id="blog-new-posts-content">
<?
if(count($arResult["POSTS"])>0)
{
	foreach($arResult["POSTS"] as $CurPost)
	{
		?>
			<div class="blog-post">
				<h2 class="blog-post-title"><a href="<?=$CurPost["urlToPost"]?>" title="<?=$CurPost["TITLE"]?>"><?=$CurPost["TITLE"]?></a></h2>
				<div class="blog-post-info-back">
				<div class="blog-post-info">
					<div class="blog-author"><a class="blog-author-icon" href="<?=$CurPost["urlToAuthor"]?>"></a><a class="" href="<?=$CurPost["urlToBlog"]?>"><?=$CurPost["AuthorName"]?></a></div>
					<div class="blog-post-date"><?=$CurPost["DATE_PUBLISH_FORMATED"]?></div>
				</div>
				</div>
				<div class="blog-post-content">
					<div class="blog-post-avatar"><?=$CurPost["BlogUser"]["AVATAR_img"]?></div>
					<?=$CurPost["TEXT_FORMATED"]?>
					<?
					if ($CurPost["CUT"] == "Y")
					{
						?><p><a class="blog-postmore-link" href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_MORE")?></a></p><?
					}
					?>
					<?if($CurPost["POST_PROPERTIES"]["SHOW"] == "Y"):?>
						<p>
						<?foreach ($CurPost["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):?>
						<?if(strlen($arPostField["VALUE"])>0):?>
						<b><?=$arPostField["EDIT_FORM_LABEL"]?>:</b>&nbsp;<?$APPLICATION->IncludeComponent(
										"bitrix:system.field.view", 
										$arPostField["USER_TYPE"]["USER_TYPE_ID"], 
										array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?>
						<?endif;?>
						<?endforeach;?>
						</p>
					<?endif;?>
				</div>
				
				<div class="blog-post-meta">
					<div class="blog-post-meta-util">
						<span class="blog-post-comments-link"><a href="<?=$CurPost["urlToPost"]?>#comment"><?=GetMessage("BLOG_BLOG_BLOG_COMMENTS")?> <?=IntVal($CurPost["NUM_COMMENTS"]);?></a></span>
						<span class="blog-vert-separator">|</span>
						<span class="blog-post-views-link"><a href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_VIEWS")?> <?=IntVal($CurPost["VIEWS"]);?></a></span>
					</div>

					<div class="blog-post-tag">
						<?
						if(!empty($CurPost["CATEGORY"]))
						{
							echo GetMessage("BLOG_BLOG_BLOG_CATEGORY");
							$i=0;
							foreach($CurPost["CATEGORY"] as $v)
							{
								if($i!=0)
									echo ",";
								?> <a href="<?=$v["urlToCategory"]?>"><?=$v["NAME"]?></a><?
								$i++;
							}
						}
						?>
	
					</div>

				</div>
			</div>
		<?
	}
	if(strlen($arResult["NAV_STRING"])>0)
		echo $arResult["NAV_STRING"];
}
?>	
</div>