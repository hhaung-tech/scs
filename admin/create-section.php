<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surveyType = $_POST['survey_type'] ?? '';
    $sectionName = trim($_POST['section_name'] ?? '');
    
    if (empty($sectionName)) {
        $error = "Section name cannot be empty";
    } elseif (empty($surveyType)) {
        $error = "Survey type is required";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, type) VALUES (?, ?)");
            $stmt->execute([$sectionName, $surveyType]);
            $success = "Section '{$sectionName}' created successfully for {$surveyType} survey!";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

$currentPage = 'create-section';
require_once '../includes/header.php';
require_once '../includes/sidebar-dynamic.php';
?>

<!-- ISY admin theme (navy + gold) -->
<link rel="stylesheet" href="/isy_scs_ai/assets/styles/isy-admin-theme.css">

<div class="wrapper">
<div class="main-content">
    <div class="row small-spacing">
        <div class="col-xs-12">
            <div class="box-content card white">
                <h4 class="box-title"><i class="fa fa-plus-circle"></i> Section Manager</h4>
                <div class="card-content">
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <i class="fa fa-check"></i> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="survey_type">Survey Type</label>
                            <select name="survey_type" id="survey_type" class="form-control" required>
                                <option value="">Select Survey Type</option>
                                <option value="student">Student Survey</option>
                                <option value="board">Board Survey</option>
                                <option value="staff">Staff Survey</option>
                                <option value="alumni">Alumni Survey</option>
                                <option value="guardian">Guardian Survey</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="section_name">Section Name</label>
                            <input type="text" name="section_name" id="section_name" class="form-control" 
                                   placeholder="e.g., School Environment, Facilities, Teachers" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Create Section
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Current Sections -->
        <div class="col-xs-12">
            <div class="box-content card white">
                <h4 class="box-title">Current Sections by Survey Type</h4>
                <div class="card-content">
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT type, name, COUNT(q.id) as question_count 
                        FROM categories c 
                        LEFT JOIN questions q ON c.id = q.category_id 
                        GROUP BY c.id, c.type, c.name 
                        ORDER BY c.type, c.name
                    ");
                    $stmt->execute();
                    $sections = $stmt->fetchAll();
                    
                    $currentType = '';
                    foreach ($sections as $section):
                        if ($currentType !== $section['type']):
                            if ($currentType !== '') echo '</ul>';
                            $currentType = $section['type'];
                            echo '<h5>' . ucfirst($currentType) . ' Survey</h5><ul>';
                        endif;
                    ?>
                        <li><?php echo htmlspecialchars($section['name']); ?> 
                            <span class="text-muted">(<?php echo $section['question_count']; ?> questions)</span>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($currentType !== '') echo '</ul>'; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php require_once '../includes/footer.php'; ?>
