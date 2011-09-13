<!-- BEGIN hall-rows -->
<h2>{HALL}</h2>
<table width="100%" border="1" cellpadding="2" style="border-collapse : collapse;">
    <tr>
        <th width="5%">Floor</th>
        <th width="10%">Room</th>
        <th width="15%">Banner ID</th>
        <th width="30%">Name</th>
        <th width="20%">Username</th>
        <th width="5%">Year</th>
        <th width="10%">Birthdate</th>
        <th width="5%">Gender</th>
    </tr>
<!-- BEGIN room-rows -->
    <tr>
        <td>{floor_number}</td>
        <td>{room_number} {bedroom_label} {bed_letter}</td>
        <td>{banner_id}</td>
        <td>{name}</td>
        <td>{asu_username}</td>
        <td>{year}</td>
        <td>{dob}</td>
        <td>{gender}</td>
    </tr>
<!-- END room-rows -->
</table>
<!-- END hall-rows -->
