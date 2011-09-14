<table>
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
      <a href="{HTML}"><img src="mod/hms/img/rrze/22x22/emblems/webview.png" title="view in web browser"></a>
      <!-- END html -->
    </td>

    <td>
      <!-- BEGIN pdf -->
      <a href="{PDF}"><img src="mod/hms/img/rrze/22x22/mime-types/document-pdf.png" title="open PDF"></a>
      <!-- END pdf -->
    </td>
    <td>
      <!-- BEGIN csv -->
      <a href="{CSV}"><img src="mod/hms/img/rrze/22x22/emblems/office-excel.png" title="download Excel file"></a>
      <!-- BEGIN csv -->
    </td>
  </tr>
  <!-- END listrows -->
</table>