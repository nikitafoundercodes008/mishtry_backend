<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Success Animation</title>
  <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
  <style>
    /* Reset styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    /* Body styles */
    body {
      text-align: center;
      padding: 40px 0;
      background: #EBF0F5; /* Light gray background */
      font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
    }

    /* Card styles */
    .card {
      background: white;
      padding: 60px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      display: inline-block;
      margin: 0 auto;
      text-align: center;
    }

    /* Heading styles */
    h1 {
      color: #88B04B;
      font-weight: 900;
      font-size: 40px;
      margin-bottom: 10px;
    }
    
    /* Paragraph styles */
    p {
      color: #404F5E;
      font-size: 18px;
      margin: 10px 0;
    }
 
    /* Lottie animation container */
    .animation-container {
      margin: 10px auto;
    } 
    
    /* Custom styles for animation player */
    dotlottie-player { 
      width: 400px; /* Adjust size as needed */
      height: 270px;
    }
  </style>
</head>
<body>
  <!-- Card Section -->
  <div class="card">
    <!-- Lottie Animation -->
    <div class="animation-container">
      <dotlottie-player 
        src="https://lottie.host/743650e7-51dc-42cb-8ec0-4d9cdf32542d/qwKFt4V60W.lottie" 
        background="transparent" 
        speed="1" 
        loop 
        autoplay>
      </dotlottie-player>
    </div>
    <!-- Success Message -->
    <h1>Success</h1>
    <p>We received your purchase request;<br> we'll be in touch shortly!</p>
  </div>
</body>
</html>
