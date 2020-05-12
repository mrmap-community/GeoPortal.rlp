<?php
require_once(dirname(__FILE__)."/../php/mb_validateSession.php");
include '../include/dyn_js.php';
?>
/**** asearch_button.php ****/
(function() {
    var self = $('#asearch_button');
    var api = self.data('api');
    var dlgPosition = typeof position === 'undefined' ? 'center': position;
    var dlg = $('<div></div>').append(api.$target).dialog({
	width: breite,
	height: hoehe,
	title: titel,
	autoOpen: false,
	position: dlgPosition
    });
    self.data('dlg', dlg);

    self.bind('click', function() {
        dlg.dialog('open');
    });
})();

