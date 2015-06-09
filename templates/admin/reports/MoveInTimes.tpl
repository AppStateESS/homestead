<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-12">
  <div class="row">
    <p class="col-md-6">
      Executed on: {EXEC_DATE} by {EXEC_USER}
    </p>
  </div>

  <!-- BEGIN hall-rows -->
    <table class="table table-striped table-hover">
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
    </table>
  <!-- END hall-rows -->
</div>
