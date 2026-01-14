<?php include __DIR__ . '/header.php'; ?>

<style>
    .survey-code-page {
        min-height: 100vh;
        background: linear-gradient(135deg, rgba(233, 233, 220, 0.95), rgba(220, 180, 30, 0.95));
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 12px;
    }
    .survey-code-card {
        max-width: 520px;
        width: 100%;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 18px 50px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.06);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
    }
    .survey-code-header {
        padding: 22px 22px 10px 22px;
        text-align: center;
    }
    .survey-code-logo {
        width: 72px;
        height: auto;
        display: inline-block;
        margin-bottom: 10px;
    }
    .survey-code-title {
        margin: 0;
        font-weight: 800;
        color: #1f2937;
        font-size: 18px;
        letter-spacing: 0.2px;
    }
    .survey-code-subtitle {
        margin: 8px 0 0 0;
        color: #6b7280;
        font-size: 13px;
    }
    .survey-code-body {
        padding: 18px 22px 22px 22px;
    }
    .survey-code-actions {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        margin-top: 14px;
    }
    @media (min-width: 480px) {
        .survey-code-actions {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>

<div class="survey-code-page">
    <div class="survey-code-card">
        <div class="survey-code-header">
            <img src="/isy_scs_ai/assets/images/isy_logo.png" alt="ISY Logo" class="survey-code-logo">
            <h4 class="survey-code-title"><?php echo ucfirst($surveyType); ?> Survey Access</h4>
            <p class="survey-code-subtitle">Enter your access code to start the survey.</p>
        </div>
        <div class="survey-code-body">
            <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_code'): ?>
                <div class="alert alert-danger" style="margin-bottom: 14px;">
                    <i class="fa fa-warning"></i> Invalid access code. Please try again.
                </div>
            <?php endif; ?>

            <form action="../admin/verify-survey-code.php" method="POST">
                <div class="form-group" style="margin-bottom: 10px;">
                    <label style="font-weight: 700; color: #374151;">Access Code</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-key"></i></span>
                        <input type="text" name="survey_code" class="form-control" placeholder="Enter Access Code" required autocomplete="one-time-code">
                    </div>
                </div>

                <input type="hidden" name="survey_type" value="<?php echo htmlspecialchars($surveyType); ?>">
                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

                <div class="survey-code-actions">
                    <button type="submit" class="btn btn-warning waves-effect waves-light" style="width: 100%;">
                        Start Survey <i class="fa fa-arrow-right"></i>
                    </button>
                    <a href="../index.php" class="btn btn-default waves-effect waves-light" style="width: 100%;">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
