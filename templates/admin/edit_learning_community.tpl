<script type='text/javascript'>
function getVals(rlc_id){
    $.getJSON('index.php', {'type': 'rlc', 'op': 'get_json', 'id': rlc_id}, function(data){
        $('#edit_rlc_name').val(data[0]['community_name']);
        $('#edit_rlc_abbv').val(data[0]['abbreviation']);
        $('#edit_rlc_capacity').val(data[0]['capacity']);
    });
}

$(document).ready(function(){
    getVals($('option')[0].value);
});
</script>
<h2>Edit Learning Communities</h2>
<div class='error'>{MESSAGE}</div>
{START_FORM}
<table>
    <tr>
        <th>{RLC_LABEL}</th>
        <td>{RLC}</td>
    </tr>
    <tr>
        <th>{NAME_LABEL}</th>
        <td>{NAME}</td>
    </tr>
    <tr>
        <th>{ABBV_LABEL}</th>
        <td>{ABBV}</td>
    </tr>
    <tr>
        <th>{CAPACITY_LABEL}</th>
        <td>{CAPACITY}</td>
    </tr>
    <tr>
        <td></td>
        <td align='right'>{SAVE_CHANGES}</td>
    </tr>
</table>
{END_FORM}
