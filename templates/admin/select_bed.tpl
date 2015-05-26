<h2>{TITLE}</h2>
<!-- BEGIN error_msg -->
<div class="alert alert-danger">{ERROR_MSG}</div>
<!-- END error_msg -->

<!-- BEGIN success_msg -->
<div class="alert alert-success">{SUCCESS_MSG}</div>
<!-- END success_msg -->

<p>{MESSAGE}</p>
{START_FORM}
<table class="table">
    <tr>
        <th>{RESIDENCE_HALL_LABEL}</th>
        <td>{RESIDENCE_HALL}</td>
    </tr>
    <tr>
        <th>{FLOOR_LABEL}</th>
        <td>{FLOOR}</td>
    </tr>
    <tr>
        <th>{ROOM_LABEL}</th>
        <td>{ROOM}</td>
    </tr>
    <tr>
        <th>{BED_LABEL}</th>
        <td>{BED}</td>
    </tr>
</table>
<br />
{SUBMIT_BUTTON}
{END_FORM}
