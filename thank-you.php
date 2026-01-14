<?php include 'includes/header.php'; ?>

<div class="thank-you-container">
    <div class="thank-you-content">
        <i class="fa fa-check-circle"></i>
        <h1>Thank You!</h1>
        <p>Your survey response has been successfully recorded.</p>
        <p>You will be redirected to the home page in <span id="countdown">10</span> seconds.</p>
        <a href="/" class="home-button">Return to Home</a>
    </div>
</div>

<style>
.thank-you-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    padding: 20px;
}

.thank-you-content {
    text-align: center;
    background: white;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    max-width: 500px;
    width: 100%;
}

.fa-check-circle {
    color: #2ecc71;
    font-size: 64px;
    margin-bottom: 20px;
}

.home-button {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 25px;
    background: #3498db;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.home-button:hover {
    background: #2980b9;
    transform: translateY(-2px);
}
</style>

<script>
let seconds = 10;
const countdownElement = document.getElementById('countdown');
const interval = setInterval(() => {
    seconds--;
    countdownElement.textContent = seconds;
    if (seconds <= 0) {
        clearInterval(interval);
        window.location.href = '/';
    }
}, 1000);
</script>

<?php include 'includes/footer.php'; ?>
