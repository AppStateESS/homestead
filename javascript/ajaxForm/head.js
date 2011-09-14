<script type="text/javascript" src="mod/hms/javascript/ajaxForm/jquery.ajaxForm.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$('{FORM_SELECT}').ajaxForm({
		'enableSelector' : '{ENABLE_SELECT}',
		'hiddenSelector' : '{HIDDEN_SELECT}',
		'submitSelector' : '{SUBMIT_SELECT}'
	});
});
</script>
