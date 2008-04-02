<div class="hms">
    <div class="box">
        <div class="box-title"> <h1>{TITLE}</h1> </div>

        <div class="box-content">
<!-- BEGIN options -->
            <ul>
                <li>{PRINT_RECORDS}</li>
                <li>{EXPORT}</li>
            </ul>
<!-- END options -->

<!-- BEGIN empty_table -->
            <p>{EMPTY_MESSAGE}</p>
<!-- END empty_table -->

            <table cellpadding="4" cellspacing="1" width="99%">
                <tr>
                    <th>Name {NAME_SORT}</th>
                    <th>Final RLC {FINAL_RLC_SORT}</th>
                    <th>Roommate {ROOMMATE_SORT}</th>
                    <th>Address {ADDRESS_SORT}</th>
                    <th>Phone/Email</th>
                </tr>
<!-- BEGIN listrows -->
                <tr {TOGGLE}>
                    <td>{NAME}</td>
                    <td>{FINAL_RLC}<td>
                    <td>{ROOMMATE}</td>
                    <td>{ADDRESS}</td>
                    <td>{PHONE}<br />{EMAIL}</td>
                </tr>
<!-- END listrows -->
            </table>
            <div class="align-center">
                {TOTAL_ROWS}<br />
                {PAGE_LABEL} {PAGES}<br />
                {LIMIT_LABEL} {LIMITS}
            </div>
        </div>
    </div>
</div>
