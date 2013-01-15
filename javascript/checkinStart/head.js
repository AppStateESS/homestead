<script type="text/javascript" src="{source_http}mod/hms/javascript/checkinStart/checkinStart.js"></script>
<script type="text/javascript" src="{source_http}mod/hms/javascript/checkinStart/CardReader.js"></script>
<script type="text/javascript">

	$().ready(function(){
		
		//console.log($.cookie("hms-checkin-hall-id"));
		
		// Check if a hall has been saved in a cookie
		var hallId = $.cookie("hms-checkin-hall-id");
		var hallName = $.cookie("hms-checkin-hall-name");
		if(hallId !== null){
			// Use the hall from the cookie
			selectHall(hallId, hallName);
		}else{
			// Setup the hall selector
			
			// Hide the hall name div
			$('#hallDiv').hide();
			
			// Hide the search box until a hall is selected
			$('#searchBoxDiv').hide();
			
			// Show the hall selector
			//$('#hallSelector').show();
			
			// Set onChange event handler for drop down box
			$('#checkin_form_residence_hall').bind('change', handleSelectHall);
		}
		
		// Setup the card reader
		var cardReader = new CardReader();
		cardReader.observe(document);
		
		cardReader.cardError(function(){
			alert('An error occurred while reading the card. Please try again.');
		});
		
		cardReader.cardRead(function(value){
			//alert(value);
			var bannerParts = value.split("=");
			$('#checkin_form_banner_id').val(bannerParts[0]);
			$('#checkin_form').submit();
		});
		
	});
	
	function handleSelectHall()
	{
		// Hide the drop down
		$('#hallSelector').hide('fast');
		
		// Get the select hall name and ID
		var hallId = $('#checkin_form_residence_hall').val();
		var hallName = $('#checkin_form_residence_hall option:selected').text();

		// Save the selection to a cookie
		$.cookie("hms-checkin-hall-id", hallId, 180);
		$.cookie("hms-checkin-hall-name", hallName, 180);
		
		console.log($.cookie("hms-checkin-hall-id"));
		
		selectHall(hallId, hallName);
	}
	
	function selectHall(hallId, hallName)
	{
		// Hide the drop down
		$('#hallSelector').hide();
		
		// Set the name of the hall in the title
		$('#hallName').html(' ' + hallName);
		
		// Save the hall id to a hidden field
		$('#checkin_form_residence_hall_hidden').val(hallId);
		
		// Show the hall name div and change link
		$('#hallDiv').show();
		
		// Show the search box
		$('#searchBoxDiv').show();
		
		// Set the event handler for the 'change hall' link
		$('#changeLink').bind('click', changeHallLink);
		
		// Set focus to the banner id text box
		$('#checkin_form_banner_id').focus();
	}
	
	function changeHallLink()
	{
		// Hide the hall name div
		$('#hallDiv').hide();
		
		// Hide the search box until a hall is selected
		$('#searchBoxDiv').hide();
		
		// Show the hall selector
		$('#hallSelector').show('fast');
		
		// Set the dropdown box to have the default ("Select a hall..") option selected to avoid onChange bugs
		$("#hallSelector option[value='0']").attr("selected", "selected");
		
		// Set onChange event handler for drop down box
		$('#checkin_form_residence_hall').bind('change', handleSelectHall);
		
		return false;
	}
</script>