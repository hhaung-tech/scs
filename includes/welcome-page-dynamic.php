<?php
// Get database connection
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/survey-helper.php';

// Get active surveys
$activeSurveys = getActiveSurveys($pdo);

// Define color classes for different survey types
$colorClasses = [
    'student' => 'blue',
    'alumni' => 'orange', 
    'board' => 'purple',
    'staff' => 'green',
    'guardian' => 'teal'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISY School Climate Survey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="services-section">
        <div class="container">
            <div class="text-center mb-4">
                <img src="/isy_scs_ai/assets/images/isy_logo.png" alt="ISY Logo" class="logo-img">
                <h2 class="section-title">ISY School Climate Survey</h2>
            </div>
            <p class="section-description">Help us create a better learning environment by participating in our<strong> anonymous school climate survey</strong>.</p>
            
            <?php if (empty($activeSurveys)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <h4>No Active Surveys</h4>
                            <p>There are currently no active surveys available. Please check back later.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($activeSurveys as $survey): ?>
                        <div class="col-md-6 mb-4">
                            <div class="service-card">
                                <div class="icon-box <?php echo $colorClasses[$survey['survey_type']] ?? 'blue'; ?>">
                                    <i class="fa <?php echo htmlspecialchars($survey['icon_class']); ?>"></i>
                                </div>
                                <div class="service-content">
                                    <h3><?php echo htmlspecialchars($survey['display_name']); ?></h3>
                                    <p><?php echo htmlspecialchars($survey['description']); ?></p>
                                    <a href="<?php echo htmlspecialchars($survey['survey_type']); ?>/survey.php" class="learn-more">
                                        Learn More <i class="fa fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<style>
.services-section {
    min-height: 100vh;
    width: 100vw;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(rgba(233, 233, 220, 0.95), rgba(220, 180, 30, 0.95));
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
    padding: 2rem;
}

.container {
    max-width: 1200px;
    width: 100%;
    margin: 0 auto;
    overflow: hidden;
}

.logo-img {
    width: 80px;
    height: auto;
    margin-bottom: 1rem;
}

.text-center {
    text-align: center;
}

.section-title {
    font-size: 2rem;
    font-weight: 600;
    color: #1a365d;
    margin-bottom: 0.5rem;
}

.section-description {
    font-size: 1rem;
    color: #64748b;
    margin-bottom: 3rem;
    text-align: center;
}

.row {
    max-width: 1000px;
    margin: 0 auto;
}

.service-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    padding: 1.5rem;
    border-radius: 12px;
    border: none;
    height: 100%;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.service-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

.icon-box {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.icon-box.blue { background: #2563eb; }
.icon-box.orange { background: #ea580c; }
.icon-box.green { background: #059669; }
.icon-box.purple { background: #7c3aed; }
.icon-box.teal { background: #0d9488; }

.service-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.service-content p {
    margin-bottom: 1rem;
    flex: 1;
}

.learn-more {
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.learn-more:hover {
    color: #1d4ed8;
}

.learn-more i {
    font-size: 0.9rem;
    transition: transform 0.2s ease;
}

.learn-more:hover i {
    transform: translateX(4px);
}

@media (max-width: 768px) {
    .services-section {
        padding: 1.5rem;
    }
    
    .section-title {
        font-size: 1.75rem;
    }

    .service-card {
        padding: 1.25rem;
    }
}
</style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
