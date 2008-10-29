<h1>Application Features</h1>
<br />
<!-- BEGIN error_msg -->
<span class="error">{ERROR}</span>
<!-- END error_msg -->
<!-- BEGIN success_msg -->
<span class="success">{SUCCESS}</span>
<!-- END success_msg -->
<br />
{START_FORM}
{TERM}
<table>
<tr>
    <th colspan=2>Feature</th>
</tr>
<!-- BEGIN feature_repeat -->
<tr>
    <td>{FEATURE_LABEL}</td>
    <td>{FEATURE}</td>
</tr>
<!-- END feature_repeat -->
</table>
<br />
{SUBMIT_BUTTON}
{END_FORM}
