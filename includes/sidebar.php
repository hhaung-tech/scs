<?php
if (!isset($_SESSION)) {
    session_start();
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
                <li>
                    <a class="waves-effect parent-item js__control" href="#"
                    ><i class="menu-icon fa fa-flag"></i><span>Survey Questions</span
                    ><span class="menu-arrow fa fa-angle-down"></span
                    ></a>
                    <ul class="sub-menu js__content">
                        <li>
                            <a href="/isy_scs_ai/admin/student/questions.php">Student</a>
                        </li>
                        <li>
                            <a href="/isy_scs_ai/admin/alumni/questions.php">Alumni Survey</a>
			</li>
			<li>
                            <a href="/isy_scs_ai/admin/board/questions.php">Board Survey</a>
                        </li>
                        <!-- <li>
                            <a href="/isy_scs_ai/admin/guardian/questions.php">Guardian</a>
                        </li>
                        <li>
                            <a href="/isy_scs_ai/admin/staff/questions.php">Staff</a>
                        </li> -->
                    </ul>
                </li>
                <li>
                    <a class="waves-effect parent-item js__control" href="#"
                    ><i class="menu-icon fa fa-bar-chart"></i><span>Survey Analytics</span
                    ><span class="menu-arrow fa fa-angle-down"></span
                    ></a>
                    <ul class="sub-menu js__content">
                    <li>
                        <a href="/isy_scs_ai/admin/student/analytics.php">Student</a>
                    </li>
                    <li>
                        <a href="/isy_scs_ai/admin/student/teacher-analytics.php">Teacher Feedback</a>
                    </li>
                    <li>
                        <a href="/isy_scs_ai/admin/alumni/analytics-ai.php">AI Alumni</a>
		    </li>
		 	<li>
                        <a href="/isy_scs_ai/admin/board/analytics-ai.php">AI Board</a>
                    </li>
                    <!-- <li>
                        <a href="/isy_scs_ai/admin/guardian/analytics.php">Guardian</a>
                    </li>
                    <li>
                        <a href="/isy_scs_ai/admin/staff/analytics.php">Staff</a>
                    </li> -->
                    </ul>
                    <!-- /.sub-menu js__content -->
                </li>
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
                        <a href="/isy_scs_ai/admin/survey-code.php">Survey Code </a>
                    </li>
                    </ul>
                    <!-- /.sub-menu js__content -->
                </li>
                <li>
                    <a class="waves-effect parent-item js__control" href="#"
                    ><i class="menu-icon fa fa-users"></i><span>Users Management</span
                    ><span class="menu-arrow fa fa-angle-down"></span
                    ></a>
                    <ul class="sub-menu js__content">
                    <li>
                        <a href="/isy_scs_ai/admin/users.php">Admin Users </a>
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
