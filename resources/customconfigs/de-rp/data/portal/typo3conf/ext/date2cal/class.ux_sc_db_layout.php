<?php

class ux_SC_db_layout extends SC_db_layout
{
	function printContent()
	{
        $this->content = $this->doc->insertStylesAndJS($this->content);
        echo $this->content;
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/date2cal/class.ux_sc_db_layout.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/date2cal/class.ux_sc_db_layout.php']);
}

?>
