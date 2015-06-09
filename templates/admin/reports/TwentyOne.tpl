<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-12">
  <div class="row">
    <p class="col-md-6">
      Executed on: {EXEC_DATE} by {EXEC_USER}
    </p>
  </div>

  <table class="table table-striped table-hover">
    <tr>
        <th>Residence Hall (max occ.)</th>
        <th>Current Occupancy</th>
        <th>Males</th>
        <th>Male %</th>
        <th>Females</th>
        <th>Female %</th>
    </tr>
    <!-- BEGIN rows -->
    <tr>
        <td>{hallName} ({maxOccupancy})</td>
        <td>{currOccupancy}</td>
        <td>{males}</td>
        <td>{malePercent}</td>
        <td>{females}</td>
        <td>{femalePercent}</td>
    </tr>
    <!-- END rows -->
    <tr>
        <td><strong>Total</strong></td>
        <td>{totalCurrOccupancy}</td>
        <td>{totalMales}</td>
        <td>{totalMalePercent}</td>
        <td>{totalFemales}</td>
        <td>{totalFemalePercent}</td>
    </tr>
  </table>
