<script type="text/javascript" src="javascript/modules/hms/formDialog/formDialog.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#formDialog").dialog({autoOpen: false, modal: true, width: 425, resizable: false, position: 'top'});
	
	var elements = new Array({ELEMENTS_TO_BIND});
	
	for (var i in elements){
		$(elements[i]).bind('change', formDialogShow2);
	}
});

function formDialogShow2()
{
	alert('changed! ' + $(this).val());
	
	$.ajax({
		url: "index.php",
		data: {module: 'hms',
			   action: '{ACTION}',
			   id: $(this).val(),
			   ajax: true},
		success: formDialogResponse
	});
}

function formDialogResponse(data)
{
	alert(data);
	if(data != ""){
		$("#linkPopupDialog").empty();
		$("#linkPopupDialog").html(data);
		$("#linkPopupDialog").dialog('open');
	}
}
</script>