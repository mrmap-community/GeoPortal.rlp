<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
	<title></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />

</head>
<body>
<h2>Geänderter Content</h2>
<table border=1>

<?php
include_once(dirname(__FILE__)."/../function/util.php");
include_once("diff.php");

$zeit=time()-(60*60*24*700);

$newpre='<span style="background:green">';
$newpost='</span>';
$oldpre='<span style="background:red">';
$oldpost='</span>';


$db=new DB_MYSQL;
$db2=new DB_MYSQL;

$sql="SELECT sys_log.uid, sys_history.history_data, sys_log.action, sys_log.recuid, sys_log.tstamp, sys_log.tablename FROM sys_log
      INNER JOIN sys_history ON (sys_log.uid=sys_history.sys_log_uid)
      WHERE sys_log.tablename IN ('pages','tt_content','tt_news') AND sys_log.tstamp > ".$zeit."
      ORDER BY sys_log.tstamp desc";

$db->query($sql);
if($db->num_rows()) {
	while($db->next_record()) {
		$history=unserialize($db->f("history_data"));

//		print "---".$db->f("uid")."---<br/>\n";
//		var_dump($history);

		$old=$new=$typ="";
		if($db->f("tablename")=="tt_content") {
			$old=$history["oldRecord"]["bodytext"];
			$new=$history["newRecord"]["bodytext"];
			$typ="Content";
		} elseif($db->f("tablename")=="pages") {
			$old=$history["oldRecord"]["title"];
			$new=$history["newRecord"]["title"];
			$typ="Page";
		} elseif($db->f("tablename")=="tt_news") {
			$old=$history["oldRecord"]["title"]."   ".$history["oldRecord"]["short"]."   ".$history["oldRecord"]["bodytext"];
			$new=$history["newRecord"]["title"]."   ".$history["newRecord"]["short"]."   ".$history["newRecord"]["bodytext"];
			$typ="News";
		}
		$old=strip_tags($old);
		$new=strip_tags($new);

		if($old!="" || $new!="") {
			list($old,$new)=show_diff($old, $new);
			print "<tr>";
			print "<td>".$db->f("action")."</td>";
			if($typ=="Page") {
				print "<td><a href='/index.php?id=".$db->f("recuid")."'>".$typ."</a></td>";
			} elseif($typ=="Content") {
				$sql="SELECT * FROM tt_content WHERE uid=".$db->f("recuid");
				$db2->query($sql);
				$db2->next_record();
				print "<td><a href='/index.php?id=".$db2->f("pid")."'>".$typ."</a></td>";
			} elseif($typ=="News") {
//<a href='/suche.$ttnewsshowid.0.html?$sword&cHash=1c2d3a&tx_ttnews[backPid]=$backPid&tx_ttnews[tt_news]=$uid'>$title</a>

				print "<td><a href='/index.php?id=34&cHash=1c2d3a&tx_ttnews&tx_ttnews[tt_news]=".$db->f("recuid")."'>".$typ."</a></td>";
			}
			print "<td>".date("d.m. H:i",$db->f("tstamp"))."</td>";
			print "<td>".$old."</td>";
			print "<td>".$new."</td>";
			print "</tr>";
		}
	}
}
?>
</table>
<h2>Neuer Content</h2>
<table border=1>
<?php
$sql="SELECT tt_content.pid, tt_content.bodytext, sys_log.action, sys_log.tstamp FROM sys_log
      INNER JOIN tt_content ON (sys_log.recuid=tt_content.uid)
      WHERE sys_log.tablename IN ('tt_content') AND sys_log.tstamp > ".$zeit." AND sys_log.action=1 AND deleted=0
      ORDER BY sys_log.tstamp desc";

$db->query($sql);
if($db->num_rows()) {
	while($db->next_record()) {
		$new=strip_tags($db->f("bodytext"));
		$typ="Content";

		if($old!="" || $new!="") {
			print "<tr>";
			print "<td>".$db->f("action")."</td>";
			print "<td><a href='/index.php?id=".$db->f("pid")."'>".$typ."</a></td>";
			print "<td>".date("d.m. H:i",$db->f("tstamp"))."</td>";
			print "<td>".$new."</td>";
			print "</tr>";
		}
	}
}
?>
</table>
<h2>Neue Pages</h2>
<table border=1>
<?php
$sql="SELECT pages.uid, pages.title, sys_log.action, sys_log.tstamp FROM sys_log
      INNER JOIN pages ON (sys_log.recuid=pages.uid)
      WHERE sys_log.tablename IN ('pages') AND sys_log.tstamp > ".$zeit." AND sys_log.action=1 AND deleted=0
      ORDER BY sys_log.tstamp desc";

$db->query($sql);
if($db->num_rows()) {
	while($db->next_record()) {
		$new=strip_tags($db->f("title"));
		$typ="Pages";

		if($old!="" || $new!="") {
			print "<tr>";
			print "<td>".$db->f("action")."</td>";
			print "<td><a href='/index.php?id=".$db->f("uid")."'>".$typ."</a></td>";
			print "<td>".date("d.m. H:i",$db->f("tstamp"))."</td>";
			print "<td>".$new."</td>";
			print "</tr>";
		}
	}
}
?>
</table>
<h2>Neue News</h2>
<table border=1>
<?php
$sql="SELECT tt_news.title, tt_news.short, tt_news.bodytext, sys_log.action, sys_log.tstamp FROM sys_log
      INNER JOIN tt_news ON (sys_log.recuid=tt_news.uid)
      WHERE sys_log.tablename IN ('tt_news') AND sys_log.tstamp > ".$zeit." AND sys_log.action=1 AND deleted=0
      ORDER BY sys_log.tstamp desc";

$db->query($sql);
if($db->num_rows()) {
	while($db->next_record()) {
		$new=strip_tags($db->f("title")."   ".$db->f("short")."   ".$db->f("bodytext"));

		$typ="News";

		if($old!="" || $new!="") {
			print "<tr>";
			print "<td>".$db->f("action")."</td>";
			print "<td>".$typ."</td>";
			print "<td>".date("d.m. H:i",$db->f("tstamp"))."</td>";
			print "<td>".$new."</td>";
			print "</tr>";
		}
	}
}
?>
</table>
</body>
</html>