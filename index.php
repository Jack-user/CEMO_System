<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Landing Page</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
    }
    h1 {
    font-size: 48px;
    white-space: nowrap;
    }
    .hero {
      background:
        linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
        url('includes/bagoo.jpg') no-repeat center center/cover;
      color: white;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      text-align: center;
      padding: 20px;
    }
    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .nav-links {
      list-style: none;
      display: flex;
      gap: 15px;
    }
    .nav-links a {
      color: white;
      text-decoration: none;
    }
    .hero-content {
      margin-top: auto;
      color: blue;
    }
    .cta-button {
      padding: 15px 30px;
      font-size: 15px; 
      background-color: blue;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      margin-top: 15px;
      display: inline-block;
    }
    .features {
      padding: 50px 20px;
      text-align: center;
    }
    .feature-cards {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 20px;
    }
    .card {
      background-color: #f4f4f4;
      padding: 30px;
      width: 200px;
      border-radius: 10px;
    }
    footer {
      background-color: #222;
      color: white;
      text-align: center;
      padding: 20px;
    }
    .intro {
      padding: 60px 20px;
      text-align: center;
      background-color: #f9f9f9;
    }

    .intro-content {
  max-width: 700px;
  margin: 230px auto 0 auto;
  color: white;
  text-align: center;
  align-items: center;
}

.intro-content h3 {
  font-size: 28px;
  margin-bottom: 10px;
}

.intro-content p {
  font-size: 18px;
  line-height: 1.5;
}


  </style>
</head>
<body>
  <header class="hero">
  <nav>
    <h2 class="logo">CEMO</h2>
    <ul class="nav-links">
      <li><a href="#">Home</a></li>
      <li><a href="#">Features</a></li>
      <li><a href="#">Contact</a></li>
    </ul>
  </nav>

  <!-- ✅ About content goes here ABOVE the hero content -->
  <div class="intro-content">
    <h1 style="font-size: 39px;">Waste Management & Tracking System</h1>
    <p>
      Smarter Waste Collection with Predictive Analytics and Real-Time Monitoring.
    </p>
  </div>

  <div class="hero-content">
    <a href="./login_page/sign-in.php" class="cta-button" style="background-color: blue;">
  Get Started
</a>
  </div>
</header>


  <!-- ✅ Intro section moved here -->
 <section class="features">
  <h3>Features</h3>
  <div class="feature-cards">
    <div class="card">Feature 1</div>
    <div class="card">Feature 2</div>
    <div class="card">Feature 3</div>
  </div>
</section>


  <footer>
    <p>&copy; 2025 MyBrand. All rights reserved.</p>
  </footer>
</body>

</html>
