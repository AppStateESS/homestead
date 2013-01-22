<script type="text/javascript" src="{source_http}mod/hms/javascript/appCardSwipe/CardReader.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	// Setup the card reader
	var cardReader = new CardReader();
	cardReader.observe(document);
	
	cardReader.cardError(function(){
		alert('An error occurred while reading the card. Please try again.');
	});
	
	cardReader.cardRead(function(value){
		//alert(value);
		var bannerParts = value.split("=");
		
		// TODO: make this a bit more abstracted out
		$('#student_search_form_banner_id').val(bannerParts[0]);
		$('#student_search_form').submit();
	});
});
</script>