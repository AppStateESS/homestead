
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
        <table cellspacing="2" cellpadding="2">
          <tr>
            <th>User name</th>
            <th>Message</th>
          </tr>
          <!-- BEGIN status -->
          <tr>
            <td>{USERNAME}</td>
            <td>{MESSAGE}</td>
          </tr>
          <!-- END status -->
        </table>
        <br />
        <h2>Warnings:</h2>
        <table>
            <tr>
                <th>User name</th>
                <th>Message</th>
            </tr>
            <!-- BEGIN warnings -->
            <tr>
                <td>{USERNAME}</td>
                <td>{MESSAGE}</td>
            </tr>
            <!-- END warnings -->
        </table>
        <h2>Rooms with new vacancies:</h2>
        <ul>
            <!-- BEGIN rooms -->
            <li>{ROOM}</li>
            <!-- END rooms -->
        </ul>
    </div>
  </div>
</div>
