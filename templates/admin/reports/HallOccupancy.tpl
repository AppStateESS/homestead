<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-12">
  <div class="row">
    <p class="col-md-6">
      Executed on: {EXEC_DATE} by {EXEC_USER}
    </p>
  </div>

  <div class="row">
    <label class="col-md-3">
      Total beds:
    </label>
    <label class="col-md-3 col-md-offset-1">
      {total_beds}
    </label>
  </div>

  <div class="row">
    <label class="col-md-3">
      Vacant beds:
    </label>
    <label class="col-md-3 col-md-offset-1">
      {vacant_beds}
    </label>

  <div class="col-md-10">
  <!-- BEGIN hall-rows -->
    <div class="row">
      <table class="table table-striped table hover">
        <tr>
          <th colspan="3">
            <h2>
              {hall_name}
            </h2>
          </th>
        </tr>
        <tr>
          <th>
            Floor
          </th>
          <th>
            Vacancies
          </th>
          <th>
            Total beds
            </th>
        </tr>
        <!-- BEGIN floor-rows -->
          <tr>
            <td>
              {floor_number}
            </td>
            <td>
              {vacancies_by_floor}
            </td>
            <td>
              {total_beds_by_floor}
            </td>
          </tr>
        <!-- END floor-rows -->
        <tr>
          <td>
            <strong>
              Total
            </strong>
          </td>
          <td>
            <strong>
              {hall_vacancies}
            </strong>
          </td>
          <td>
            <strong>
              {hall_total_beds}
            </strong>
          </td>
        </tr>
      </table>
    </div>
  <!-- END hall-rows -->
  </div>
</div>
