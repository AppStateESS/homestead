<script type="text/javascript" src="javascript/modules/hms/linkPopup/jquery.linkPopup.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#linkPopupDialog").dialog({autoOpen: false, modal: true, width: 425, resizable: false, position: 'top'});
	$('{LINK_SELECT}').linkPopup();
});
</script>