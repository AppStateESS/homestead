<h2>{TITLE}</h2>

<!--<div class="float-right form-group">{SEARCH}></div>-->
<div class="row">
<div class="col-md-6">
<table class="table table-striped table-hover">
  <tr>
    <th>Participants</th>
    <th>Last Updated</th>
    <th></th>
  </tr>

  <tr>
      <td colspan="9">{EMPTY_MESSAGE}</td>
  </tr>

  <!-- BEGIN REQUESTS -->
  <tr>
    <td>{participants}</td>
    <td><abbr title="{last_updated_date}" data-livestamp="{last_updated_timestamp}"></abbr></td>
    <td><a class="btn btn-outline-dark" href="{manage}">manage</a></td>
  </tr>
  <!-- END REQUESTS -->
</table>
</div>
</div>
