        <div class="box-content">
<!-- BEGIN content -->
            <h2>Summary</h2>
            <table cellpadding="4" cellspacing="1" width="99%">
                <tr>
                    <th>&nbsp;</th>
                    <th>Total</th>
<!-- BEGIN headings -->
                    <th>{HEADING}</th>
<!-- END headings -->
                </tr>
                <tr>
                    <th>Assignments (male/female)</th>
                    <td>{TOTAL_ASSIGNMENTS}</td>
<!-- BEGIN assignments -->
                    <td>{ASSIGNMENT}</td>
<!-- END assignments -->
                </tr>
                <tr>
                    <th>Total Seats</th>
                    <td>{TOTAL_AVAILABLE}</td>
<!-- BEGIN available -->
                    <td>{AVAILABLE}</td>
<!-- END available -->
                </tr>
                <tr>
                    <th>Seats Remaining</th>
                    <td>{TOTAL_REMAINING}</td>
<!-- BEGIN remaining -->
                    <td>{REMAINING}</td>
<!-- END remaining -->
                </tr>   
            </table>
            {EMPTY_MESSAGE}
            <div class="align-center">
                {TOTAL_ROWS}<br />
                {PAGE_LABEL} {PAGES}<br />
                {LIMIT_LABEL} {LIMITS}
            </div>
<!-- END content -->
<!-- BEGIN nocontent -->
            Summary: {NO_COMMUNITIES}
<!-- END nocontent -->
        </div>
