<?php
# $Id: login.php 7138 2010-11-16 14:37:08Z christoph $
# Copyright (C) 2002 CCGIS
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2, or (at your option)
# any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.

require_once dirname(__FILE__) ."/../../core/globalSettings.php";
?>
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">
<META http-equiv="Content-Style-Type" content="text/css">
<META http-equiv="Content-Script-Type" content="text/javascript">
<?php
echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';
?>
<title>Zugriff verweigert</title>

<link rel="stylesheet" type="text/css" href="../css/login.css" />
<link rel="shortcut icon" href="../img/favicon.ico" />

<div>
	<!-- <h3>Anmeldung im GeoPortal Saarland</h3>  -->
	<?php
		echo "<p>Die Anmeldung im GeoPortal Saarland ist ".MAXLOGIN." mal fehlgeschlagen. Ihr Benutzerkonto wurde gesperrt. Bitte versuchen Sie es in ".REACTIVATE_LOGIN_TIME." Minuten wieder.</p>";
	?>
	<p>Hier geht es zurück zur Startseite des GeoPortal Saarland: <a href="../geoportal/mod_login.php">Startseite GeoPortal Saarland</a></p>
</div>