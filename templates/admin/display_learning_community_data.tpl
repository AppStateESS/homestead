<div class="box-title">
<h1>{TITLE}</h1>
</div>
<!-- BEGIN community -->
<h2>{COMMUNITY}</h2>
<!-- END community -->
<div class="box-content">
{START_FORM}
<table>
	<tr>
		<td>Community Name:&nbsp;</td>
		<td>{COMMUNITY_NAME}</td>
	</tr>
	<tr>
		<td>Abbreviation:</td>
		<td>{ABBREVIATION}</td>
	</tr>
	<tr>
		<td>Capacity:</td>
		<td>{CAPACITY}</td>
	</tr>
	<tr>
	   <td>First-time Application Allowed Student Types:</td>
	   <td>{STUDENT_TYPES} (comma separated list, i.e.: 'F,C,T')</td>
	</tr>
	<tr>
	   <td>Re-application Allowed Student Types:</td>
	   <td>{REAPPLICATION_STUDENT_TYPES} (comma separated list, i.e.: 'F,C,T')</td>
	</tr>
	<tr>
	   <td>{MEMBERS_REAPPLY_LABEL}</td>
	   <td>{MEMBERS_REAPPLY}</td>
	</tr>
</table>
<br />
{SUBMIT} {END_FORM}
</div>