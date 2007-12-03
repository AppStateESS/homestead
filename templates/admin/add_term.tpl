<div class="hms">
  <div class="box">
    <div class="box-title"><h1>{TITLE}</h1></div>
    <div class="box-content">
 
      <!-- BEGIN error_msg -->
      <div><font color="red">{ERROR_MSG}</font></div>
      <!-- END error_msg -->

      <!-- BEGIN success_msg -->
      <div><font color="green">{SUCCESS_MSG}</font></div>
      <!-- END success_msg -->
      
      <!-- BEGIN term_form -->
      {START_FORM}
      <table>
        <tr>
          <td>{YEAR_DROP_LABEL}</td>
          <td>{YEAR_DROP}</td>
        </tr>
        <tr>
          <td>{TERM_DROP_LABEL}</td>
          <td>{TERM_DROP}</td>
        </tr>
        <tr>
          <td>{COPY_DROP_LABEL}</td>
          <td>{COPY_DROP}</td>
        </tr>
        <tr>
          <td colspan="2">{SUBMIT}</td>
        </tr>
      </table>
      {END_FORM}
      <!-- END term_form -->
    </div>
  </div>
</div>
