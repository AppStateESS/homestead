<h2> Early Release <small>{TERM}</small></h2>

<div class="col-md-10">
  <div class="row">
    <p class="col-md-6">Executed on: {EXEC_DATE} by {EXEC_USER}</p>
  </div>

  <!-- BEGIN empty_message -->
  <div class="row">
    <p class="col-md-6">{EMPTY_MESSAGE}</p>
  </div>
  <!-- END empty_message -->

  <!-- BEGIN grad_total -->
  <div class="row">
    <label class="col-md-4">Graduating in December:</label>
    <strong class="col-md-1"> {GRAD_TOTAL}</strong>
  </div>
  <!-- END grad_total -->

  <!-- BEGIN intern_total -->
  <div class="row">
    <label class="col-md-4">Internship:</label>
    <strong class="col-md-1">{INTERN_TOTAL}</strong>
  </div>
  <!-- END intern_total -->

  <!-- BEGIN international_total -->
  <div class="row">
    <label class="col-md-4">International Exchange Ending:</label>
    <strong class="col-md-1">{INTL_TOTAL}</strong>
  </div>
  <!-- END international_total -->

  <!-- BEGIN marriage_total -->
  <div class="row">
    <label class="col-md-4">Getting Married:</label>
    <strong class="col-md-1">{MARRIAGE_TOTAL}</strong>
  </div>
  <!-- END marriage_total -->

  <!-- BEGIN teaching_total -->
  <div class="row">
    <label class="col-md-4">Student Teaching: </label>
    <strong class="col-md-1">{TEACHING_TOTAL}</strong>
  </div>
  <!-- END teaching_total -->

  <!-- BEGIN abroad_total -->
  <div class="row">
    <label class="col-md-4">Studying Abroad:</label>
    <strong class="col-md-1">{ABROAD_TOTAL}</strong>
  </div>
  <!-- END abroad_total -->

  <!-- BEGIN transfer_total -->
  <div class="row">
    <label class="col-md-4">Transfers:</label>
    <strong class="col-md-1"> {TRANSFERS_TOTAL}</strong>
  </div>
  <!-- END transfer_total -->

  <!-- BEGIN withdrawal_total -->
  <div class="row">
    <label class="col-md-4">Withdrawals:</label>
    <strong class="col-md-1">{WITHDRAW_TOTAL}</strong>
  </div>
  <!-- END withdrawal_total -->

  <!-- BEGIN total -->
  <div class="row">
    <label class="col-md-4">Total:</label>
    <strong class="col-md-1">{TOTAL}</strong>
  </div>

  <div class="row">
    <table class="table table-striped table-hover">
        <tr>
            <th>Name</th>
            <th>Banner ID</th>
            <th>Username</th>
            <th>Reason</th>
        </tr>
      <!-- BEGIN rows -->
        <tr>
            <td>{name}</td>
            <td>{banner_id}</td>
            <td>{username}</td>
            <td>{early_release}</td>
        </tr>
      <!-- END rows -->
    </table>
  </div>
  <!-- END total -->

</div>
