<h1>{NAME} - {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}<br />

<ul>
  <li>Total beds in system: {TOTAL_BEDS}</li>
  <li>Unavailable: {UNAVAILABLE_BEDS}</li>
  <li>Available: {AVAILABLE_BEDS}</li>
</ul>

<table>
  <tr>
    <th>Hall</th>
    <th>Room #</th>
    <th>Bed</th>
    <th>Reserved</th>
    <th>RA</th>
    <th>RA Roommate</th>
    <th>Private</th>
    <th>Overflow</th>
    <th>Parlor</th>
    <th>Int'l Reserved</th>
    <th>Offline</th>
  </tr>
  <!-- BEGIN bed_rows -->
  <tr>
    <td>{HALL}</td>
    <td>{ROOM}</td>
    <td class="text-center">{BED_LETTER}</td>
    <td class="text-center">{RESERVED}</td>
    <td class="text-center">{RA}</td>
    <td class="text-center">{RA_ROOMMATE}</td>
    <td class="text-center">{PRIVATE}</td>
    <td class="text-center">{OVERFLOW}</td>
    <td class="text-center">{PARLOR}</td>
    <td class="text-center">{INTL}</td>
    <td class="text-center">{OFFLINE}</td>
  </tr>
  <!-- END bed_rows -->
  <tr style="border-top:3px solid black">
    <th><strong>Totals</strong></th>
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    <th>Reserved</th>
    <th>RA</th>
    <th>RA Roommate</th>
    <th>Private</th>
    <th>Overflow</th>
    <th>Parlor</th>
    <th>Int'l Reserved</th>
    <th>Offline</th>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class="text-center">{RESERVED_TOTAL}</td>
    <td class="text-center">{RA_TOTAL}</td>
    <td class="text-center">{RA_ROOMMATE_TOTAL}</td>
    <td class="text-center">{PRIVATE_TOTAL}</td>
    <td class="text-center">{OVERFLOW_TOTAL}</td>
    <td class="text-center">{PARLOR_TOTAL}</td>
    <td class="text-center">{INTL_TOTAL}</td>
    <td class="text-center">{OFFLINE_TOTAL}</td>
  </tr>
  
</table>