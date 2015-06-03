<h2>Mismatched Roommates <small>{TERM}</small></h2>

<div class="col-md-12">
<p>Executed on: {EXEC_DATE} by {EXEC_USER}</p>

<p>There are <strong>{MISMATCH_COUNT}</strong> roommate pairs mismatched</p>

<table class="table table-striped table-hover">
    <tr>
        <th>Requestor's Name</th>
        <th>Requestor's Banner ID</th>
        <th>Requestor's Username</th>
        <th>Requestee's Name</th>
        <th>Requestee's Banner ID</th>
        <th>Requestee's Username</th>
    </tr>
<!-- BEGIN rows -->
    <tr>
        <td>{requestor_name}</td>
        <td>{requestor_banner}</td>
        <td>{requestor}</td>
        <td>{requestee_name}</td>
        <td>{requestee_banner}</td>
        <td>{requestee}</td>
    </tr>
<!-- END rows -->
</table>
</div>
