<div class="hms">
    <div class="box">
        <div class="box-title"> <h1>{TITLE}</h1> </div>

        <div class="box-content">
            <ul>
                <li>{PRINT_RECORDS}</li>
                <li>{EXPORT}</li>
            </ul>

            <table cellpadding="4" cellspacing="1" width="99%">
                <tr>
                    <th>Name</th>
                    <th>Final RLC</th>
                    <th>Course OK?</th>
                    <th>Roommate</th>
                    <th>Address</th>
                    <th>Phone/Cell</th>
                    <th>Email</th>
                </tr>
<!-- BEGIN listrows -->
                <tr>
                    <td>{NAME}</td>
                    <td>{FINAL_RLC}</td>
                    <td>{COURSE_OK}</td>
                    <td>{ROOMMATE}</td>
                    <td>{ADDRESS}</td>
                    <td>{PHONE}<br />{CELL}</td>
                    <td>{EMAIL_1}<br />{EMAIL_2}</td>
                </tr>
<!-- END listrows -->
            </table>
        </div>
        
    </div>
</div>
