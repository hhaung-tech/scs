<?php include 'header.php'; ?>

<div id="single-wrapper">
    <div class="main-content" style="margin:0;">
        <div class="row small-spacing">
            <div class="col-md-6 col-md-offset-3">
                <div class="box-content card white">
		            <h4 class="box-title" style="text-align:center;"><?php echo ucfirst($surveyType); ?> Survey Access</h4>
                    <div class="card-content">
                        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_code'): ?>
                            <div class="alert alert-danger">
                                <i class="fa fa-warning"></i> Invalid access code. Please try again.
                            </div>
                        <?php endif; ?>

                        <form action="../admin/verify-survey-code.php" method="POST" class="form-horizontal">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                        <input type="text" name="survey_code" class="form-control" placeholder="Enter Access Code" required>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="survey_type" value="<?php echo htmlspecialchars($surveyType); ?>">
                            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                            
                            <div class="form-group margin-top-20">
                                <div class="col-sm-12 text-center">
                                    <button type="submit" class="btn btn-warning btn-sm waves-effect waves-light">
                                        Start Survey <i class="fa fa-arrow-right"></i>
                                    </button>
                                    <a href="../index.php" class="btn btn-default btn-sm waves-effect waves-light">
                                        <i class="fa fa-arrow-left"></i> Back
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
