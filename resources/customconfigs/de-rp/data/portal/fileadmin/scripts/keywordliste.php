<?php
//ini override
ini_set("arg_separator.output","&amp;");

// Zugangsdaten für Datenbank
$host = "localhost";
$port = "5433";
$user = "postgres";
$password = "postgrespassword";
$dbname = "geoportal";


//  Verbindung  aufbauen
$conn = @pg_connect ("dbname=$dbname user=$user password=$password host=$host port=$port") or die ("Konnte keine Verbindung zur Datenbank herstellen.");
$db = pg_dbname ($conn);

// Query
$query = pg_query ($sql);

// Anzahl der Zeilen
$rows = pg_num_rows ($query);

//Navigation (Tabs mit Buchstabenbereichen)
if ($navi == true) {
  echo '<ul class=\'search-cat\'>'; 
  echo '<li ';
  if ($letter1=='A') echo 'class=\'active first\'';
  echo '><a href=\''.$url.'letter1=A&amp;letter2=C\'>A-C</a></li>';
  echo '<li ';
  if ($letter1=='D') echo 'class=\'active first\'';
  echo '><a href=\''.$url.'letter1=D&amp;letter2=F\'>D-F</a></li>';
  echo '<li ';
  if ($letter1=='G') echo 'class=\'active first\'';
  echo '><a href=\''.$url.'letter1=G&amp;letter2=I\'>G-I</a></li>';
  echo '<li ';
  if ($letter1=='J') echo 'class=\'active first\'';
  echo '><a href=\''.$url.'letter1=J&amp;letter2=L\'>J-L</a></li>';
  echo '<li ';
  if ($letter1=='M') echo 'class=\'active first\'';
  echo '><a href=\''.$url.'letter1=M&amp;letter2=O\'>M-O</a></li>';
  echo '<li ';
  if ($letter1=='P') echo 'class=\'active first\'';
  echo '><a href=\''.$url.'letter1=P&amp;letter2=R\'>P-R</a></li>';
  echo '<li ';
  if ($letter1=='S') echo 'class=\'active first\'';
  echo '><a href=\''.$url.'letter1=S&amp;letter2=U\'>S-U</a></li>';
  echo '<li ';
  if ($letter1=='V') echo 'class=\'active first\'';
  echo '><a href=\''.$url.'letter1=V&amp;letter2=Z\'>V-Z</a></li>';
  echo '<li ';
  if ($letter1=='1' and $letter2=='9') echo 'class=\'active first\'';
  echo '><a href=\''.$url.'letter1=1&amp;letter2=9\'>1-9</a></li>';
  echo '<li ';
  if ($letter1=='1' and $letter2=='Z') echo 'class=\'active first\'';
  echo '><a href=\''.$url.'letter1=1&amp;letter2=Z\'>alle</a></li>';
  echo '</ul>';
}

// Clearer
echo '<div class=\'clearer\'></div>';

//Beginn Container für Glossar
echo '<div class=\'glossar-container\'>';

?>
