<div class="row">
    <div class="col-md-12">
        <h2>Choose a Residence Hall</h2>
        <p>
            Congratulations, you have been selected for on-campus housing for {TERM}!
        </p>

        <p>
            You may select any room which is currently available. Browse available rooms by selecting a residence hall below.
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-3 col-md-offset-1">
        <table class="table table-striped table-bordered">
            <tr>
                <th>
                    Residence Hall
                </th>
            </tr>
            <!-- BEGIN hall_list -->
            <tr>
                <td>
                    {HALL_NAME}
                </td>
            </tr>
            <!-- END hall_list -->
        </table>

        <!-- BEGIN nothing_left -->
        {NOTHING_LEFT}
        <p><strong>Oops!</strong> It looks like there's no remaining beds in your assigned Residential Learning Community. For more detail, you may want to contact University Housing by calling 828-262-6111.</p>
        <!-- END nothing_left -->
    </div>
</div>
