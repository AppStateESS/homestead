<script type="text/javascript" src="{source_http}mod/hms/javascript/checkinStart/checkinStart.js"></script>
<script type="text/javascript" src="{source_http}mod/hms/javascript/checkinStart/CardReader.js"></script>
<script type="text/javascript">

	$().ready(function(){
		
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
		
		cardReader.validate(function (value) {
			// Tests if value is not equal to 'E'.
			if(value == 'E'){
				return false;
			}
			
			// Tests if value is not equal to 'E+E'.
			if(value == 'E+E'){
				return false;
			}
			
			if(value == '+E?'){
				return false;
			}

			if(value.indexOf('E') >= 0 ){
				return false;
			}

			return true;
		});
		
		// Event handler for card read errors
		cardReader.cardError(function(){
			$("#cardswipe-error").show();
			$("#cardswipe-error").delay(2500).fadeOut('fast');
		});
		
		cardReader.cardRead(function(value){
			console.log(value);
			var bannerParts = value.split("=");
			$('#checkin_form_banner_id').val(bannerParts[0]);
			$('#checkin_form').submit();
		});
		
		
		// Setup autocomplete on the search input
		$("#checkin_form_banner_id").autocomplete({
			source: "index.php?module=hms&action=AjaxGetUsernameSuggestions&ajax=1",
			delay: 500,
			minLength: 3,
			focus: function( event, ui ) {
				event.preventDefault();  // NB: Makes moving the focus with the keyboard work
				$("#checkin_form_banner_id").val(ui.item.banner_id);
			},
			select: function( event, ui ) {
				$("#checkin_form_banner_id").val(ui.item.banner_id);
				return false; // NB: Must return false or default behavior will clear the search field
			}
		}).data("autocomplete")._renderItem = function (ul, item) {
			//
			var regExp = new RegExp("^" + this.term);
			var nameHighlight = item.name.replace(regExp, "<span style='font-weight:bold;'>" + this.term + "</span>");
			var userHighlight = item.username.replace(regExp, "<span style='font-weight:bold;'>" + this.term + "</span>");
			var bannerHighlight = item.banner_id.replace(regExp, "<span style='font-weight:bold;'>" + this.term + "</span>");
			// Custom HTML for the drop-down menu
			return $("<li>").data("item.autocomplete", item).append( "<a><span style='font-size:16px'>" + nameHighlight + "</span><br>" + bannerHighlight + " &bull; " + userHighlight + "</a>" ).appendTo(ul);
		};
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