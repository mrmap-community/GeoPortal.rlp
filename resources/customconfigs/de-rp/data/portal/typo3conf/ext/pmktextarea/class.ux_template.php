<?php
class ux_template extends template {
	var $extKey = 'pmktextarea';
	
	function startPage($title) {
		$content = parent::startPage($title);
		
		if (!$GLOBALS["BE_USER"]->uc['disablePMKTextarea'])	{
			$pmkTAconf = unserialize($GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"][$this->extKey]);
			$taInitCode='';
			while (list($k,$v) = each($pmkTAconf)) {
				if ($v!='') $taInitCode.= $k.': "'.$v.'",';
			}
			$taInitCode.='languageKey: "'.$GLOBALS['LANG']->lang.'"';
			$replace = '<script type="text/javascript">if (typeof ta_init == "undefined") var ta_init = {'.$taInitCode.'};</script>
				<script src="'.$this->backPath.t3lib_extMgm::extRelPath($this->extKey).'pmk_textarea.js" type="text/javascript"></script>';
	 		if ($GLOBALS["BE_USER"]->uc['disableTabInTextarea']) {
				$content = str_replace('<!--###POSTJSMARKER###-->',$replace.' <!--###POSTJSMARKER###-->',$content);
			}
			else {
				$content = str_replace('<script src="'.$this->backPath.'tab.js" type="text/javascript"></script>',$replace,$content);
			}
		}
		return $content;
	}
}
class ux_bigDoc extends ux_template {
	var $divClass = 'typo3-bigDoc';
}
class ux_noDoc extends ux_template {
	var $divClass = 'typo3-noDoc';
}
class ux_smallDoc extends ux_template {
	var $divClass = 'typo3-smallDoc';
}
class ux_mediumDoc extends ux_template {
	var $divClass = 'typo3-mediumDoc';
}
?>