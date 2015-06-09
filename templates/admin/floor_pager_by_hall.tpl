<table class="table table-striped table-hover">
    <tr>
        <th>Floor</th>
        <th>Gender</th>
        <th>Online</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="3">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{FLOOR_NUMBER}</td>
        <td>{GENDER_TYPE}</td>
        <td>{IS_ONLINE}</td>
    </tr>
    <!-- END listrows -->
</table>
