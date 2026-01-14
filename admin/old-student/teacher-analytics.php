<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/analytics-common.php';
requireLogin();

// Set page configuration
$currentPage = 'student-teacher-analytics';
$surveyType = 'student';

// Check if filtering by a specific teacher
$selectedTeacherId = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : null;

// Get the list of teachers with responses
$teachers = getTeachersWithResponses($pdo);

// Initialize variables
$statistics = [];
$total_responses = 0;
$teacherName = 'All Teachers';

try {
    // Verify database connection
    if (!$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
        throw new PDOException("Database connection not established");
    }

    // Get teacher-specific analytics data
    $results = getTeacherAnalyticsData($pdo, $selectedTeacherId);
    if (!$results) {
        throw new Exception("No data retrieved for teacher feedback");
    }

    // Process the data
    $data = processAnalyticsData($results);
    if (!isset($data['statistics']) || !isset($data['total_responses'])) {
        throw new Exception("Invalid data structure returned from processing");
    }

    // Set the processed data
    $statistics = $data['statistics'];
    $total_responses = $data['total_responses'];
    
    // Get teacher name if a specific teacher is selected
    if ($selectedTeacherId) {
        foreach ($teachers as $teacher) {
            if ($teacher['teacher_user_dcid'] == $selectedTeacherId) {
                $teacherName = $teacher['teacher_name'];
                break;
            }
        }
    }

    // Include template files
    require_once '../../includes/header.php';
    require_once '../../includes/sidebar.php';

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
} catch (Exception $e) {
    error_log("Analytics Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die("An error occurred while processing the data: " . $e->getMessage());
}
?>

<div id="wrapper">
    <div class="main-content">
        <div class="row small-spacing">
            <div class="col-xs-12">
                <div class="box-content">
                    <div class="card-content">
                        <h1 class="page-title">Teacher Feedback Analytics</h1>
                        <p>Viewing feedback for: <strong><?php echo htmlspecialchars($teacherName); ?></strong></p>
                        <span class="badge total-responses">Total Responses: <?php echo $total_responses; ?></span>
                        
                        <!-- Teacher Filter -->
                        <div class="filter-container mb-4">
                            <form method="GET" class="form-inline">
                                <div class="form-group">
                                    <label for="teacher_id">Select Teacher:</label>
                                    <select name="teacher_id" id="teacher_id" class="form-control ml-2" onchange="this.form.submit()">
                                        <option value="">All Teachers</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo htmlspecialchars($teacher['teacher_user_dcid']); ?>" 
                                                <?php echo $selectedTeacherId == $teacher['teacher_user_dcid'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($teacher['teacher_name']); ?> 
                                                (<?php echo $teacher['response_count']; ?> responses)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                        
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

            <?php if (empty($statistics)): ?>
                <div class="col-xs-12">
                    <div class="box-content">
                        <div class="alert alert-info">
                            No teacher feedback data available. Please select a different teacher or check if responses have been submitted.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($statistics as $category => $questions): ?>
                <div class="col-lg-12">
                    <div class="box-content">
                        <h4 class="box-title"><?php echo htmlspecialchars($category); ?></h4>
                        <?php foreach ($questions as $analysis): ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <?php if ($analysis['type'] === 'text'): ?>
                                        <div class="box-content">
                                            <h4 class="box-title"><?php echo htmlspecialchars($analysis['question']); ?></h4>
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
            <?php endif; ?>
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

<?php require_once '../../includes/footer.php'; ?> 