<h1>{NAME} - {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}
<br />

<!-- BEGIN summaries -->
<table border="1">
  <tr>
    <th colspan="12">
      <h2 style="text-align: center">{HALL_NAME}</h2></th>
  </tr>
  <tr>
    <td rowspan="2"></td>
    <th colspan="1">Freshmen (F)</th>
    <th colspan="4">Transfer (T)</th>
    <th colspan="4">Continuing (C)</th>
    <th rowspan="2">Other (O)</th>
    <th rowspan="2">Totals</th>
  </tr>
  <tr>
    <th>FR</th>
    
    <th>FR</th>
    <th>SO</th>
    <th>JR</th>
    <th>SR</th>
    
    <th>FR</th>
    <th>SO</th>
    <th>JR</th>
    <th>SR</th>
  </tr>
  <tr>
    <th>Male</th>
    <td>{BLG_F_FR_MALE}</td>
    
    <td>{BLG_T_FR_MALE}</td>
    <td>{BLG_T_SO_MALE}</td>
    <td>{BLG_T_JR_MALE}</td>
    <td>{BLG_T_SR_MALE}</td>
    
    <td>{BLG_C_FR_MALE}</td>
    <td>{BLG_C_SO_MALE}</td>
    <td>{BLG_C_JR_MALE}</td>
    <td>{BLG_C_SR_MALE}</td>
    
    <td rowspan="2">&nbsp;</td>
    
    <td>{BLG_TOTAL_MALES}</td>
  </tr>
  <tr>
    <th>Feale</th>
    <td>{BLG_F_FR_FEMALE}</td>
    
    <td>{BLG_T_FR_FEMALE}</td>
    <td>{BLG_T_SO_FEMALE}</td>
    <td>{BLG_T_JR_FEMALE}</td>
    <td>{BLG_T_SR_FEMALE}</td>
    
    <td>{BLG_C_FR_FEMALE}</td>
    <td>{BLG_C_SO_FEMALE}</td>
    <td>{BLG_C_JR_FEMALE}</td>
    <td>{BLG_C_SR_FEMALE}</td>
    
    <td>{BLG_TOTAL_FEMALES}</td>
  </tr>
  
  <tr>
    <th>Total</th>
    <td>{BLG_TOTAL_F}</td>
    <td colspan="4">{BLG_TOTAL_T}</td>
    <td colspan="4">{BLG_TOTAL_C}</td>
    <td>{BLG_OTHER}</td>
    <td>{BLG_TOTAL}</td>
  </tr>
</table>
<br />
<!-- END summaries -->

<!-- grand totals -->
<table border="1">
  <tr>
    <th colspan="12">
      <h2 style="text-align: center">Grand Totals</h2></th>
  </tr>
  <tr>
    <td rowspan="2"></td>
    <th colspan="1">Freshmen (F)</th>
    <th colspan="4">Transfer (T)</th>
    <th colspan="4">Continuing (C)</th>
    <th rowspan="2">Other (O)</th>
    <th rowspan="2">Totals</th>
  </tr>
  <tr>
    <th>FR</th>
    
    <th>FR</th>
    <th>SO</th>
    <th>JR</th>
    <th>SR</th>
    
    <th>FR</th>
    <th>SO</th>
    <th>JR</th>
    <th>SR</th>
  </tr>
  <tr>
    <th>Male</th>
    <td>{TOTAL_F_FR_MALE}</td>
    
    <td>{TOTAL_T_FR_MALE}</td>
    <td>{TOTAL_T_SO_MALE}</td>
    <td>{TOTAL_T_JR_MALE}</td>
    <td>{TOTAL_T_SR_MALE}</td>
    
    <td>{TOTAL_C_FR_MALE}</td>
    <td>{TOTAL_C_SO_MALE}</td>
    <td>{TOTAL_C_JR_MALE}</td>
    <td>{TOTAL_C_SR_MALE}</td>
    
    <td rowspan="2">&nbsp;</td>
    
    <td>{TOTAL_TOTAL_MALES}</td>
  </tr>
  <tr>
    <th>Female</th>
    <td>{TOTAL_F_FR_FEMALE}</td>
    
    <td>{TOTAL_T_FR_FEMALE}</td>
    <td>{TOTAL_T_SO_FEMALE}</td>
    <td>{TOTAL_T_JR_FEMALE}</td>
    <td>{TOTAL_T_SR_FEMALE}</td>
    
    <td>{TOTAL_C_FR_FEMALE}</td>
    <td>{TOTAL_C_SO_FEMALE}</td>
    <td>{TOTAL_C_JR_FEMALE}</td>
    <td>{TOTAL_C_SR_FEMALE}</td>
    
    <td>{TOTAL_TOTAL_FEMALES}</td>
  </tr>
  
  <tr>
    <th>Total</th>
    <td>{TOTAL_TOTAL_F}</td>
    <td colspan="4">{TOTAL_TOTAL_T}</td>
    <td colspan="4">{TOTAL_TOTAL_C}</td>
    <td>{TOTAL_OTHER}</td>
    <td>{TOTAL_TOTAL}</td>
  </tr>
</table>
