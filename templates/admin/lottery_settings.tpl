<div class="hms">
  <div class="box">
    <div class="title"> <h1>Lottery Settings</h1> </div>
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
                <th>Lottery term: </th><td align="left">{LOTTERY_TERM}</td>
            </tr>
            <tr>
                <th>Percent sophomore: </th><td align="left">{LOTTERY_PER_SOPH}</td>
            </tr>
            <tr>
                <th>Percent junior: </th><td align="left">{LOTTERY_PER_JR}</td>
            </tr>
            <tr>
                <th>Percent senior: </th><td align="left">{LOTTERY_PER_SENIOR}</td>
            </tr>
        </table>
        <br />
        {SUBMIT}
        {END_FORM}
    </div>
  </div>
</div>
