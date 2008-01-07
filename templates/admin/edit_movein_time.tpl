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
        <h2>Create Move-in Time</h2>
        {START_FORM}
        <table>
            <tr>
              <th colspan="2">Begin Date & Time</th>
              <th colspan="2">End Date & Time</th>
            </tr>
            <tr>
                <td>Month</td>
                <td>{BEGIN_MONTH}</td>
                <td>Month</td>
                <td>{END_MONTH}</td>
            </tr>
            <tr>
                <td>Day</td>
                <td>{BEGIN_DAY}</td>
                <td>Day</td>
                <td>{END_DAY}</td>
            </tr>
            <tr>
                <td>Year</td>
                <td>{BEGIN_YEAR}</td>
                <td>Year</td>
                <td>{END_YEAR}</td>
            </tr>
            <tr>
                <td>Hour</td>
                <td>{BEGIN_HOUR}</td>
                <td>Hour</td>
                <td>{END_HOUR}</td>
            </tr>
            <tr>
                <td colspan="2">{SUBMIT}</td>
            </tr>
        </table>
        {END_FORM}
        <br />
        <h2>Existing Move-in Times</h2>
        {MOVEIN_TIME_PAGER}
    </div>
  </div>
</div>
