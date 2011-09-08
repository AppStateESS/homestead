<style>
table#needs td {
border : 1px solid black;
}

table#needs .nope {
font-weight : bold;
border : none;
}

table#needs .cat {
border-top : 1px solid black;
}
</style>

<h1>{NAME} - {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}<br />

<table id="needs" width="100%" cellpadding="3" border="1" style="border-collapse : collapse">
    <tr>
        <th class="nope" width="80px">&#160;</th>
        <th class="nope" width="30px">&#160;</th>
        <th width="80px">Banner Id</th>
        <th width="200px">Name</th>
        <th width="100px">Username</th>
        <th width="80px">Class Status</th>
        <th width="80px">Student Type</th>
    </tr>
<!-- BEGIN f -->
    <tr>
        <td class="{style}">{f_word}</td>
        <td class="{style}">{f_total}</td>
        <td width="80px">{banner_id}</td>
        <td width="200px">{name}</td>
        <td width="100px">{username}</td>
        <td width="80px" style="text-align : center">{class}</td>
        <td width="80px" style="text-align : center">{student_type}</td>
    </tr>
<!-- END f -->
<!-- BEGIN s -->
    <tr>
        <td class="{style}">{s_word}</td>
        <td class="{style}">{s_total}</td>
        <td width="80px">{banner_id}</td>
        <td width="200px">{name}</td>
        <td width="100px">{username}</td>
        <td width="80px" style="text-align : center">{class}</td>
        <td width="80px" style="text-align : center">{student_type}</td>
    </tr>
<!-- END s -->
<!-- BEGIN g -->
    <tr>
        <td class="{style}">{g_word}</td>
        <td class="{style}">{g_total}</td>
        <td width="80px">{banner_id}</td>
        <td width="200px">{name}</td>
        <td width="100px">{username}</td>
        <td width="80px" style="text-align : center">{class}</td>
        <td width="80px" style="text-align : center">{student_type}</td>
    </tr>
<!-- END g -->
<!-- BEGIN m -->
    <tr>
        <td class="{style}">{m_word}</td>
        <td class="{style}">{m_total}</td>
        <td width="80px">{banner_id}</td>
        <td width="200px">{name}</td>
        <td width="100px">{username}</td>
        <td width="80px" style="text-align : center">{class}</td>
        <td width="80px" style="text-align : center">{student_type}</td>
    </tr>
<!-- END m -->
</table>