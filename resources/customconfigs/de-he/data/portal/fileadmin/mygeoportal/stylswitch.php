<?php
//print "<script src=\"/mapbender/extensions/jquery-ui-1.8.16.custom/js/jquery-1.6.2.min.js\"></script> ";
print "<script type=\"text/javascript\">";
print "$(document).ready(function(){ ";
print "    $(\".ps_black_white\").click(function(){";
print "		$(\"link\").attr(\"href\", \"fileadmin/design/geoportal_bw.css\");";
print "		return false;";
print "		});";
print "    $(\".ps_white_black\").click(function(){";
print "		$(\"link\").attr(\"href\", \"fileadmin/design/geoportal_wb.css\");";
print "		return false; ";
print "		});";
print "    $(\".ps_standard\").click(function(){";
print "		$(\"link\").attr(\"href\", \"fileadmin/design/geoportal_std.css\");";
print "		return false;";
print "		});";
print "    });";
print "</script> ";

?>
<ul id="pagestyle" class="pagestyle_innen">
    <li class="ps_black_white"><a class="ps_icon ps_icon_black_white" tabindex="5" title="Webseiten-Stil: Schwarz/Weiss" href="#"><span class="display_hidden">Schwarz / Weiss</span></a></li>
    <li class="ps_white_black"><a class="ps_icon ps_icon_white_black" tabindex="6" title="Webseiten-Stil: Weiss/Schwarz" href="#"><span class="display_hidden">Weiss / Schwarz</span></a></li>
    <li class="ps_standard"><a class="ps_icon ps_icon_standard" tabindex="7" title="Webseiten-Stil: Standard" href="#"><span class="display_hidden">Standard</span></a></li>
  </ul>
