Dateien:
LocalSettings.php: /var/lib/mediawiki/LocalSettings.php

Ordner:
Timeless: /usr/share/mediawiki/skins/Timeless
q4u_template: /usr/share/mediawiki/skins/q4u_template



In LocalSettings.php müssen folgende Werte eingesetzt werden (bitte die folgende Groß- und Kleinschreibung beachten):

$wgSitename = "RLP";
$wgServer = " --Ihre Domain (zB https://mediawiki.de)-- ";
$wgLanguageCode = "de";
$wgDefaultSkin = "timeless";
wfLoadSkin( 'Timeless' );



/Timeless/i18n/de.json
"timeless-search-placeholder": "Den Text von ungefähr {{NUMBEROFPAGES}} Seiten durchsuchen",
-> "timeless-search-placeholder": "{{NUMBEROFPAGES}} Seiten durchsuchen",

/Timeless/i18n/en.json
"timeless-search-placeholder": "Search the text of approximately {{NUMBEROFPAGES}} pages",
-> "timeless-search-placeholder": "Search through {{NUMBEROFPAGES}} pages",



q4u-styles erstetzt styles des timeless skins.
q4u-variables ersetzt variablen des timeless skins.



q4u-screen...less wird in der skin.json am ende der liste der styles unter print.css eingebunden
wenn man internet explorer nicht unterstützt, können die less dateien auch zusammengeführt werden und stattdessen css media queries verwendet werden. da der less compiler allerdings selbst die in der skin.json definierten media queries setzt, käme es zu einer dopplung, die im internet explorer falsch interpretiert würde.



folgender code muss in Timeless/skin.json[Zeile:50] (unter "print") eingefügt werden:

"../q4u_template/q4u-screen-common.less": {
  "media": "screen"
},
"../q4u_template/q4u-screen-desktop.less": {
  "media": "screen and (min-width: 851px)"
},
"../q4u_template/q4u-screen-desktop-large.less": {
  "media": "screen and (min-width: 1340px)"
},
"../q4u_template/q4u-screen-desktop-mid.less": {
  "media": "screen and (min-width: 1100px) and (max-width: 1339px)"
},
"../q4u_template/q4u-screen-desktop-small.less": {
  "media": "screen and (min-width: 851px) and (max-width: 1099px)"
},
"../q4u_template/q4u-screen-mobile.less": {
  "media": "screen and (max-width: 850px)"
},
"../q4u_template/q4u-screen-mobile-s.less": {
  "media": "screen and (max-width: 600px)"
},
"../q4u_template/q4u-screen-mobile-xs.less": {
  "media": "screen and (max-width: 400px)"
},
"../q4u_template/q4u-screen-mobile-xxs.less": {
  "media": "screen and (max-width: 374px)"
}



q4u-variables.less wird in der variables.less ganz am ende eingebunden
@import "../../q4u_template/q4u-variables.less";
