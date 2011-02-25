<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>{TITLE}</h1> </div>

    <div class="box-content">
      <!-- BEGIN options -->
      <ul>
        <li>{PRINT_RECORDS}</li>
        <li>{EXPORT}</li>
      </ul>
      <!-- END options -->

      <table cellpadding="4" cellspacing="1" width="99%">
        <tr>
          <th>Name {USER_ID_SORT}</th>
          <th>Final RLC {RLC_ID_SORT}</th>
          <th>Roommate</th>
          <th>Address</th>
          <th>Phone/Email</th>
        </tr>
        <!-- BEGIN empty_table -->
        <tr>
          <td>{EMPTY_MESSAGE}</td>
        </tr>
        <!-- END empty_table -->
        <!-- BEGIN listrows -->
        <tr {TOGGLE}>
          <td>{NAME}</td>
          <td>{FINAL_RLC}</td>
          <td>{ROOMMATE}</td>
          <td>{ADDRESS}</td>
          <td>{PHONE}<br />{EMAIL}</td>
        </tr>
        <!-- END listrows -->
      </table>
      <div class="align-center">
        {TOTAL_ROWS}<br />
        {PAGE_LABEL} {PAGES}<br />
        {LIMIT_LABEL} {LIMITS}
      </div>
      <div>{CSV_REPORT}</div>
    </div>
  </div>
</div>
