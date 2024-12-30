<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["PATH_TO_ICON"] = (empty($arParams["PATH_TO_ICON"]) ? $templateFolder."/images/icon" : $arParams["PATH_TO_ICON"]);
$arParams["PATH_TO_ICON"] = str_replace("//", "/", $arParams["PATH_TO_ICON"]."/");
global $find_forum, $find_date1, $find_date2;
/********************************************************************
				/Input params
********************************************************************/
// For filter only
$filter_value_fid = array(
	"0" => GetMessage("F_ALL_FORUMS"));
if (is_array($arResult["GROUPS_FORUMS"])):
	foreach ($arResult["GROUPS_FORUMS"] as $key => $res):
		if ($res["TYPE"] == "GROUP"):
			$filter_value_fid["GROUP_".$res["ID"]] = array(
				"NAME" => ($res["DEPTH"] > 0 ? str_pad("", ($res["DEPTH"] - 1)*4, " ") : "").$res["~NAME"], 
				"CLASS" => "forums level".$res["DEPTH"], 
				"TYPE" => "OPTGROUP");
		else:
			$filter_value_fid[$res["ID"]] = array(
				"NAME" => ($res["DEPTH"] > 0 ? str_pad("", ($res["DEPTH"] + 1)*4, " ") : "").$res["~NAME"], 
				"CLASS" => "forums level".$res["DEPTH"], 
				"TYPE" => "OPTION");
		endif;
	endforeach;
endif;
?>
<div class="forum-info-box forum-filter">
	<div class="forum-info-box-inner">
<?
$APPLICATION->IncludeComponent("bitrix:forum.interface", "filter_simple", 
	array(
		"HEADER" => array(
			"TITLE" => GetMessage("F_TITLE")),
		"FIELDS" => array(
			array(
				"NAME" => "PAGE_NAME",
				"TYPE" => "HIDDEN",
				"VALUE" => "active"),
			array(
				"TITLE" => GetMessage("F_FILTER_FORUM"),
				"NAME" => "find_forum",
				"TYPE" => "SELECT",
				"CLASS" => "forums",
				"VALUE" => $filter_value_fid,
				"ACTIVE" => $find_forum),
			array(
				"TITLE" => GetMessage("F_FILTER_LAST_MESSAGE_DATE"),
				"NAME" => "find_date1",
				"NAME_TO" => "find_date2",
				"TYPE" => "PERIOD",
				"VALUE" => $find_date1,
				"VALUE_TO" => $find_date2)
		)),
		$component,
		array(
			"HIDE_ICONS" => "Y"));?><?
?>
	</div>
</div>

