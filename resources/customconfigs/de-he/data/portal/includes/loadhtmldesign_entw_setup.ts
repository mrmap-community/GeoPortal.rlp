#AdminPanel
#config.admPanel = 1
config.doctype = xhtml_strict

#Bemerkung Ein/Ausblenden            
config.disablePrefixComment = 1

# Nur Seiten in der korrekten Sprache anzeigen - kein fallback auf die Standardsprache
# config.sys_language_mode = strict

includeLibs.functions = fileadmin/scripts/typo3_callbackfunctions.php

temp.seite = PAGE
temp.seite {
  typeNum = 0

  config {
    disableAllHeaderCode = 1
    xhtml_cleaning = all
    removeDefaultJS = 0
    #RealURL aktivieren
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
	    <link rel="stylesheet" title="Standard" type="text/css" href="fileadmin/design/geoportal.css" media="all" />
	    <link rel="alternate stylesheet" title="Schwarz / Weiß" type="text/css" href="fileadmin/design/geoportal_bw.css" media="all" />
	    <link rel="alternate stylesheet" title="Weiß / Schwarz" type="text/css" href="fileadmin/design/geoportal_wb.css" media="all" />
        )

       GEOPORTALTITLE = TEXT
       GEOPORTALTITLE.value (
        Entwicklungsumgebung Geoportal Hessen
        )

       STYLESWITCH = PHP_SCRIPT_EXT
       STYLESWITCH.file = fileadmin/mygeoportal/stylswitch.php

	   #LoginInfo
       LOGININFO = PHP_SCRIPT_EXT
       LOGININFO.file = fileadmin/mygeoportal/logininfo.php

       #Navigation
       NAVI1 < temp.MainNavi
       NAVI2 < LeftNavi
       NAVI3 < FooterNavi
       NAVI0 < temp.ServiceNavi
       NAVI5 < HeaderNavi

       HOST = PHP_SCRIPT_EXT
       HOST.file = fileadmin/design/hostname.php

#       ZOOM = PHP_SCRIPT_EXT
#       ZOOM.file = fileadmin/scripts/zoomkeys.php

#       BODYSIZE = PHP_SCRIPT_INT
#       BODYSIZE.file = fileadmin/scripts/fontsize.php

       #Glossar
       GLOSSAR = COA
       GLOSSAR {  
        100 < styles.content.get
        includeLibs = typo3conf/ext/q4u_glossar/pi1/class.tx_q4uglossar_pi1.php
        stdWrap.postUserFunc = tx_q4uglossar_pi1->parse
        }

       #Optimiert für - Text
#       OPTIMIZED = TEXT
#       OPTIMIZED.value = {$optimized}

       #LoginBox
#       LOGINBOX = PHP_SCRIPT_EXT
#       LOGINBOX.file = fileadmin/mygeoportal/logininfo.php

       #Textsize
#       TEXTSIZE = PHP_SCRIPT_EXT
#       TEXTSIZE.file = fileadmin/mygeoportal/textsize.php

       #Startseite Zufallsbilder
#       STARTIMAGE = PHP_SCRIPT_EXT
#       STARTIMAGE.file = fileadmin/design/startimage.php

       #Suche
       LIVESEARCH = PHP_SCRIPT_EXT
       LIVESEARCH.file = typo3conf/ext/q4u_search/pi1/ajaxsearch.php

       SEARCHLEFT = PHP_SCRIPT_EXT
       SEARCHLEFT.file = typo3conf/ext/q4u_search/pi1/suchschlitz.php
      
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
      
       LEFT = COA
       LEFT {
        stdWrap.postUserFunc = tx_q4ucontentparser_pi1->parse
        stdWrap.postUserFunc.parse < plugin.tx_q4ucontentparser_pi1.parse
        10 < styles.content.getLeft
        }
      
       RAND = COA
       RAND {
        stdWrap.postUserFunc = tx_q4ucontentparser_pi1->parse
        stdWrap.postUserFunc.parse < plugin.tx_q4ucontentparser_pi1.parse
        10 < styles.content.getBorder
        }

       #Browserweiche
#       STYLESHEET = PHP_SCRIPT_EXT
#       STYLESHEET.file = fileadmin/design/browser.php

       #Metatag Keywords
#       KEYWORDS=TEXT
#       KEYWORDS.field = keywords

       #Metatag Description
       METADES=TEXT
       METADES.field = abstract

       #Title Description
       TITLEDES=TEXT
       TITLEDES.field=description
       TITLEDES.ifEmpty.field=title
       #marksEnde
       }
     #templateEnde
     }
  #seiteEnde
  }