        <div class="box-content">
<!-- BEGIN content -->
            <h2>Summary</h2>
            <table cellpadding="4" cellspacing="1" width="99%">
                <tr>
                    <th>&nbsp;</th>
<!-- BEGIN headings -->
                    <th>{HEADING}</th>
<!-- END headings -->
                    <th>Total</th>
                </tr>
                <tr>
                    <th>Assignments (male/female)</th>
<!-- BEGIN assignments -->
                    <td>{ASSIGNMENT}</td>
<!-- END assignments -->
                    <td>{TOTAL_ASSIGNMENTS}</td>
                </tr>
                <tr>
                    <th>Total Seats</th>
<!-- BEGIN available -->
                    <td>{AVAILABLE}</td>
<!-- END available -->
                    <td>{TOTAL_AVAILABLE}</td>
                </tr>
                <tr>
                    <th>Seats Remaining</th>
<!-- BEGIN remaining -->
                    <td>{REMAINING}</td>
<!-- END remaining -->
                    <td>{TOTAL_REMAINING}</td>
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
