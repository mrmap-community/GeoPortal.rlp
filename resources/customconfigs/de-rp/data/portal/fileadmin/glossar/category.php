<?php
include_once("fileadmin/function/util.php");
include_once("config.php");

Show_Cat();

function Show_Cat() {
	global $lexikon_cat_id, $url;
	$timestamp=time();

	$db=new DB_MYSQL;

	$sql="SELECT uid, title
	        FROM tt_news_cat
	       WHERE pid=$lexikon_cat_id AND deleted=0 AND hidden=0 AND $timestamp>=starttime AND ($timestamp<=endtime or endtime=0)
	    ORDER BY title";

	$db->query($sql);

	print '<div class="lexikon-category-container">';
	print '<h2>Lexikonthemen</h2>';
	print '<ul>';

	while($db->next_record()) {
		$uid=$db->f("uid");
		$title=$db->f("title");
		$class="";
		if($_REQUEST["tx_lexicon"]["cat"]==$uid) $class=" class='active'";
		print '<li'.$class.'><a href="'.$url.'?tx_lexicon[cat]='.$uid.'">'.$title.'</a></li>';
	}

	print '</ul>';
	print '</div>';
}



?>
