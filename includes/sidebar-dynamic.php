<?php
if (!isset($_SESSION)) {
    session_start();
}

// Include database and survey helper with error handling
try {
    if (!isset($pdo)) {
        require_once __DIR__ . '/../config/database.php';
    }
    require_once __DIR__ . '/survey-helper.php';
    
    // Get active surveys for dynamic menu generation
    $activeSurveys = getActiveSurveys($pdo);
} catch (Exception $e) {
    // Fallback to empty array if there's an error
    $activeSurveys = [];
    error_log("Sidebar error: " . $e->getMessage());
}
?>
<div class="wrapper">
    <div class="main-menu">
        <header class="header">
        <a href="/isy_scs_ai/admin/index.php" class="logo">School Climate Survey</a>
        <button
            type="button"
            class="button-close fa fa-times js__menu_close"
        ></button>
        <div class="user">
            <a href="#" class="avatar">
                <img src="/isy_scs_ai/assets/images/user.png" alt="User Avatar">
                <span class="status online"></span>
            </a>
            <h5 class="name">
                    <a href="/isy_scs_ai/admin/profile.php">
                        <?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Administrator'; ?>
                    </a>
                </h5>
            <h5 class="position">Administrator</h5>
        </div>
        <!-- /.user -->
        </header>
        <!-- /.header -->
        <div class="content">
        <div class="navigation">
            <h5 class="title">Navigation</h5>
            <!-- /.title -->
            <ul class="menu js__accordion">
                <li class="current">
                    <a class="waves-effect" href="/isy_scs_ai/admin/index.php"
                    ><i class="menu-icon fa fa-home"></i><span>Dashboard</span></a
                    >
                </li>
                
                <!-- Dynamic Survey Questions Menu -->
                <?php if (!empty($activeSurveys)): ?>
                <li>
                    <a class="waves-effect parent-item js__control" href="#"
                    ><i class="menu-icon fa fa-flag"></i><span>Survey Questions</span
                    ><span class="menu-arrow fa fa-angle-down"></span
                    ></a>
                    <ul class="sub-menu js__content">
                        <?php foreach ($activeSurveys as $survey): ?>
                        <li>
                            <a href="/isy_scs_ai/admin/<?php echo $survey['survey_type']; ?>/questions.php">
                                <?php echo htmlspecialchars($survey['display_name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Dynamic Survey Analytics Menu -->
                <?php if (!empty($activeSurveys)): ?>
                <!-- Regular Survey Analytics -->
                <li>
                    <a class="waves-effect parent-item js__control" href="#"
                    ><i class="menu-icon fa fa-bar-chart"></i><span>Survey Analytics</span
                    ><span class="menu-arrow fa fa-angle-down"></span
                    ></a>
                    <ul class="sub-menu js__content">
                    <?php foreach ($activeSurveys as $survey): ?>
                        <li>
                            <a href="/isy_scs_ai/admin/<?php echo $survey['survey_type']; ?>/analytics.php">
                                <?php echo htmlspecialchars($survey['display_name']); ?> Analytics
                            </a>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </li>

                <!-- AI Survey Analytics -->
                <li>
                    <a class="waves-effect parent-item js__control" href="#"
                    ><i class="menu-icon fa fa-magic"></i><span>AI Survey Analytics</span
                    ><span class="menu-arrow fa fa-angle-down"></span
                    ></a>
                    <ul class="sub-menu js__content">
                    <?php foreach ($activeSurveys as $survey): ?>
                        <li>
                            <a href="/isy_scs_ai/admin/<?php echo $survey['survey_type']; ?>/analytics-ai.php">
                                AI <?php echo htmlspecialchars($survey['display_name']); ?> Insights
                            </a>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <li>
                    <a class="waves-effect parent-item js__control" href="#"
                    ><i class="menu-icon fa fa-line-chart"></i><span>Yearly Insights Survey</span
                    ><span class="menu-arrow fa fa-angle-down"></span
                    ></a>
                    <ul class="sub-menu js__content">
                    <li>
                        <a href="/isy_scs_ai/admin/yearly-analytics.php">Comparison Report</a>
                    </li>
                    </ul>
                    <!-- /.sub-menu js__content -->
                </li>
                
                <li>
                    <a class="waves-effect parent-item js__control" href="#"
                    ><i class="menu-icon fa fa-code"></i><span>Manage Code Access</span
                    ><span class="menu-arrow fa fa-angle-down"></span
                    ></a>
                    <ul class="sub-menu js__content">
                    <li>
                        <a href="/isy_scs_ai/admin/survey-code.php">Survey Code</a>
                    </li>
                    </ul>
                    <!-- /.sub-menu js__content -->
                </li>
                
                <!-- Survey Management -->
                <li>
                    <a class="waves-effect parent-item js__control" href="#"
                    ><i class="menu-icon fa fa-cogs"></i><span>Survey Management</span
                    ><span class="menu-arrow fa fa-angle-down"></span
                    ></a>
                    <ul class="sub-menu js__content">
                        <li><a href="/isy_scs_ai/admin/manage-questions-modern.php">Question Manager</a></li>
                        <li><a href="/isy_scs_ai/admin/survey-settings.php">Survey Settings</a></li>
                    </ul>
                </li>
                
                <li>
                    <a class="waves-effect parent-item js__control" href="#"
                    ><i class="menu-icon fa fa-users"></i><span>Users Management</span
                    ><span class="menu-arrow fa fa-angle-down"></span
                    ></a>
                    <ul class="sub-menu js__content">
                    <li>
                        <a href="/isy_scs_ai/admin/users.php">Admin Users</a>
                    </li>
                    <li>
                        <a href="/isy_scs_ai/admin/profile.php">Profile</a>
                    </li>
                    </ul>
                    <!-- /.sub-menu js__content -->
                </li>
    
            </ul>
            <!-- /.menu js__accordion -->
        </div>
        <!-- /.navigation -->
        </div>
        <!-- /.content -->
    </div>
    <!-- /.main-menu -->
    <div class="fixed-navbar">
        <div class="pull-left">
        <button
            type="button"
            class="menu-mobile-button glyphicon glyphicon-menu-hamburger js__menu_mobile"
        ></button>
        <h1 class="page-title">Home</h1>
        </div>
        <!-- /.pull-left -->
        <div class="pull-right">
            <a href="/isy_scs_ai/admin/logout.php" class="ico-item fa fa-power-off"></a>
        </div>
    </div>  <!-- /.pull-right -->
</div>
<!-- end wrapper -->
