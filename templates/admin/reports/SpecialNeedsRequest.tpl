<h1>{NAME} - {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}<br />

<table width="100%" cellpadding="3">
    <tr>
        <td width="100px">&#160;</td>
        <td width="50px">&#160;</td>
        <th width="80px">Banner Id</th>
        <th width="200px">Name</th>
        <th width="100px">Username</th>
        <th width="80px">Class Status</th>
        <th width="80px">Student Type</th>
    </tr>
    <tr>
        <td>Physical</td>
        <td>{f_total}</td>
        <td colspan="5">
        <table width="100%">
<!-- BEGIN f -->
            <tr>
                <td width="80px">{banner_id}</td>
                <td width="200px">{name}</td>
                <td width="100px">{username}</td>
                <td width="80px" style="text-align : center">{class}</td>
                <td width="80px" style="text-align : center">{type}</td>
            </tr>
<!-- END f -->
        </table>
        </td>
    </tr>
    <tr>
        <td>Psychological</td>
        <td>{s_total}</td>
        <td colspan="5">
        <table>
<!-- BEGIN s -->
            <tr>
                <td width="80px">{banner_id}</td>
                <td width="200px">{name}</td>
                <td width="100px">{username}</td>
                <td width="80px" style="text-align : center">{class}</td>
                <td width="80px" style="text-align : center">{type}</td>
            </tr>
<!-- END s -->
        </table>
        </td>
    </tr>
    <tr>
        <td>Gender</td>
        <td>{g_total}</td>
        <td colspan="5">
        <table>
<!-- BEGIN g -->
            <tr>
                <td width="80px">{banner_id}</td>
                <td width="200px">{name}</td>
                <td width="100px">{username}</td>
                <td width="80px" style="text-align : center">{class}</td>
                <td width="80px" style="text-align : center">{type}</td>
            </tr>
<!-- END g -->
        </table>
        </td>
    </tr>
    <tr>
        <td>Medical</td>
        <td>{m_total}</td>
        <td colspan="5">
        <table>
<!-- BEGIN m -->
            <tr>
                <td width="80px">{banner_id}</td>
                <td width="200px">{name}</td>
                <td width="100px">{username}</td>
                <td width="80px" style="text-align : center">{class}</td>
                <td width="80px" style="text-align : center">{type}</td>
            </tr>
<!-- END m -->
        </table>
        </td>
    </tr>
</table>