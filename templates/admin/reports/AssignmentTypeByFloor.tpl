<h1>{NAME} - {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}
<br />
<br />

<!-- BEGIN hall_repeat -->
<h2 style="margin-top: 30px;">{HALL_NAME}</h2>
<span style="">Total Beds: {HALL_OCCUPANCY}/{HALL_CAPACITY}</span>
<div style="margin-left: 15px; margin-bottom:20px;">
  <table>
    <!-- BEGIN hall_totals -->
    <tr style="margin-left: 20px;">
      <td>{HALL_TOTAL_TYPE}:</td>
      <td>{HALL_TOTAL_COUNT}</td>
    </tr>
    <!-- END hall_totals -->
  </table>
</div>

<div>
  <table>
    <!-- BEGIN floors -->
    <tr>
      <th style="width: 200px" colspan="2">{FLOOR_NUMBER} Floor</th>
    </tr>

    <!-- BEGIN floor_counts -->
    <tr>
      <td>{TYPE}</td>
      <td>{COUNT}</td>
    </tr>
    <!-- END floor_counts -->

    <!-- END floors -->
  </table>
</div>
<!-- END hall_repeat -->