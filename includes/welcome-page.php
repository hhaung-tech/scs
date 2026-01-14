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
                <img src="/../assets/images/isy_logo.png" alt="ISY Logo" class="logo-img">
                <h2 class="section-title">ISY School Climate Survey</h2>
            </div>
            <p class="section-description">Help us create a better learning environment by participating in our<strong> anonymous school climate survey</strong>.</p>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="service-card">
                        <div class="icon-box blue">
                            <i class="fa fa-child"></i>
                        </div>
                        <div class="service-content">
                            <h3>Student Survey</h3>
                            <p>Share your valuable insights about your school experience. Your feedback helps us create a better learning environment for everyone.</p>
                            <a href="student/survey.php" class="learn-more">Learn More <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="service-card">
                        <div class="icon-box orange">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
                        <div class="service-content">
                            <h3>Alumni Survey</h3>
                            <p>Your post-graduation experience matters. Help us understand how we can better prepare current students for their future.</p>
                            <a href="alumni/survey.php" class="learn-more">Learn More <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
		        </div>
                <div class="col-md-6 mb-4">
                        <div class="service-card">
                            <div class="icon-box purple">
                                <i class="fa fa-industry"></i>
                            </div>
                            <div class="service-content">
                                <h3>Board Survey</h3>
                                <p>Board members play a crucial role in shaping the institution’s future. Share your insights to help guide strategic decisions and ensure continuous improvement.</p>
                                <a href="board/survey.php" class="learn-more">Learn More <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>

                <!-- <div class="col-md-6 mb-4">
                    <div class="service-card">
                        <div class="icon-box green">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div class="service-content">
                            <h3>Guardian Survey</h3>
                            <p>Parents and guardians, your perspective is crucial. Share your thoughts on your child's educational experience.</p>
                            <a href="guardian/survey.php" class="learn-more">Learn More <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="service-card">
                        <div class="icon-box purple">
                            <i class="fa fa-industry"></i>
                        </div>
                        <div class="service-content">
                            <h3>Staff Survey</h3>
                            <p>School staff insights are essential for improvement. Share your professional perspective on our educational environment.</p>
                            <a href="staff/survey.php" class="learn-more">Learn More <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div> -->
            </div>
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
