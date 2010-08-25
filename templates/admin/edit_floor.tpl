<script type="text/javascript">
function edit_toggle(){
    $("#static_pager").toggle();
    $("#dynamic_pager").toggle();
    switch($("#edit").text()){
        case 'Edit':
            $("#edit").text('View');
            break;
        case 'View':
            $("#edit").text('Edit');
            break;
        default:
            break;
    }
}

function submit_form(form_element, dropdown){
    var table_row = $(form_element).parent().parent().get(0);
    var form_element_value = 0;
    if(dropdown){
        form_element_value = $(form_element).val();
    } else {
        form_element_value = form_element.checked ? 1 : 0;
    }
    
    form_element.disabled=true;

    $.getJSON('index.php', {'module': 'hms', 'action': 'UpdateRoomField', 'id': table_row.id, 'field': form_element.name, 'value': form_element_value}, function(json){
        if(json.value != false){
            var display_text;
            switch(form_element_value){
                case "0":
                    display_text = 'Female';
                    break;
                case "1":
                    display_text = 'Male';
                    break;
                case "2":
                    display_text = 'COED';
                    break;
                case 0:
                    display_text = 'No';
                    break;
                case 1:
                    display_text = 'Yes';
                    break;
                default:
                    display_text = 'Unkown field value';
                    break;
            }

            var tablecell = $("#"+json.id+""+form_element.name).get(0);
            $(tablecell).text(""+display_text);
        } else {
            alert('Error updating the database, no changes were made.\n'+json.message);
        }
        form_element.disabled=false;
    });
}

$(document).ready(function(){
    $("#dynamic_pager").hide();
});
</script>
<div class="hms">
  <div class="box">
    <div class="{TITLE_CLASS}"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <h2>Floor Properties</h2>
        {START_FORM}
        <table>
            <tr>
                <th>Hall Name:</th><td align="left">{HALL_NAME}</td>
            </tr>
            <tr>
                <th>Floor: </th><td align="left">{FLOOR_NUMBER}</td>
            </tr>
            <tr>
                <th>Number of rooms: &nbsp;&nbsp;</th><td align="left">{NUMBER_OF_ROOMS}</td>
            </tr>
            <tr>
                <th>Number of beds: </th><td>{NUMBER_OF_BEDS}</td>
            </tr>
            <tr>
                <th>Number of occupants: </th><td>{NUMBER_OF_ASSIGNEES}</td>
            </tr>
            <tr>
                <th>Gender type: </th>
                <!-- BEGIN gender_radio_buttons -->
                <td align="left">{GENDER_TYPE}</td>
                <!-- END gender_radio_button -->
            </tr>
            <tr>
                <th>Is online: </th>
                <td align="left">{IS_ONLINE}</td>
            </tr>
            <tr>
                <th>Freshmen Move-in Time: </th><td>{F_MOVEIN_TIME}</td>
            </tr>
            <tr>
                <th>Transfer Move-in Time: </th><td>{T_MOVEIN_TIME}</td>
            </tr>
            <tr>
                <th>Returning Move-in Time: </th><td>{RT_MOVEIN_TIME}</td>
            </tr>
            <tr>
                <th>Reserved for RLC: </th><td>{FLOOR_RLC_ID}</td>
            </tr>
            <tr>
                <th>Floor plan:</th><td>{FILE_MANAGER}</td>
            </tr>
        </table>
        <br />
        {SUBMIT_FORM}
        {END_FORM}
        <br /><br />
        <a id="edit" onclick="edit_toggle()">Edit</a>
        <div id="static_pager">
        {STATIC_ROOM_PAGER}
        </div>
        <div id="dynamic_pager">
        {DYNAMIC_ROOM_PAGER}
        </div>
        <div>
        {ADD_ROOM}
        </div>
        <div id="roles">
        {ROLE_EDITOR}
        </div>
    </div>
  </div>
</div>
