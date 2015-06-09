<h1>{NAME} - {TERM}</h1>

<p>Executed on: {EXEC_DATE} by {EXEC_USER}</p>

<p>
  <ul>
    <li>{TOTAL} unassigned student</li>
    <ul>
        <li>Male: {MALE}</li>
        <li>Female: {FEMALE}</li>
    </ul>
  </ul>
</p>

<table id="needs" cellpadding="2" border="1" style="border-collapse : collapse">
    <tr>
        <th>Banner ID</th>
        <th>User name</th>
        <th>Gender</th>
        <th>App Term</th>
        <th>Type</th>
        <th>Meal</th>
        <th>Date</th>
        <th>Lifestyle</th>
        <th>Bedtime</th>
        <th>Condition</th>
        <th>Room Type</th>
        <th>Roommate</th>
        <th>Roommate ID</th>
    </tr>
<!-- BEGIN rows -->
    <tr>
        <td>{banner_id}</td>
        <td>{username}</td>
        <td>{gender}</td>
        <td>{application_term}</td>
        <td>{student_type}</td>
        <td>{meal_plan}</td>
        <td>{created_on}</td>
        <td>{lifestyle_option}</td>
        <td>{preferred_bedtime}</td>
        <td>{room_condition}</td>
        <td>{room_type}</td>
        <td>{roommate}</td>
        <td>{roommate_id}</td>
    </tr>
<!-- END rows -->
</table>
