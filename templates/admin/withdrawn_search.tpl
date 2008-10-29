<div class="hms">
  <div class="box">
    <div class="{TITLE_CLASS}"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->
        {START_FORM}
        
        <table cellspacing="2" cellpadding="2">
          <tr>
            <th>&nbsp;</th>
            <th>Name</th>
            <th>Banner ID</th>
            <th>Current Assignment</th>
          </tr>
          <!-- BEGIN withdrawn_students -->
          <tr>
            <td>{REMOVE_CHECKBOX}</td>
            <td>{NAME}</td>
            <td>{BANNER_ID}</td>
            <td>{ASSIGNMENT}</th>
          </tr>
          <!-- END withdrawn_students -->
        </table>
        <br />
        Found {COUNT} withdrawn students.<br /><br />
        {SUBMIT}
        {END_FORM}
    </div>
  </div>
</div>
