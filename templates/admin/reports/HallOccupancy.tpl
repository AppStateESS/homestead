<style>

table#ocp h2 {
    margin : 0;
    padding : 0;
}
</style>
<h1>{NAME} - {TERM}</h1>

<p>Executed on: {EXEC_DATE} by {EXEC_USER}</p>
<p>Total beds: {total_beds}</p>
<p>Vacant beds: {vacant_beds}</p>
<table id="ocp" cellpadding="4" style="border-collapse : collapse" width="100%" border="1">
<!-- BEGIN hall-rows -->
    <tr><th colspan="3"><h2>{hall_name} - Vacancies {hall_vacancies} / Total beds {hall_total_beds}</h2></th></tr>
    <tr><th>Floor</th><th>Vacancies</th><th>Total beds</th></tr>
    <!-- BEGIN floor-rows -->
    <tr><td>{floor_number}</td><td>{vacancies_by_floor}</td><td>{total_beds_by_floor}</td></tr>
    <!-- END floor-rows -->
<!-- END hall-rows -->
</table>
