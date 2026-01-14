<style>
    .highcharts-title {
    display: none;
}
</style>
<div id="wrapper">
    <div class="main-content">
        <div class="row small-spacing">
            <div class="col-xs-12">
            <div class="box-content">
                <div class="card-content">
                    <h1 class="page-title"><?php echo ucfirst($surveyType); ?> Survey Analytics</h1>
                    <span class="badge total-responses">Total Responses: <?php echo $total_responses; ?></span>
                    <div class="pull-right export-buttons">
                        <button id="pdfExport" class="btn btn-info btn-sm waves-effect waves-light">
                            <i class="fa fa-file-pdf-o"></i> Export PDF
                        </button>
                        <button id="excelExport" class="btn btn-info btn-sm waves-effect waves-light">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
            </div>

            <?php foreach ($statistics as $category => $questions): ?>
            <div class="col-lg-12">
                <div class="box-content">
                    <h4 class="box-title"><?php echo htmlspecialchars($category); ?></h4>
                    <?php foreach ($questions as $analysis): ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <?php if ($analysis['type'] === 'text'): ?>
                                    <div class="box-content">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th width="5%">#</th>
                                                        <th>Response</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($analysis['answers'])): ?>
                                                        <?php $counter = 1; ?>
                                                        <?php foreach ($analysis['answers'] as $answer): ?>
                                                            <?php if (!empty(trim($answer))): ?>
                                                                <tr>
                                                                    <td><?php echo $counter++; ?></td>
                                                                    <td><?php echo nl2br(htmlspecialchars($answer)); ?></td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                        <?php else: ?>
                                                        <tr>
                                                            <td colspan="2" class="text-center">No responses available</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                        <?php if (in_array($analysis['type'], ['likert_scale', 'drop_down', 'checkbox'])): ?>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="box-content">
                                                    <h4 class="box-title"><?php echo htmlspecialchars($analysis['question']); ?></h4>
                                                    <div id="barChart_<?php echo $analysis['question_id']; ?>" class="chart-container"></div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="box-content">
                                                    <h4 class="box-title">Response Distribution</h4>
                                                    <div id="pieChart_<?php echo $analysis['question_id']; ?>" class="chart-container"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                 </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script>

document.addEventListener('DOMContentLoaded', function() {
    try {
        if (typeof Highcharts === 'undefined') {
            throw new Error('Highcharts library not loaded');
        }

        <?php foreach ($statistics as $category => $questions): ?>
            <?php foreach ($questions as $analysis): ?>
                <?php if (in_array($analysis['type'], ['likert_scale', 'drop_down', 'checkbox'])): ?>
                    var chartData = {
                        labels: <?php echo json_encode($analysis['labels'] ?? []); ?>,
                        data: <?php echo json_encode($analysis['data'] ?? []); ?>
                    };
                    if (chartData.labels.length > 0) {
                        createCharts(
                            <?php echo json_encode($analysis['question_id']); ?>,
                            chartData,
                            <?php echo json_encode($analysis['question']); ?>,
                            <?php echo json_encode($analysis['type']); ?>
                        );
                    }
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    } catch (error) {
        console.error('Chart initialization error:', error);
    }
});

function createCharts(questionId, chartData, questionTitle, questionType) {
    // Bar Chart
    Highcharts.chart('barChart_' + questionId, {
        chart: { 
            type: 'column',
            height: 400
        },
        title: { text: questionTitle },
        xAxis: {
            categories: chartData.labels,
            title: { text: 'Options' },
            labels: {
                style: { fontSize: '11px' },
                wrap: true,
                rotation: -45
            }
        },
        yAxis: {
            title: { text: 'Number of Responses' },
            allowDecimals: false,
            min: 0
        },
        plotOptions: {
            column: {
                colorByPoint: questionType === ['checkbox', 'drop_down'],
                dataLabels: {
                    enabled: true
                }
            }
        },
        series: [{
            name: 'Responses',
            data: chartData.data
        }],
        credits: { enabled: false }
    });

    // Pie Chart
    Highcharts.chart('pieChart_' + questionId, {
        chart: { 
            type: 'pie',
            height: 400
        },
        title: { text: 'Response Distribution' },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f}%'
                }
            }
        },
        series: [{
            name: 'Responses',
            data: chartData.labels.map((label, index) => ({
                name: label,
                y: chartData.data[index]
            }))
        }],
        credits: { enabled: false }
    });
}
</script>
