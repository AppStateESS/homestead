        <div class="box-content">
            <h2>Summary</h2>
            <table cellpadding="4" cellspacing="1" width="99%">
                <tr>
                    <th>&nbsp;</th>
                    <th>Total</th>
                    {HEADINGS}
                </tr>
<!-- BEGIN listrows -->
                <tr {TOGGLE}>
                    <td>{STATISTIC}</td>
                    <td>{TOTAL}</td>
                    {COLUMNS}
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
