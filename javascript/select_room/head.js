<script type="text/javascript" src="mod/hms/javascript/jquery.selectboxes.js"></script>


<script type="text/javascript">
$(document).ready(function() {
		// Set event listener on "add another role" link
		$('#phpws_form_submit_button').attr('disabled', true);
		$('#phpws_form_floor').attr('disabled', true);
		$('#phpws_form_room').attr('disabled', true);
		
		$("#phpws_form_residence_hall").bind("change", handleHallChange);
		$("#phpws_form_floor").bind("change", handleFloorChange);
		$("#phpws_form_room").bind("change", handleRoomChange);
	});
</script>

<script type="text/javascript">
// <![CDATA[

var res_hall_drop   = '#phpws_form_residence_hall';
var floor_drop      = '#phpws_form_floor';
var room_drop		= '#phpws_form_room';

function handleHallChange()
{
  // Reset and disable all the lower-order drop downs
  setOptions(floor_drop, {});
  setOptions(room_drop, {});
  $(floor_drop).attr('disabled', true);
  $(room_drop).attr('disabled', true);

  // Get the selected value
  var hallId = $('#phpws_form_residence_hall').val();
  
  // the default value is selected
  if(hallId == 0){
      // alert('default selected');
      return;
  }
  
  // Set the floor drop down to "loading", show the loading animation
  setOptions(floor_drop, {'0': 'Loading...'});
  $("#loading_img").show();

	var request = $.ajax( {
	type : "GET",
	url : "index.php",
	dataType : "json",
	data : {
		module : "hms",
		ajax : true,
		action : "AjaxGetFloors",
		hallId : hallId
	},
	success : function(data, textStatus) {
		handleFloorResponse(data, textStatus);
	},
	error : function(XMLHttpRequest, textStatus, errorThrown) {
		ajaxError(XMLHttpRequest, textStatus, errorThrown);
	}
});
}

function handleFloorResponse(data, textStatus){
	$("#loading_img").hide();
	$(floor_drop).attr('disabled', false);
	setOptions(floor_drop, data);
}

function handleFloorChange(){
	var floorId = $("#phpws_form_floor").val();
	
	$('#phpws_form_submit_button').attr('disabled', true);
	$(room_drop).attr('disabled', true);
	setOptions(room_drop,{});
	
	if(floorId == 0){
		return;
	}
	
	// Set the floor drop down to "loading", show the loading animation
	setOptions(room_drop, {'0': 'Loading...'});
	$("#loading_img").show();

	var request = $.ajax( {
		type : "GET",
		url : "index.php",
		dataType : "json",
		data : {
			module : "hms",
			ajax : true,
			action : "AjaxGetRooms",
			floorId : floorId
		},
		success : function(data, textStatus) {
			handleRoomResponse(data, textStatus);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			ajaxError(XMLHttpRequest, textStatus, errorThrown);
		}
	});
}

function handleRoomResponse(data, textStatus)
{
	$("#loading_img").hide();
	$(room_drop).attr('disabled', false);
	setOptions(room_drop, data);
}

function handleRoomChange()
{
	var roomId = $('#phpws_form_room').val();
	
	if(roomId == 0){
		$('#phpws_form_submit_button').attr('disabled', true);
	}else{
		$('#phpws_form_submit_button').attr('disabled', false);
	}
}

function setOptions(elementId, options)
{
	 $(elementId).empty();
	 $(elementId).addOption(options);
	 $(elementId).selectOptions("", true);
}

function ajaxError(XMLHttpRequest, textStatus, errorThrown)
{
	alert('Ajax error: ' + textStatus);
}

// ]]>
</script>