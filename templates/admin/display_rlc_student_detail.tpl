<div class="hms">
    <div class="box">
        <div class="box-title"> <h1>{TITLE}</h1> </div>
        <div class="box-content">
            <table>
                <tr>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Zip</th>
                    <th>ASU Box</th>
                    <th>Birthdate</th>
                    <th>Phone</th>
                    <th>Cell Phone</th>
                    <th>Email 1</th>
                    <th>Email 2</th>
                </tr>
                <tr>
                    <td>{ADDRESS}</td>
                    <td>{CITY}</td>
                    <td>{STATE}</td>
                    <td>{ZIP}</td>
                    <td>{ASU_BOX}</td>
                    <td>{BIRTHDATE}</td>
                    <td>{PHONE}</td>
                    <td>{CELL_PHONE}</td>
                    <td>{EMAIL_1}</td>
                    <td>{EMAIL_2}</td>
                </tr>
            </table>

            {START_FORM}
            <table>
                <tr>
                    <th>Comments</th>
                </tr>
                <tr>
                    <td>{COMMENTS}<br />{SUBMIT}</td>
                </tr>
            </table>
            {END_FORM}

            {FORM_ANSWERS}

        </div>
    </div>
</div>
