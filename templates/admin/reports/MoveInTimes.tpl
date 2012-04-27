<style>
table#movein h2 {
    margin : 0;
    padding : 0;
}
</style>
<h1>{NAME} - {TERM}</h1>

<p>Executed on: {EXEC_DATE} by {EXEC_USER}</p>
<table id="movein" cellpadding="4" width="100%">
<!-- BEGIN hall-rows -->
    <tr>
        <th colspan="4"><h2>{HALL_NAME}</h2></th>
    </tr>
    <tr>
        <th>Floor</th><th>Freshman</th><th>Transfer</th><th>Returning</th>
    </tr>
    <!-- BEGIN floor-rows -->
    <tr>
        <td>{FLOOR_NUM}</td><td>{F_TIME}</td><td>{T_TIME}</td><td>{RT_TIME}</td>
    </tr>
    <!-- END floor-rows -->
<!-- END hall-rows -->
</table>