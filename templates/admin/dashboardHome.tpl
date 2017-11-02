
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

<!--  Charts Plugin -->
<script src="./mod/hms/node_modules/chartist/dist/chartist.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){

        var dataSales = {
          labels: ['9:00AM', '12:00AM', '3:00PM', '6:00PM', '9:00PM', '12:00PM', '3:00AM', '6:00AM'],
          series: [
             [287, 385, 490, 492, 554, 586, 698, 695, 752, 788, 846, 944],
            [67, 152, 143, 240, 287, 335, 435, 437, 539, 542, 544, 647],
            [23, 113, 67, 108, 190, 239, 307, 308, 439, 410, 410, 509]
          ]
        };

        var optionsSales = {
          lineSmooth: false,
          low: 0,
          high: 800,
          showArea: true,
          height: "245px",
          axisX: {
            showGrid: false,
          },
          lineSmooth: Chartist.Interpolation.simple({
            divisor: 3
          }),
          showLine: false,
          showPoint: false,
        };

        var responsiveSales = [
          ['screen and (max-width: 640px)', {
            axisX: {
              labelInterpolationFnc: function (value) {
                return value[0];
              }
            }
          }]
        ];

        Chartist.Line('#chartHours', dataSales, optionsSales, responsiveSales);


        var data = {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          series: [
            [542, 443, 320, 780, 553, 453, 326, 434, 568, 610, 756, 895],
            [412, 243, 280, 580, 453, 353, 300, 364, 368, 410, 636, 695]
          ]
        };

        var options = {
            seriesBarDistance: 10,
            axisX: {
                showGrid: false
            },
            height: "245px"
        };

        var responsiveOptions = [
          ['screen and (max-width: 640px)', {
            seriesBarDistance: 5,
            axisX: {
              labelInterpolationFnc: function (value) {
                return value[0];
              }
            }
          }]
        ];

        Chartist.Bar('#chartActivity', data, options, responsiveOptions);

        var dataPreferences = {
            series: [
                [25, 30, 20, 25]
            ]
        };

        var optionsPreferences = {
            donut: true,
            donutWidth: 40,
            startAngle: 0,
            total: 100,
            showLabel: false,
            axisX: {
                showGrid: false
            }
        };

        Chartist.Pie('#chartPreferences', dataPreferences, optionsPreferences);

        Chartist.Pie('#chartPreferences', {
          labels: ['62%','32%','6%'],
          series: [62, 32, 6]
        });
    });
</script>
