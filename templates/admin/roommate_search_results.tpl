<table cellpadding="6" cellspacing="1" width="100%">
  <tr>
    <th align="left">First Roommate</th>
    <th align="left">Second Roommate</th>
    <th align="left">Third Roommate</th>
    <th align="left">Fourth Roommate</th>
    <th align="left">ACTIONS</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td align="left">{ROOMMATE_ZERO}</td>
    <td align="left">{ROOMMATE_ONE}</td>
    <td align="left">{ROOMMATE_TWO}</td>
    <td align="left">{ROOMMATE_THREE}</td>
    <td align="left">{ACTIONS}</td>
  </tr>
<!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
