<h1>Add/Edit a learning Community</h1>

<!-- BEGIN community -->
<h2>{COMMUNITY}</h2>
<!-- END community -->

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
    <tr>
        <td>{FRESHMEN_QUESTION_LABEL}</td>
        <td>{FRESHMEN_QUESTION}</td>
    </tr>
    <tr>
        <td>{RETURNING_QUESTION_LABEL}</td>
        <td>{RETURNING_QUESTION}</td>
    </tr>
</table>
<br />
{SUBMIT} {END_FORM}