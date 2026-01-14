<?php
// analytics-template-ai.php
?>
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
                                    <?php 
                                        $qId   = $analysis['question_id'];
                                        $qType = $analysis['type'];
                                        $qText = $analysis['question'];
                                    ?>

                                    <!-- Display question & results -->
                                    <?php if ($qType === 'text'): ?>
                                        <div class="box-content">
                                            <h4 class="box-title"><?php echo htmlspecialchars($qText); ?></h4>
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
                                    <?php elseif (in_array($qType, ['likert_scale', 'drop_down', 'checkbox'])): ?>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="box-content">
                                                    <h4 class="box-title"><?php echo htmlspecialchars($qText); ?></h4>
                                                    <div id="barChart_<?php echo $qId; ?>" class="chart-container"></div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="box-content">
                                                    <h4 class="box-title">Response Distribution</h4>
                                                    <div id="pieChart_<?php echo $qId; ?>" class="chart-container"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Catch other question types if necessary -->
                                    <?php endif; ?>

                                    <!-- AI Insights Trigger + Container -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <!-- Button to fetch AI for this question only -->
                                            <button 
                                                class="btn btn-sm btn-warning generate-insight" 
                                                data-qid="<?php echo $qId; ?>"
                                                data-survey="<?php echo htmlspecialchars($surveyType); ?>"
                                            >
                                                <i class="fa fa-magic"></i> Generate AI Insights
                                            </button>

                                            <!-- Container where we display the AI output -->
                                            <div 
                                                class="ai-insights-container" 
                                                id="aiInsights_<?php echo $qId; ?>" 
                                                style="margin-top:10px;">
                                                <!-- Populated dynamically via AJAX -->
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End AI Insights -->

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
    // Initialize charts
    try {
        if (typeof Highcharts === 'undefined') {
            throw new Error('Highcharts library not loaded');
        }
        <?php foreach ($statistics as $category => $questions): ?>
            <?php foreach ($questions as $analysis): ?>
                <?php if (in_array($analysis['type'], ['likert_scale', 'drop_down', 'checkbox'])): ?>
                    var chartData = {
                        labels: <?php echo json_encode($analysis['labels'] ?? []); ?>,
                        data:   <?php echo json_encode($analysis['data'] ?? []); ?>
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

    // Attach click handler to each "Generate AI Insights" button
    document.querySelectorAll('.generate-insight').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var questionId = this.getAttribute('data-qid');
            var surveyType = this.getAttribute('data-survey');
            if (!questionId) {
                alert('Missing question ID.');
                return;
            }

            // AJAX call to generate-ai.php?questionId=xxx&surveyType=yyy
            var url = '../../includes/generate-ai.php?questionId=' + encodeURIComponent(questionId);
            if (surveyType) {
                url += '&surveyType=' + encodeURIComponent(surveyType);
            }

            // Show a loading message or spinner if desired
            var container = document.getElementById('aiInsights_' + questionId);
            if (container) {
                container.innerHTML = '<div><em>Generating AI insights, please wait...</em></div>';
            }

            fetch(url)
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success && data.insight) {
                        var aiText = data.insight;
                        // Display inside container
                        if (container) {
                            // Because AI might return HTML, insert it as innerHTML
                            container.innerHTML = aiText;
                        }
                    } else {
                        if (container) {
                            container.innerHTML = '<div><em>Failed to generate AI insights.</em></div>';
                        }
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    if (container) {
                        container.innerHTML = '<div><em>Error generating AI insights. See console for details.</em></div>';
                    }
                });
        });
    });
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
