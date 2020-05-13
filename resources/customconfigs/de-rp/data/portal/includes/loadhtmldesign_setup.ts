
#AdminPanel
#config.admPanel = 1
config.doctype = xhtml_strict

#Bemerkung Ein/Ausblenden
config.disablePrefixComment = 1

# Nur Seiten in der korrekten Sprache anzeigen - kein fallback auf die Standardsprache # config.sys_language_mode = strict

includeLibs.functions = fileadmin/scripts/typo3_callbackfunctions.php

temp.seite = PAGE
temp.seite {
   typeNum = 0

   config {
     disableAllHeaderCode = 1
     xhtml_cleaning = all

# RealURL aktivieren
     tx_realurl_enable = 1
     prefixLocalAnchors = all
   }

   10 = TEMPLATE
   10 {
     template = FILE
     template.file = {$templatefile}

     marks {

       HEADERDATA = TEXT
       HEADERDATA.value (
       <script type="text/javascript" src="../mapbender/extensions/jquery-ui-1.12.1.custom/jquery-3.3.1.min.js"></script>
       <script type="text/javascript" src="../mapbender/extensions/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
       <link type="text/css" href="../mapbender/extensions/jquery-ui-1.12.1.custom/jquery-ui.min.css" rel="Stylesheet" property="stylesheet"/>
       <!--<script type="text/javascript" src="fileadmin/scripts/jquery.min.js"></script>-->
       <script type="text/javascript" src="fileadmin/scripts/slimbox-2.04/js/slimbox2.js"></script>
       <link rel="stylesheet" type="text/css" href="fileadmin/scripts/slimbox-2.04/css/slimbox2.css" media="screen" />
       )

#Navigation
#      NAVI2 < temp.SubNavi
#      NAVI2.stdWrap.outerWrap = <div class="box" id="navi2">|</div>
#      NAVI2.stdWrap.required = 1
#      NAVI2.stdWrap.prepend < temp.MainNaviAct

       NAVI1 < temp.MainNavi

       NAVI0 < temp.ServiceNavi
       MAINIMAGE < temp.Bild

#      HOST = TEXT
#      HOST.value = rlpnew.lab.q4u.de
#      HOST.wrap = http://|/

       HOST = PHP_SCRIPT_EXT
       HOST.file = fileadmin/design/hostname.php

       ZOOM = PHP_SCRIPT_EXT
       ZOOM.file = fileadmin/scripts/zoomkeys.php

       BODYSIZE = PHP_SCRIPT_INT
       BODYSIZE.file = fileadmin/scripts/fontsize.php

# Glossar
       GLOSSAR = COA
       GLOSSAR {
         10 < styles.content.get
         includeLibs = typo3conf/ext/q4u_glossar/pi1/class.tx_q4uglossar_pi1.php
         stdWrap.postUserFunc = tx_q4uglossar_pi1->parse
       }

# Optimiert für - Text
#      OPTIMIZED = TEXT
#      OPTIMIZED.value = {$optimized}

#LoginBox
#      LOGINBOX = PHP_SCRIPT_EXT
#      LOGINBOX.file = fileadmin/mygeoportal/logininfo.php

#LoginInfo
       LOGININFO = PHP_SCRIPT_EXT
       LOGININFO.file = fileadmin/mygeoportal/logininfo.php

#Textsize
#      TEXTSIZE = PHP_SCRIPT_EXT
#      TEXTSIZE.file = fileadmin/mygeoportal/textsize.php

#Startseite Zufallsbilder
#      STARTIMAGE = PHP_SCRIPT_EXT
#      STARTIMAGE.file = fileadmin/design/startimage.php

#Suche
       LIVESEARCH = PHP_SCRIPT_EXT
       LIVESEARCH.file = typo3conf/ext/q4u_search/pi1/ajaxsearch.php

       SEARCHLEFT = PHP_SCRIPT_EXT
       SEARCHLEFT.file = typo3conf/ext/q4u_search/pi1/search_left.php

       BREADCRUMB < temp.BreadCrumb

       CONTENT = COA
       CONTENT {
         stdWrap.postUserFunc = tx_q4ucontentparser_pi1->parse
         stdWrap.postUserFunc.parse < plugin.tx_q4ucontentparser_pi1.parse
         10 < styles.content.get
       }

       RIGHT = COA
       RIGHT {
         stdWrap.postUserFunc = tx_q4ucontentparser_pi1->parse
         stdWrap.postUserFunc.parse < plugin.tx_q4ucontentparser_pi1.parse
         10 < styles.content.getRight
       }


# WAI-Logo
#      WAI < plugin.tx_q4u_accessibility_logo.logo

# Stimmungsbildin Marginalspalte
#      MARGINIMAGE = COA
#      MARGINIMAGE {
#        10 < plugin.tx_marginal.marginal
#      }


#Hostname
#      HOST = PHP_SCRIPT_EXT
#      HOST.file = fileadmin/design/hostname.php

#Realurl
#      REALURL = PHP_SCRIPT_EXT
#      REALURL.file = fileadmin/design/realurl.php

#Browserweiche
#      STYLESHEET = PHP_SCRIPT_EXT
#      STYLESHEET.file = fileadmin/design/browser.php

#Metatag Keywords
#      KEYWORDS=TEXT
#      KEYWORDS.field = keywords

#Metatag Description
       METADES=TEXT
       METADES.field = abstract

#Title Description
       TITLEDES=TEXT
       TITLEDES.field=description
       TITLEDES.ifEmpty.field=title
     }
   }
}

# Auf News-Detailseite News-Titel ausgeben
[globalVar = TSFE:id = 34    ]
temp.seite.10.marks.TITLEDES=RECORDS
temp.seite.10.marks.TITLEDES {
   source.data = GPvar:tx_ttnews|tt_news
   tables = tt_news
   conf.tt_news = TEXT
   conf.tt_news {
     field = title
   }
}
[global]

#Loginseite mit speziellem Marker
# [globalVar = TSFE:id = 34]
# temp.seite.10.marks.LOGIN < temp.LOGIN # [else] # temp.seite.10.marks.LOGIN = TEXT # [global]

--
Zentrale Stelle Geodateninfrastruktur
Rheinland-Pfalz
LVermGeo-RP

Von-Kuhl-Straße 49
56070 Koblenz

0261/492-466
armin.retterath@vermkv.rlp.de
http://www.geoportal.rlp.de

