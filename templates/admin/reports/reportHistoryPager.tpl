<div class="table-responsive">
	<table class="table table-striped table-hover">
	  <tr>
	    <th>Completion Date</th>
	    <th>HTML</th>
	    <th>PDF</th>
	    <th>CSV</th>
	  </tr>
	
	  <!-- BEGIN empty -->
	  <tr>
	    <td colspan="5">{EMPTY_MESSAGE}</td>
	  </tr>
	  <!-- END empty -->
	
	  <!-- BEGIN listrows -->
	  <tr{TOGGLE}>
	    <td>{COMPLETION_DATE}</td>
	    <td>
	      <!-- BEGIN html -->
	      <a href="{HTML}"><i class="fa fa-globe" title="View in web browser"></i></a>
	      <!-- END html -->
	    </td>
	
	    <td>
	      <!-- BEGIN pdf -->
	      <a href="{PDF}"><i class="fa fa-file-pdf-o" title="Open PDF"></i></a>
	      <!-- END pdf -->
	    </td>
	    <td>
	      <!-- BEGIN csv -->
	      <a href="{CSV}"><i class="fa fa-file-excel-o" title="download Excel file"></i></a>
	      <!-- END csv -->
	    </td>
	  </tr>
	  <!-- END listrows -->
	</table>
</div>