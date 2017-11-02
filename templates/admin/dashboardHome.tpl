
<div class="row">
    <div class="col-lg-3 col-sm-6">
        <div class="card card-stats">
            <div class="content">
                <div class="row">
                    <div class="col-xs-5">
                        <div class="icon-big text-center icon-warning">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="col-xs-7">
                        <div class="numbers">
                            <p>Residents</p>
                            {NUM_RESIDENTS}
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-refresh"></i>
                        <!-- react-text: 125 --> <!-- /react-text --><!-- react-text: 126 -->Updated now<!-- /react-text -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="card card-stats">
            <div class="content">
                <div class="row">
                    <div class="col-xs-5">
                        <div class="icon-big text-center icon-warning">
                            <i class="fa fa-bed"></i>
                        </div>
                    </div>
                    <div class="col-xs-7">
                        <div class="numbers">
                            <p>Available Beds</p>
                            {NUM_BEDS_AVAIL}
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-refresh"></i>
                        <!-- react-text: 125 --> <!-- /react-text --><!-- react-text: 126 -->Updated now<!-- /react-text -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="card card-stats">
            <div class="content">
                <div class="row">
                    <div class="col-xs-5">
                        <div class="icon-big text-center icon-warning">
                            <i class="fa fa-exclamation"></i>
                        </div>
                    </div>
                    <div class="col-xs-7">
                        <div class="numbers">
                            <p>Overflow Assignments</p>
                            {OVERFLOW_ASSIGNMENTS}
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-refresh"></i>
                        <!-- react-text: 125 --> <!-- /react-text --><!-- react-text: 126 -->Updated now<!-- /react-text -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="header">
                <h4 class="title">Class Makeup</h4>
                <p class="category">Current Residents</p>
            </div>
            <div class="content">
                <div id="chartPreferences" class="ct-chart ct-perfect-fourth"></div>

                <div class="footer">
                    <div class="legend">
                        <i class="fa fa-circle text-info"></i> Freshmen
                        <i class="fa fa-circle text-danger"></i> Sophomore
                        <i class="fa fa-circle text-warning"></i> Junior
                        <i class="fa fa-circle text-success"></i> Senior
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="header">
                <h4 class="title">Residents Over Time</h4>
                <p class="category">Recent &amp; Future Semesters</p>
            </div>
            <div class="content">
                <div id="chartHours" class="ct-chart"></div>
                <div class="footer">
                    <div class="legend">
                        <i class="fa fa-circle text-info"></i> Open
                        <i class="fa fa-circle text-danger"></i> Click
                        <i class="fa fa-circle text-warning"></i> Click Second Time
                    </div>
                    <hr>
                    <div class="stats">
                        <i class="fa fa-history"></i> Updated 3 minutes ago
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card ">
            <div class="header">
                <h4 class="title">2014 Sales</h4>
                <p class="category">All products including Taxes</p>
            </div>
            <div class="content">
                <div id="chartActivity" class="ct-chart"></div>

                <div class="footer">
                    <div class="legend">
                        <i class="fa fa-circle text-info"></i> Tesla Model S
                        <i class="fa fa-circle text-danger"></i> BMW 5 Series
                    </div>
                    <hr>
                    <div class="stats">
                        <i class="fa fa-check"></i> Data information certified
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
