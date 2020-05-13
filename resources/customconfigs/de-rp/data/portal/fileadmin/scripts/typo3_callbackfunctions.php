<?php
class user_callback	{
	var $cObj;		// Reference to the parent (calling) cObj set from TypoScript

	function randomize($content,$conf)	{
		$temp=split(',',$content);
		return $temp[array_rand($temp)];
	}
	
}
?>