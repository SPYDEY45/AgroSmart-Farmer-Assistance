<?php
/**
 * AgroSmart - Weather Information & Farming Advisory Module
 */
$pageTitle = 'Weather Advisory – AgroSmart';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

$city = trim($_GET['city'] ?? ($_SESSION['district'] ?? WEATHER_DEFAULT_CITY));
$weatherData = null;
$apiError = '';
$isLive = false;

// Check if WEATHER_API_KEY is configured in config/database.php
if (!empty(WEATHER_API_KEY)) {
    // OpenWeatherMap Current Weather Endpoint
    $encodedCity = urlencode($city . ',IN');
    $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q={$encodedCity}&units=metric&appid=" . WEATHER_API_KEY;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $json = json_decode($response, true);
        if ($json && isset($json['main'])) {
            $isLive = true;
            $weatherData = [
                'city' => $json['name'],
                'temp' => round($json['main']['temp']),
                'temp_min' => round($json['main']['temp_min']),
                'temp_max' => round($json['main']['temp_max']),
                'humidity' => $json['main']['humidity'],
                'wind_speed' => round($json['wind']['speed'] * 3.6, 1), // km/h
                'description' => ucfirst($json['weather'][0]['description'] ?? 'Clear'),
                'icon' => $json['weather'][0]['icon'] ?? '01d',
                'rain' => $json['rain']['1h'] ?? 0,
            ];
        }
    } else {
        $apiError = "Unable to fetch live weather data from OpenWeather API (HTTP Code: {$httpCode}). Showing offline regional agricultural forecast benchmark.";
    }
} else {
    $apiError = "No external WEATHER_API_KEY is configured in config/database.php. Showing educational district agronomy guidance.";
}

// Regional Benchmark Agricultural Weather Profiles for demonstration (clearly labeled)
$regionalProfiles = [
    'Pune' => ['temp' => 28, 'temp_min' => 21, 'temp_max' => 31, 'humidity' => 68, 'wind_speed' => 14, 'condition' => 'Partly Cloudy with Scattered Showers', 'rain_prob' => '40%', 'spray_advice' => 'Favorable for foliar spraying between 8 AM - 11 AM.'],
    'Nashik' => ['temp' => 26, 'temp_min' => 19, 'temp_max' => 30, 'humidity' => 74, 'wind_speed' => 12, 'condition' => 'Overcast with Morning Mist', 'rain_prob' => '30%', 'spray_advice' => 'Monitor vineyard downy mildew; spray systemic fungicides if humidity stays above 80%.'],
    'Nagpur' => ['temp' => 32, 'temp_min' => 24, 'temp_max' => 35, 'humidity' => 58, 'wind_speed' => 16, 'condition' => 'Sunny & Warm', 'rain_prob' => '15%', 'spray_advice' => 'Irrigate orange groves in evening to prevent moisture stress.'],
    'Latur' => ['temp' => 30, 'temp_min' => 22, 'temp_max' => 33, 'humidity' => 62, 'wind_speed' => 15, 'condition' => 'Clear Sky', 'rain_prob' => '10%', 'spray_advice' => 'Favorable for intercultural weed removal and pesticide application.'],
];

$matchedBenchmark = $regionalProfiles[$city] ?? $regionalProfiles['Pune'];
?>

