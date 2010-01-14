<h2>Email Residents</h2>

<!-- BEGIN notify_all -->
{START_FORM}
<table>
    <tr>
        <th colspan="2">Select the Halls to notify</th>
    </tr>
    <!-- BEGIN halls_list -->
    <tr>
        <td>{SELECT}</td>
        <td>{LABEL}</td>
    </tr>
    <!-- END halls_list -->
    <!-- BEGIN hall -->
    <tr>
        <td>
            {HALL_LABEL} {HALL}
        </td>
    </tr>
    <!-- END hall -->
</table>
<br />
{SUBMIT}
{END_FORM}
<!-- END notify_all -->
<!-- BEGIN notify_hall -->
{SELECT}
<!-- END notify_hall -->