<br/>
<?
if (!empty($arResult["ERROR_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;

if ($arResult["NAV_RESULT"]->NavPageCount > 0):
?><div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;
?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=GetMessage("F_TITLE")?></span></div>
</div>

<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
			<table cellspacing="0" class="forum-table forum-topic-list">
			<thead>
				<tr>
					<th class="forum-column-title" colspan="2"><div class="forum-head-title"><span><?=GetMessage("F_HEAD_TOPICS")?></span></div></th>
					<th class="forum-column-replies"><span><?=GetMessage("F_HEAD_POSTS")?><?/*?><?=$arResult["SortingEx"]["POSTS"]?><?*/?></span></th>
					<th class="forum-column-views"><span><?=GetMessage("F_HEAD_VIEWS")?></span><?/*?><?=$arResult["SortingEx"]["VIEWS"]?><?*/?></th>
					<th class="forum-column-lastpost"><span><?=GetMessage("F_HEAD_LAST_POST")?></span><?/*?><?=$arResult["SortingEx"]["LAST_POST_DATE"]?><?*/?></th>
				</tr>
			</thead>
			<tbody>
<?
if($arResult["SHOW_RESULT"] == "Y"):
	$iCount = 0;
	foreach ($arResult["TOPICS"] as $res):
		$iCount++;
?>
 				<tr class="<?=($iCount == 1 ? "forum-row-first " : (
 					$iCount == count($arResult["TOPICS"]) ? "forum-row-last " : ""))?><?
 					?><?=($iCount%2 == 1 ? "forum-row-odd " : "forum-row-even ")?><?
 					?><?=(intVal($res["SORT"]) != 150 ? "forum-row-sticky " : "")?><?
 					?><?=($res["STATE"] != "Y" && $res["STATE"] != "L" ? "forum-row-closed " : "")?><?
 					?><?=($res["TopicStatus"] == "MOVED" ? "forum-row-moved " : "")?><?
 					?><?=($res["APPROVED"] != "Y" ? " forum-row-hidden ": "")?><?
 					?>">
					<td class="forum-column-icon">
						<div class="forum-icon-container">
							<div class="forum-icon <?
							$title = ""; $class = "";
							if (intVal($res["SORT"]) != 150):
								?> forum-icon-sticky <?
								$title = GetMessage("F_PINNED_TOPIC");
							endif;
							if ($res["TopicStatus"] == "MOVED"):
								$title = GetMessage("F_MOVED_TOPIC");
								?> forum-icon-moved <?
							elseif ($res["STATE"] != "Y" && $res["STATE"] != "L"):
								$title = (intVal($res["SORT"]) != 150 ? GetMessage("F_PINNED_CLOSED_TOPIC") : GetMessage("F_CLOSED_TOPIC")).
									" (".GetMessage("F_HAVE_NEW_MESS").")";
									?> forum-icon-closed-newposts <?
							else:
								$title .= (empty($title) ? GetMessage("F_HAVE_NEW_MESS") : " (".GetMessage("F_HAVE_NEW_MESS").")");
								?> forum-icon-newposts <?
							endif;
							
							?>" title="<?=$title?>"><!-- ie --></div>
						</div>
					</td>
					<td class="forum-column-title">
						<div class="forum-item-info">
							<div class="forum-item-name"><?
						if ($res["TopicStatus"] == "MOVED"):
								?><span class="forum-status-moved"><?=GetMessage("F_MOVED")?></span>:&nbsp;<?
						elseif (intVal($res["SORT"]) != 150 && ($res["STATE"]!="Y") && ($res["STATE"]!="L")):
								?><span class="forum-status-sticky"><?=GetMessage("F_PINNED")?></span>, <span class="forum-status-closed"><?=GetMessage("F_CLOSED")?></span>:&nbsp;<?
						elseif (intVal($res["SORT"]) != 150):
								?><span class="forum-status-sticky"><?=GetMessage("F_PINNED")?></span>:&nbsp;<?
						elseif (($res["STATE"]!="Y") && ($res["STATE"]!="L")):
								?><span class="forum-status-closed"><?=GetMessage("F_CLOSED")?></span>:&nbsp;<?
						endif;
								?><span class="forum-item-title"><?
						if (false && strLen($res["IMAGE"]) > 0):
								?><img src="<?=$arParams["PATH_TO_ICON"].$res["IMAGE"];?>" alt="<?=$res["IMAGE_DESCR"];?>" border="0" width="15" height="15"/><?
						endif;
								?><a href="<?=$res["URL"]["TOPIC"]?>"><?=$res["TITLE"]?></a></span><?
						if ($res["PAGES_COUNT"] > 1):
								?> <span class="forum-item-pages">(<?
							$iCount = intVal($res["PAGES_COUNT"] > 5 ? 3 : $res["PAGES_COUNT"]);
							for ($ii = 1; $ii <= $iCount; $ii++):
								?><a href="<?=ForumAddPageParams($res["URL"]["~TOPIC"], array("PAGEN_".$arParams["PAGEN"] => $ii))?>"><?
									?><?=$ii?></a><?=($ii < $iCount ? ",&nbsp;" : "")?><?
							endfor;
							if ($iCount < $res["PAGES_COUNT"]):
								?>&nbsp;...&nbsp;<a href="<?=ForumAddPageParams($res["URL"]["~TOPIC"], 
									array("PAGEN_".$arParams["PAGEN"] => $res["PAGES_COUNT"]))?>"><?=$res["PAGES_COUNT"]?></a><?
							endif;
								?>)</span><?
						endif;
							?></div>
<?
						if (!empty($res["DESCRIPTION"])):
?>
							<span class="forum-item-desc"><?=$res["DESCRIPTION"]?></span><span class="forum-item-desc-sep">&nbsp;&middot; </span>
<?
						endif;
							?><span class="forum-item-author"><span><?=GetMessage("F_AUTHOR")?></span>&nbsp;<?=$res["USER_START_NAME"]?></span>
						</div>
					</td>
<?
						if ($res["PERMISSION"] >= "Q" && $res["mCnt"] > 0):
?>
					<td class="forum-column-replies forum-cell-hidden"><span><?=$res["POSTS"]?> <?
						?>(<a href="<?=$res["URL"]["MODERATE_MESSAGE"]?>" title="<?=GetMessage("F_MESSAGE_NOT_APPROVED")?>"><?=$res["mCnt"]?></a>)</span></td>
<?
						else:
?>
					<td class="forum-column-replies"><span><?=$res["POSTS"]?></span></td>
<?
						endif;
?>
					<td class="forum-column-views"><span><?=$res["VIEWS"]?></span></td>
					<td class="forum-column-lastpost"><?
						if ($res["LAST_MESSAGE_ID"] > 0):
?>
							<div class="forum-lastpost-box">
							<span class="forum-lastpost-date"><a href="<?=$res["URL"]["LAST_MESSAGE"]?>"><?=$res["LAST_POST_DATE"]?></a></span>
							<span class="forum-lastpost-title"><span class="forum-lastpost-author"><?=$res["LAST_POSTER_NAME"]?></span></span>
						</div>
<?
						endif;
?>
					</td>
				</tr>
<?
	endforeach;
else:
?>
 				<tr class="forum-row-first forum-row-odd">
					<td class="forum-column-icon" colspan="5">
						<div class="forum-item-info">
							<?=GetMessage("F_TOPICS_LIST_IS_EMPTY")?>
						</div>
					</td>
				</tr>
<?	
endif;

?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?
if ($arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-bottom">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;
?>