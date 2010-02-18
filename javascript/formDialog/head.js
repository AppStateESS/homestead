<script type="text/javascript">
$(document).ready(function() {
	var elements = new Array({ELEMENTS_TO_BIND});
	
	for (var i in elements){
		$(elements[i]).bind('change', formDialogShow);
	}
});

function formDialogShow()
{
	$.ajax({
		url: "index.php",
		data: {module: 'hms',
			   action: '{ACTION}',
			   id: $(this).val(),
			   ajax: true},
		dataType: "json",
		success: formDialogResponse
	});
}

function formDialogResponse(data)
{
	if(data != ""){
		$("#formDialog").empty();
		$("#formDialog").dialog({title: data.title, autoOpen: false, modal: true, width: 425, resizable: false, position: 'top'});
		$("#formDialog").html(data.content);
		
		$("#formDialogContinue").bind('click', function() {
			$("#formDialog").dialog('close');
		});
		
		$("#formDialog").dialog('open');
	}
}
</script>