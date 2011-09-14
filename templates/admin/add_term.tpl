<div class="hms">
  <div class="box">
    <div class="box-title"><h1>{TITLE}</h1></div>
    <div class="box-content">
      <!-- BEGIN error_msg -->
      <div><span class="error">{ERROR_MSG}</span></div>
      <!-- END error_msg -->

      <!-- BEGIN success_msg -->
      <div><span class="success">{SUCCESS_MSG}</span></div>
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
          <td>{COPY_PICK_LABEL}</td>
          <td>
            <span class="copy-pick">{COPY_PICK_1}{COPY_PICK_1_LABEL}<br/></span>
            <span class="copy-pick-sub">{COPY_PICK_2}{COPY_PICK_2_LABEL}<br/></span>
            <span class="copy-pick-sub">{COPY_PICK_3}{COPY_PICK_3_LABEL}<br/></span>
          </td>
        </tr>
        <tr>
            <td>{FROM_TERM_LABEL}</td>
            <td>{FROM_TERM}</td>
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
