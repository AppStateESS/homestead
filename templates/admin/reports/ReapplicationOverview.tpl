<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-9">
  <div class="row">
    <p class="col-md-6">Executed on: {EXEC_DATE} by {EXEC_USER}</p>
  </div>

  <table class="table table-striped table-hover">
    <tr>
      <h3>Re-applications</h3>
    </tr>
    <tr>
      <th></th>
      <th>Sophomores</th>
      <th>Juniors</th>
      <th>Seniors</th>
      <th>Overrall</th>
    </tr>
    <tr>
      <th>Male</th>
      <td>{SOPH_M_APPS}</td>
      <td>{JR_M_APPS}</td>
      <td>{SR_M_APPS}</td>
      <td>{M_APPS}</td>
    </tr>
    <tr>
      <th>Female</th>
      <td>{SOPH_F_APPS}</td>
      <td>{JR_F_APPS}</td>
      <td>{SR_F_APPS}</td>
      <td>{F_APPS}</td>
    </tr>
    <tr>
      <th>Total</th>
      <td>{SOPH_APPS}</td>
      <td>{JR_APPS}</td>
      <td>{SR_APPS}</td>
      <td>{LOTTERY_APPS}</td>
    </tr>
  </table>

  <table class="table table-striped table-hover">
    <tr>
      <h3>Unaffiliated Re-applications</h3>
    </tr>
    <tr>
      <td></td>
      <th>Sophomores</th>
      <th>Juniors</th>
      <th>Seniors</th>
      <th>Overrall</th>
    </tr>
    <tr>
      <th>Male</th>
      <td>{NET_SO_M_APPS}</td>
      <td>{NET_JR_M_APPS}</td>
      <td>{NET_SR_M_APPS}</td>
      <td>{NET_M_APPS}</td>
    </tr>
    <tr>
      <th>Female</th>
      <td>{NET_SO_F_APPS}</td>
      <td>{NET_JR_F_APPS}</td>
      <td>{NET_SR_F_APPS}</td>
      <td>{NET_F_APPS}</td>
    </tr>
    <tr>
      <th>Total</th>
      <td>{NET_SO_APPS}</td>
      <td>{NET_JR_APPS}</td>
      <td>{NET_SR_APPS}</td>
      <td>{NET_APPS}</td>
    </tr>
  </table>
</div>

<div class="col-md-7">
  <table class="table table-striped table-hover">
    <tr>
      <h3>Invites</h3>
    </tr>
    <tr>
      <th></th>
      <th>Sophomores</th>
      <th>Juniors</th>
      <th>Seniors</th>
    </tr>
    <tr>
      <th>Male</th>
      <td>{SO_M_INVITES}</td>
      <td>{JR_M_INVITES}</td>
      <td>{SR_M_INVITES}</td>
    </tr>
    <tr>
      <th>Female</th>
      <td>{SO_F_INVITES}</td>
      <td>{JR_F_INVITES}</td>
      <td>{SR_F_INVITES}</td>
    </tr>
    <tr>
      <th>Total</th>
      <td>{SO_INVITES}</td>
      <td>{JR_INVITES}</td>
      <td>{SR_INVITES}</td>
    </tr>
  </table>
</div>

<div class="col-md-9">
  <h3>Outstanding Invites</h3>
  <table class="table table-striped table-hover">
    <tr>
      <th></th>
      <th>Sophomores</th>
      <th>Juniors</th>
      <th>Seniors</th>
      <th>Roommate Invites</th>
      <th>Total</th>
    </tr>
    <tr>
      <th>Invites</th>
      <td>{PENDING_SOPH_INVITES}</td>
      <td>{PENDING_JR_INVITES}</td>
      <td>{PENDING_SR_INVITES}</td>
      <td>{PENDING_ROOMMATE_INVITES}</td>
      <td>{TOTAL_PENDING_INVITES}</td>
    </tr>
  </table>
</div>

<div class="col-md-7">
  <table class="table table-striped table-hover">
    <tr>
      <h3>Re-assignments</h3>
      <p>Total Reapplications assigned: {LOTTERY_ASSIGNED}</p>
    </tr>
    <tr>
      <th></th>
      <th>Sophomores</th>
      <th>Juniors</th>
      <th>Seniors</th>
    </tr>
    <tr>
      <th>Male</th>
      <td>{SOPH_MALE_ASSIGNED}</td>
      <td>{JR_MALE_ASSIGNED}</td>
      <td>{SR_MALE_ASSIGNED}</td>
    </tr>
    <tr>
      <th>Female</th>
      <td>{SOPH_FEMALE_ASSIGNED}</td>
      <td>{JR_FEMALE_ASSIGNED}</td>
      <td>{SR_FEMALE_ASSIGNED}</td>
    </tr>
    <tr>
      <th>Total</th>
      <td>{SOPH_ASSIGNED}</td>
      <td>{JR_ASSIGNED}</td>
      <td>{SR_ASSIGNED}</td>
    </tr>
  </table>

  <table class="table table-striped table hover">
    <tr>
      <h3>Remaining Unaffiliated Re-applications</h3>
    </tr>
    <tr>
      <th></th>
      <th>Sophomores</th>
      <th>Juniors</th>
      <th>Seniors</th>
    </tr>
    <tr>
      <th>Male</th>
      <td>{SO_M_ENTRIES_REMAIN}</td>
      <td>{JR_M_ENTRIES_REMAIN}</td>
      <td>{SR_M_ENTRIES_REMAIN}</td>
    </tr>
    <tr>
      <th>Female</th>
      <td>{SO_F_ENTRIES_REMAIN}</td>
      <td>{JR_F_ENTRIES_REMAIN}</td>
      <td>{SR_F_ENTRIES_REMAIN}</td>
    </tr>
    <tr>
      <th>Total</th>
      <td>{SO_ENTRIES_REMAIN}</td>
      <td>{JR_ENTRIES_REMAIN}</td>
      <td>{SR_ENTRIES_REMAIN}</td>
    </tr>
  </table>

</div>
