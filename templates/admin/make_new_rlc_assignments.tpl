<div class="hms">
    <div class="box">
        <div class="box-title"> <h1>{TITLE}</h1> </div>

        {SUMMARY}
        
        <div class="box-content">
            <h2>Applicants</h2>
            <table cellpadding="4" cellspacing="1" width="99%">
                <tr>
                    <th>Name</th>
                    <th>1st Choice</th>
                    <th>Final RLC</th>
                    <th>2nd Choice</th>
                    <th>3rd Choice</th>
                    <th>Special Pop</th>
                    <th>Major</th>
                    <th>HS GPA</th>
                    <th>Gender</th>
                    <th>Roommate</th>
                    <th>Apply Date</th>
                    <th>Course OK?</th>
                    <th>Final Assign. by</th>
                </tr>
<!-- BEGIN listrows -->
                <tr {TOGGLE}>
                    <td>{NAME}</td>
                    <td>{1ST_CHOICE}</td>
                    <td>{FINAL_RLC}</td>
                    <td>{2ND_CHOICE}</td>
                    <td>{3RD_CHOICE}</td>
                    <td>{SPECIAL_POP}</td>
                    <td>{MAJOR}</td>
                    <td>{HS_GPA}</td>
                    <td>{GENDER}</td>
                    <td>{ROOMMATE}</td>
                    <td>{APPLY_DATE}</td>
                    <td>{COURSE_OK}</td>
                    <td>{FINAL_ASSIGN_BY}</td>
                </tr>
<!-- END listrows -->
            </table>
            {EMPTY_MESSAGE}
            <div class="align-center">
                {TOTAL_ROWS}<br />
                {PAGE_LABEL} {PAGES}<br />
                {LIMIT_LABEL} {LIMITS}
            </div>
        </div>
        
    </div>
</div>
