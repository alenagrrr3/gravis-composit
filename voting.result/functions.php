<?
if (!function_exists("_vote_answer_sort"))
{
	function _vote_answer_sort($ar1, $ar2)
	{
		if ($ar1["COUNTER"]<$ar2["COUNTER"]) 
			return 1;
		if ($ar1["COUNTER"]>$ar2["COUNTER"])
			return -1;
		return 0;
	}
}

?>