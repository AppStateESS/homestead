{MESSAGE}
<!-- BEGIN error_msg -->
<span class="error">{ERROR}</span>
<!-- END error_msg -->

<table>
    <tr>
        <th>Application Term</th>
        <th>Valid Application Term</th>
        <th>Required</th>
        <th>Action</th>
    </tr>
    <!-- BEGIN form -->
    <tr>
    {START_FORM}
        <td>{TERM1}</td>
        <td>{TERM2}</td>
        <td>{REQUIRED}</td>
        <td>{SUBMIT}</td>
    {END_FORM}
    </tr>
    <!-- END form -->
    <!-- BEGIN empty -->
    {EMPTY_MESSAGE}
    <!-- END empty -->
    <!-- BEGIN listrows -->
    <tr>
        <td>{APP_TERM}</td>
        <td>{TERM}</td>
        <td>{REQUIRED}</td>
        <td>{DELETE}</td>
    </tr>
    <!-- END listrows -->
</table>
<div align="center">
      <b>{PAGE_LABEL}</b><br />
      {PAGES}<br />
      {LIMITS}
    </div>

