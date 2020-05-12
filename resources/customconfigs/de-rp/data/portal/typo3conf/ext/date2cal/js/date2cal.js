function date2cal_setDatetime(id, date) {
	field = document.getElementById(id);
	if (field.value == false)
		field.value = date;
	else field.value = '';
}

function date2cal_activeDateField(idCb, idHr) {
	if (document.getElementById(idHr).value != '')
		document.getElementById(idCb).checked = true;
	else
		document.getElementById(idCb).checked = false;
}
