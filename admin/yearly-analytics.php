<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

$currentPage = 'yearly-analytics';
// Get available years from responses
$years = $pdo->query("
    SELECT DISTINCT date_part('year', created_at)::integer as year 
    FROM responses 
    WHERE created_at IS NOT NULL
    ORDER BY year DESC
")->fetchAll(PDO::FETCH_COLUMN);

// Get survey types
$types = $pdo->query("SELECT DISTINCT type FROM categories")->fetchAll(PDO::FETCH_COLUMN);

if (isset($_GET['year1']) && isset($_GET['year2']) && isset($_GET['type'])) {
    $year1 = $_GET['year1'];
    $year2 = $_GET['year2'];
    $type = $_GET['type'];

    try {
        $stmt = $pdo->prepare("
        SELECT 
            c.name AS category_name,
            q.question_text,
            COALESCE(ROUND(AVG(CASE 
                WHEN date_part('year', r.created_at) = :year1 AND r.answer ~ '^[0-9]+(\.[0-9]+)?$'
                THEN r.answer::numeric 
            END), 2), 0) as year1_avg,
            COALESCE(ROUND(AVG(CASE 
                WHEN date_part('year', r.created_at) = :year2 AND r.answer ~ '^[0-9]+(\.[0-9]+)?$'
                THEN r.answer::numeric 
            END), 2), 0) as year2_avg,
            COUNT(DISTINCT CASE 
                WHEN date_part('year', r.created_at) = :year1 
                THEN r.id 
            END) as year1_responses,
            COUNT(DISTINCT CASE 
                WHEN date_part('year', r.created_at) = :year2 
                THEN r.id 
            END) as year2_responses,
            COALESCE(ROUND((
                AVG(CASE WHEN date_part('year', r.created_at) = :year2 AND r.answer ~ '^[0-9]+(\.[0-9]+)?$' THEN r.answer::numeric END) -
                AVG(CASE WHEN date_part('year', r.created_at) = :year1 AND r.answer ~ '^[0-9]+(\.[0-9]+)?$' THEN r.answer::numeric END)
            ), 2), 0) as difference
        FROM categories c
        JOIN questions q ON c.id = q.category_id
        LEFT JOIN responses r ON q.id = r.question_id
        WHERE c.type = :type
        GROUP BY c.name, q.question_text, q.id
        ORDER BY c.name, q.id
        ");

        $stmt->execute([
            ':year1' => $year1,
            ':year2' => $year2,
            ':type' => $type
        ]);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) {
            echo "<div class='alert alert-warning'>No results found for the selected criteria.</div>";
        }
        
        $categories = [];
        foreach ($results as $row) {
            if (!isset($categories[$row['category_name']])) {
                $categories[$row['category_name']] = [];
            }
            $categories[$row['category_name']][] = $row;
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar-dynamic.php';
?>

<style>
.chart-container {
    min-height: 400px;
    margin: 20px 0;
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    padding: 15px;
}
.analysis-form {
    margin-bottom: 20px;
}
.badge {
    padding: 5px 10px;
    margin-right: 5px;
}
</style>

<div id="wrapper">
    <div class="main-content">
        <div class="row small-spacing">
            <div class="col-xs-12">
                <div class="box-content">
                    <div class="card-content">
                        <h4 class="box-title">Year by Survey Analysis</h4>
                            <button id="pdfExport" class="btn btn-info btn-sm waves-effect waves-light">
                                <i class="fa fa-file-pdf-o"></i> Export PDF
                            </button>
                            <button id="excelExport" class="btn btn-info btn-sm waves-effect waves-light">
                                <i class="fa fa-file-excel-o"></i> Export Excel
                            </button>
                        </div>
                 </div>
            </div>

            <div class="col-xs-12">
                <div class="box-content">
                    <form method="GET" class="analysis-form">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Base Year</label>
                                    <select name="year1" class="form-control" required>
                                        <option value="">Select Year</option>
                                        <?php foreach ($years as $year): ?>
                                            <option value="<?= $year ?>" <?= isset($_GET['year1']) && $_GET['year1'] == $year ? 'selected' : '' ?>>
                                                <?= $year ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Comparison Year</label>
                                    <select name="year2" class="form-control" required>
                                        <option value="">Select Year</option>
                                        <?php foreach ($years as $year): ?>
                                            <option value="<?= $year ?>" <?= isset($_GET['year2']) && $_GET['year2'] == $year ? 'selected' : '' ?>>
                                                <?= $year ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Survey Type</label>
                                    <select name="type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <?php foreach ($types as $type): ?>
                                            <!-- <option value="<?= $type ?>" <?= isset($_GET['type']) && $_GET['type'] == $type ? 'selected' : '' ?>>
                                                <?= ucfirst($type) ?> Survey
                                            </option> -->
                                            <option value="<?= $type ?>" <?= isset($_GET['type']) && $_GET['type'] == $type ? 'selected' : '' ?>>
                                                <?= ucfirst($type) ?> Survey
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-success btn-block waves-effect waves-light"><i class="fa fa-filter"></i> Analyze</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (isset($categories) && !empty($categories)): ?>
                <?php foreach ($categories as $categoryName => $questions): ?>
                    <div class="col-xs-12">
                        <div class="box-content">
                            <h4 class="box-title">
                                <?= htmlspecialchars($categoryName) ?>
                                <span class="badge badge-info margin-right-10">
                                    <?= $year1 ?>: <?= $questions[0]['year1_responses'] ?? 0 ?> responses
                                </span>
                                <span class="badge badge-info">
                                    <?= $year2 ?>: <?= $questions[0]['year2_responses'] ?? 0 ?> responses
                                </span>
                            </h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped margin-bottom-10">
                                    <thead>
                                        <tr>
                                            <th>Question</th>
                                            <th class="text-center"><?= $year1 ?></th>
                                            <th class="text-center"><?= $year2 ?></th>
                                            <th class="text-center">Change</th>
                                            <th>Impact Analysis</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($questions as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['question_text']) ?></td>
                                                <td class="text-center"><?= $row['year1_avg'] ?? 'N/A' ?></td>
                                                <td class="text-center"><?= $row['year2_avg'] ?? 'N/A' ?></td>
                                                <td class="text-center">
                                                    <?php if (isset($row['difference'])): ?>
                                                        <span class="badge <?= $row['difference'] > 0 ? 'badge-success' : ($row['difference'] < 0 ? 'badge-danger' : 'badge-default') ?>">
                                                            <?= $row['difference'] > 0 ? '+' : '' ?><?= $row['difference'] ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-default">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if (isset($row['difference'])) {
                                                        $diff = abs($row['difference']);
                                                        if ($diff >= 1) {
                                                            echo $row['difference'] > 0 
                                                                ? '<span class="text-success">Significant Improvement</span>'
                                                                : '<span class="text-danger">Critical Decline - Needs Attention</span>';
                                                        } elseif ($diff >= 0.5) {
                                                            echo $row['difference'] > 0
                                                                ? '<span class="text-success">Moderate Improvement</span>'
                                                                : '<span class="text-warning">Moderate Decline - Monitor</span>';
                                                        } elseif ($diff > 0) {
                                                            echo $row['difference'] > 0
                                                                ? '<span class="text-info">Slight Improvement</span>'
                                                                : '<span class="text-warning">Slight Decline</span>';
                                                        } else {
                                                            echo '<span class="text-muted">No Change</span>';
                                                        }
                                                    } else {
                                                        echo '<span class="text-muted">N/A</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="chart-container">
                                <div id="chart-<?= preg_replace('/[^a-zA-Z0-9-]/', '-', strtolower($categoryName)) ?>"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($categories) && !empty($categories)): ?>
        <?php foreach ($categories as $categoryName => $questions): ?>
            var chartData = {
                questions: [],
                year1Data: [],
                year2Data: []
            };
            
            <?php foreach ($questions as $row): ?>
                chartData.questions.push(<?= json_encode($row['question_text']) ?>);
                chartData.year1Data.push(<?= floatval($row['year1_avg']) ?>);
                chartData.year2Data.push(<?= floatval($row['year2_avg']) ?>);
            <?php endforeach; ?>

            var containerId = 'chart-<?= preg_replace('/[^a-zA-Z0-9-]/', '-', strtolower($categoryName)) ?>';
            
            Highcharts.chart(containerId, {
                chart: { 
                    type: 'column',
                    height: 400
                },
                title: { 
                    text: '<?= htmlspecialchars($categoryName) ?> - Year Comparison'
                },
                xAxis: {
                    categories: chartData.questions,
                    labels: { 
                        style: { fontSize: '11px' },
                        rotation: -45
                    }
                },
                yAxis: {
                    min: 0,
                    max: 5,
                    title: { text: 'Average Score' }
                },
                legend: {
                    align: 'center',
                    verticalAlign: 'bottom'
                },
                plotOptions: {
                    column: { 
                        groupPadding: 0.2,
                        pointPadding: 0.1
                    }
                },
                series: [{
                    name: '<?= $year1 ?>',
                    data: chartData.year1Data,
                    color: '#36a2eb'
                }, {
                    name: '<?= $year2 ?>',
                    data: chartData.year2Data,
                    color: '#ff6384'
                }]
            });
        <?php endforeach; ?>
    <?php endif; ?>
});
</script>

<?php require_once '../includes/footer.php'; ?>