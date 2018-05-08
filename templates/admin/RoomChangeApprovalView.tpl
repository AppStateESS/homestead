<h1>Room Change Management <small>{TERM}</small></h1>

<p>Showing room change requests for floors in: {HALL_NAMES}</p>

<div>

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#approval" aria-controls="approval" role="tab" data-toggle="tab">Needs Approval <span class="badge badge-danger">{NEEDS_ACTION_COUNT}</span></a></li>
    <li role="presentation"><a href="#in-progress" aria-controls="in-progress" role="tab" data-toggle="tab">In Progress <span class="badge">{APPROVED_COUNT}</span></a></li>
    <li role="presentation"><a href="#pending" aria-controls="pending" role="tab" data-toggle="tab">Pending <span class="badge">{PENDING_COUNT}</span></a></li>
    <li role="presentation"><a href="#completed" aria-controls="completed" role="tab" data-toggle="tab">Completed <span class="badge">{COMPLETED_COUNT}</span></a></li>
    <li role="presentation"><a href="#cancelled" aria-controls="cancelled" role="tab" data-toggle="tab">Cancelled <span class="badge">{INACTIVE_COUNT}</span></a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="approval">
        <h3>Waiting for your Approval</h3>
        {NEEDS_ACTION}
    </div>
    <div role="tabpanel" class="tab-pane" id="in-progress">
        <h3>Approved - In Progress</h3>
        {APPROVED}
    </div>
    <div role="tabpanel" class="tab-pane" id="pending">
        <h3>Waiting for Approval</h3>
        {PENDING}
    </div>
    <div role="tabpanel" class="tab-pane" id="completed">
        <h3>Completed</h3>
        {COMPLETED}
    </div>
    <div role="tabpanel" class="tab-pane" id="cancelled">
        <h3>Cancelled &amp; Denied</h3>
        {INACTIVE}
    </div>
  </div>

</div>