<div class="row justify-content-center">
  <div class="col-lg-10">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
      <div>
        <h2 class="fw-bold text-success mb-1"><i class="bi bi-cloud-sun me-2"></i>Weather & Agrometeorology Advisory</h2>
        <p class="text-muted mb-0">Micro-climate observations and field operation spraying advisories</p>
      </div>
      <!-- Location Selector -->
      <form method="GET" action="<?= BASE_URL ?>/farmer/weather.php" class="d-flex mt-3 mt-md-0 gap-2">
        <select name="city" class="form-select">
          <?php foreach (['Pune', 'Nashik', 'Nagpur', 'Latur', 'Kolhapur', 'Solapur', 'Aurangabad', 'Amravati'] as $c): ?>
            <option value="<?= $c ?>" <?= $city === $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-agro-primary"><i class="bi bi-search"></i></button>
      </form>
    </div>

    <!-- Mandatory Weather Advisory Notice -->
    <div class="agro-notice-box">
      <div class="d-flex">
        <i class="bi bi-info-circle-fill text-warning fs-4 me-3"></i>
        <div>
          <strong><?= __('disclaimer_title') ?>:</strong>
          <p class="mb-0 mt-1"><?= __('weather_notice') ?></p>
        </div>
      </div>
    </div>

    <?php if ($isLive && $weatherData): ?>
      <!-- Live OpenWeather Result -->
      <div class="card card-agro text-white mb-4 border-0" style="background: linear-gradient(135deg, #0277bd 0%, #00838f 100%);">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="badge bg-light text-dark mb-2"><i class="bi bi-broadcast me-1"></i>Live Weather API Data</span>
              <h1 class="display-4 fw-bold mb-0"><?= $weatherData['temp'] ?>°C</h1>
              <h4 class="fw-normal"><?= e($weatherData['city']) ?></h4>
              <p class="mb-0 text-white-50"><?= e($weatherData['description']) ?></p>
            </div>
            <i class="bi bi-cloud-sun-fill display-1 text-warning opacity-75"></i>
          </div>

          <div class="row g-3 mt-3 text-center border-top border-white border-opacity-25 pt-3">
            <div class="col-3">
              <small class="d-block text-white-50">Humidity</small>
              <strong><?= $weatherData['humidity'] ?>%</strong>
            </div>
            <div class="col-3">
              <small class="d-block text-white-50">Wind Speed</small>
              <strong><?= $weatherData['wind_speed'] ?> km/h</strong>
            </div>
            <div class="col-3">
              <small class="d-block text-white-50">Min / Max</small>
              <strong><?= $weatherData['temp_min'] ?>° / <?= $weatherData['temp_max'] ?>°</strong>
            </div>
            <div class="col-3">
              <small class="d-block text-white-50">Precipitation</small>
              <strong><?= $weatherData['rain'] ?> mm</strong>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <!-- Demonstration & Educational Advisory Banner -->
      <div class="alert alert-warning py-2 mb-3 small d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <span><?= e($apiError) ?></span>
      </div>

      <div class="card card-agro text-white mb-4 border-0" style="background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="badge bg-warning text-dark mb-2"><i class="bi bi-mortarboard me-1"></i>Demonstration Agro-Meteorological Benchmark (<?= e($city) ?>)</span>
              <h1 class="display-4 fw-bold mb-0"><?= $matchedBenchmark['temp'] ?>°C</h1>
              <h4 class="fw-normal"><?= e($city) ?> District, Maharashtra</h4>
              <p class="mb-0 text-white-75"><?= e($matchedBenchmark['condition']) ?></p>
            </div>
            <i class="bi bi-cloud-rain-heavy-fill display-1 text-warning opacity-75"></i>
          </div>

          <div class="row g-3 mt-3 text-center border-top border-white border-opacity-25 pt-3">
            <div class="col-3">
              <small class="d-block text-white-75">Relative Humidity</small>
              <strong><?= $matchedBenchmark['humidity'] ?>%</strong>
            </div>
            <div class="col-3">
              <small class="d-block text-white-75">Wind Speed</small>
              <strong><?= $matchedBenchmark['wind_speed'] ?> km/h</strong>
            </div>
            <div class="col-3">
              <small class="d-block text-white-75">Min / Max Temp</small>
              <strong><?= $matchedBenchmark['temp_min'] ?>°C / <?= $matchedBenchmark['temp_max'] ?>°C</strong>
            </div>
            <div class="col-3">
              <small class="d-block text-white-75">Rain Probability</small>
              <strong><?= $matchedBenchmark['rain_prob'] ?></strong>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Field Operation Advisory Cards -->
    <h5 class="fw-bold text-success mb-3"><i class="bi bi-tools me-2"></i>Field Operation Guidance for Today</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <div class="card card-agro p-3 h-100 border-start border-success border-4">
          <h6 class="fw-bold text-success"><i class="bi bi-droplet-half me-1"></i>Spraying & Crop Protection Advisory</h6>
          <p class="small text-muted mb-0"><?= e($matchedBenchmark['spray_advice'] ?? 'Ensure winds are below 15 km/h before spraying insecticides.') ?></p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card card-agro p-3 h-100 border-start border-primary border-4">
          <h6 class="fw-bold text-primary"><i class="bi bi-water me-1"></i>Irrigation Scheduling Advisory</h6>
          <p class="small text-muted mb-0">With current humidity, evapotranspiration is moderate. Drip irrigation of 2-3 hours in early morning is optimal for standing crops.</p>
        </div>
      </div>
    </div>

    <!-- API Configuration Guide for BCA Project Viva -->
    <div class="card card-agro p-3 mt-4 bg-light">
      <h6 class="fw-bold text-dark mb-1"><i class="bi bi-code-slash me-2"></i>How to Connect Real Live Weather API for Project Demonstration</h6>
      <p class="small text-muted mb-2">
        To demonstrate live external API calls during your BCA viva examination:
      </p>
      <ol class="small text-muted mb-0 ps-3">
        <li>Sign up for a free API Key at <a href="https://openweathermap.org/api" target="_blank">openweathermap.org</a>.</li>
        <li>Open <code>config/database.php</code> and set <code>define('WEATHER_API_KEY', 'your_actual_key_here');</code>.</li>
        <li>The weather page will automatically detect the key, perform live cURL requests, and display live satellite telemetry!</li>
      </ol>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
