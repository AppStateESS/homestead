<h1>{NAME} - {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}<br />

<table border="1" cellpadding="3">
    <tr>
        <td colspan="9" style="text-align: center">{TERM} - Housing Applications Received (by class and gender):</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <th>Freshmen</th>
        <th>Transfer</th>
        <th>Continuing</th>
        <th>Re-admit</th>
        <th>Returning</th>
        <th>Non-degree</th>
        <th>Withdrawn</th>
        <th>Totals</th>
    </tr>
    <tr>
        <td>Male</td>
        <!-- BEGIN male_totals -->
        <td>{COUNT}</td>
        <!-- END male_totals -->
        <td>{MALE_SUM}</td>
    </tr>
    <tr>
        <td>Female</td>
        <!-- BEGIN female_totals -->
        <td>{COUNT}</td>
        <!-- END female_totals -->
        <td>{FEMALE_SUM}</td>
    </tr>
    <tr>
        <td>Totals</td>
        <!-- BEGIN type_totals -->
        <td>{COUNT}</td>
        <!-- END type_totals -->
        <td>{ALL_TOTAL}</td>
    </tr>
</table>
