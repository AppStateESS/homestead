<div class="hms">
  <div class="box">
    <div class="{TITLE_CLASS}"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <font color="red">{ERROR_MSG}<br /></font>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <font color="green">{SUCCESS_MSG}<br /></font>
        <!-- END success_msg -->
        {START_FORM}
        
        <table>
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
