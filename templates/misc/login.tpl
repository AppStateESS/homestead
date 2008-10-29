{START_FORM}
<div class="hms">
  <div class="box">
    <div class="box-title"> 
    <h1>LOGIN</h1><br />
    <!-- BEGIN error_msg -->
    <span class="error">{ERROR}</span><br/>
    <!-- END error_msg -->
  </div>
    <div class="box-content">
    {COOKIE_WARNING}
    {WELCOME}<br />
        <table>
            <tr>
                <th>Username: &nbsp;&nbsp;</th><td>{ASU_USERNAME}</td>
            </tr>
            <tr>
                <th>Password: </th><td>{PASSWORD}</td>
            </tr>
        </table>
        <br />
        {SUBMIT}
    </div>
  </div>
</div>
{END_FORM}
