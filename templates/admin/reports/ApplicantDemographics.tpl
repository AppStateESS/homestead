<h1>{NAME} - {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}<br />

<table border="1" cellpadding="3">
    <tr>
        <td colspan="9" style="text-align: center">{TERM} - Housing Applications Received (by class and gender):</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <th>Freshmen (F)</th>
        <th>Transfer (T)</th>
        <th>Returning (R)</th>
        <th>Non-degree (NU)</th>
        <th>Sub-Totals</th>
        <th>Cancelled</th>
        <th>Totals</th>
    </tr>
    <tr>
        <td>Male</td>
        <!-- BEGIN male_totals -->
        <td>{COUNT}</td>
        <!-- END male_totals -->
        <td><strong>{MALE_SUB}</strong></td>
        <td>{MALE_CANCELLED}</td>
        <td>{MALE_TOTAL}</td>
    </tr>
    <tr>
        <td>Female</td>
        <!-- BEGIN female_totals -->
        <td>{COUNT}</td>
        <!-- END female_totals -->
        <td><strong>{FEMALE_SUB}</strong></td>
        <td>{FEMALE_CANCELLED}</td>
        <td>{FEMALE_TOTAL}</td>
    </tr>
    <tr>
        <td>Totals</td>
        <!-- BEGIN type_totals -->
        <td>{COUNT}</td>
        <!-- END type_totals -->
        <td><strong>{SUB_TOTAL}</strong></td>
        <td>{CANCELLED_SUB}</td>
        <td>{ALL_TOTAL}</td>
    </tr>
</table>
