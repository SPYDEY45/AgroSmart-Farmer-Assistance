<?php
/**
 * AgroSmart - Helper and Business Logic Functions
 * Clean, documented functions suitable for BCA viva presentation.
 */

require_once __DIR__ . '/../config/database.php';

// Load language translation dictionary
function getTranslations() {
    static $langData = null;
    if ($langData === null) {
        $lang = $_SESSION['lang'] ?? 'en';
        $file = __DIR__ . "/lang/{$lang}.php";
        if (file_exists($file)) {
            $langData = require $file;
        } else {
            $langData = require __DIR__ . '/lang/en.php';
        }
    }
    return $langData;
}

/**
 * Translate key with fallback
 */
function __($key, $default = '') {
    $dict = getTranslations();
    return $dict[$key] ?? ($default ?: $key);
}

/**
 * Sanitize output against XSS
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF Token
 */
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash messaging helpers
 */
function setFlash($type, $message) {
    $_SESSION['flash_type'] = $type; // success, danger, warning, info
    $_SESSION['flash_message'] = $message;
}

function displayFlash() {
    if (isset($_SESSION['flash_message'])) {
        $type = e($_SESSION['flash_type'] ?? 'info');
        $msg = e($_SESSION['flash_message']);
        unset($_SESSION['flash_type'], $_SESSION['flash_message']);
        echo "<div class='alert alert-{$type} alert-dismissible fade show shadow-sm my-3' role='alert'>
                <span>{$msg}</span>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

/**
 * Validate and upload product photo securely
 * Returns uploaded filename or false with error message
 */
function uploadProductPhoto($file, &$errorMessage) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = "No file selected or an upload error occurred.";
        return false;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 3 * 1024 * 1024; // 3MB

    if ($file['size'] > $maxSize) {
        $errorMessage = "Uploaded file size exceeds 3MB limit.";
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        $errorMessage = "Only JPG, JPEG, PNG and WebP images are allowed.";
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimes)) {
        $errorMessage = "Invalid image file format detected.";
        return false;
    }

    $uploadDir = __DIR__ . '/../uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFilename = 'prod_' . uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $uploadDir . $newFilename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newFilename;
    } else {
        $errorMessage = "Failed to save uploaded file on server.";
        return false;
    }
}

/**
 * Smart Crop Recommendation Rule-Based Engine
 * Kept in a dedicated modular function so it can later be replaced with an ML model / microservice.
 *
 * Inputs:
 * - season: Kharif, Rabi, Zaid, Annual
 * - soil_type: Black Soil, Loamy Soil, Red Soil, Sandy Loam, Clay Loam
 * - water_availability: Rainfed, Canal, Well/Borewell, Drip Irrigation
 * - land_area: Numeric in acres
 */
function recommendCrops($season, $soil_type, $water_availability, $land_area = 1.0) {
    $recommendations = [];

    // Kharif recommendations
    if ($season === 'Kharif') {
        if (str_contains($soil_type, 'Black')) {
            if ($water_availability === 'Canal' || $water_availability === 'Drip Irrigation') {
                $recommendations[] = [
                    'crop' => 'Cotton',
                    'suitability' => 'High',
                    'reason' => 'Deep black soil retains moisture essential for boll formation; assured water ensures top lint yield.',
                    'expected_yield' => (10 * $land_area) . ' - ' . (15 * $land_area) . ' Quintals',
                    'ideal_practices' => 'Sow in late June, spacing 90cm x 60cm with integrated pest management for bollworm.'
                ];
                $recommendations[] = [
                    'crop' => 'Soybean',
                    'suitability' => 'Very High',
                    'reason' => 'Black soil provides strong nutrient base for legume nodules; moderate harvest cycle (90-100 days).',
                    'expected_yield' => (8 * $land_area) . ' - ' . (12 * $land_area) . ' Quintals',
                    'ideal_practices' => 'Seed treatment with Rhizobium and Trichoderma, broad bed furrow method.'
                ];
            } else {
                $recommendations[] = [
                    'crop' => 'Tur (Pigeon Pea)',
                    'suitability' => 'High',
                    'reason' => 'Deep taproot system thrives even under rainfed or intermittent dry spells in black soil.',
                    'expected_yield' => (6 * $land_area) . ' - ' . (9 * $land_area) . ' Quintals',
                    'ideal_practices' => 'Intercrop with soybean or green gram; provide 1-2 protective irrigations if rain fails.'
                ];
                $recommendations[] = [
                    'crop' => 'Soybean',
                    'suitability' => 'Medium-High',
                    'reason' => 'Performs well under rainfed conditions if monsoon onset is timely (>100mm rain received).',
                    'expected_yield' => (6 * $land_area) . ' - ' . (9 * $land_area) . ' Quintals',
                    'ideal_practices' => 'Maintain 30cm row spacing; apply pre-emergence herbicide.'
                ];
            }
        } elseif (str_contains($soil_type, 'Loam') || str_contains($soil_type, 'Red')) {
            $recommendations[] = [
                'crop' => 'Maize',
                'suitability' => 'High',
                'reason' => 'Loamy and well-drained red soils prevent waterlogging and promote excellent root aeration for maize.',
                'expected_yield' => (18 * $land_area) . ' - ' . (24 * $land_area) . ' Quintals',
                'ideal_practices' => 'Hybrid seeds with balanced NPK (120:60:40 kg/ha); monitor early for fall armyworm.'
            ];
            $recommendations[] = [
                'crop' => 'Groundnut / Peanuts',
                'suitability' => 'Medium',
                'reason' => 'Friable soil permits smooth pegging and pod expansion without soil compaction.',
                'expected_yield' => (7 * $land_area) . ' - ' . (11 * $land_area) . ' Quintals',
                'ideal_practices' => 'Gypsum application at 30 days after sowing enhances pod filling and shell hardening.'
            ];
        } else {
            $recommendations[] = [
                'crop' => 'Bajra (Pearl Millet)',
                'suitability' => 'High',
                'reason' => 'Highly hardy coarse cereal suitable for sandy or light soils with minimal water requirements.',
                'expected_yield' => (10 * $land_area) . ' - ' . (14 * $land_area) . ' Quintals',
                'ideal_practices' => 'Fast maturity (80-85 days), drought-tolerant, low chemical fertilizer requirement.'
            ];
        }
    }
    // Rabi recommendations
    elseif ($season === 'Rabi') {
        if (str_contains($soil_type, 'Loam') || str_contains($soil_type, 'Black')) {
            if ($water_availability !== 'Rainfed') {
                $recommendations[] = [
                    'crop' => 'Wheat',
                    'suitability' => 'Very High',
                    'reason' => 'Cool winter temperatures with fertile loam or clay loam support high tillering and grain weight.',
                    'expected_yield' => (15 * $land_area) . ' - ' . (22 * $land_area) . ' Quintals',
                    'ideal_practices' => 'Ensure 4-5 irrigations with first irrigation strictly at CRI stage (21 DAS).'
                ];
                $recommendations[] = [
                    'crop' => 'Onion (Late Kharif / Rabi)',
                    'suitability' => 'High',
                    'reason' => 'High market commercial value; well-drained loam provides round bulb development.',
                    'expected_yield' => (100 * $land_area) . ' - ' . (150 * $land_area) . ' Quintals',
                    'ideal_practices' => 'Raised bed transplanting with micro-sprinkler or drip irrigation.'
                ];
            } else {
                $recommendations[] = [
                    'crop' => 'Gram (Chickpea / Chana)',
                    'suitability' => 'High',
                    'reason' => 'Ideal rabi pulse capable of utilizing subsoil residual moisture in deep black soils.',
                    'expected_yield' => (7 * $land_area) . ' - ' . (11 * $land_area) . ' Quintals',
                    'ideal_practices' => 'Sow in October; single protective irrigation at pod formation increases yield by 30%.'
                ];
                $recommendations[] = [
                    'crop' => 'Safflower (Kardi)',
                    'suitability' => 'Medium',
                    'reason' => 'Deep taproot extracts deep moisture; spiny leaves naturally minimize transpiration.',
                    'expected_yield' => (5 * $land_area) . ' - ' . (8 * $land_area) . ' Quintals',
                    'ideal_practices' => 'Sow in late September to mid October.'
                ];
            }
        } else {
            $recommendations[] = [
                'crop' => 'Mustard',
                'suitability' => 'High',
                'reason' => 'Short duration rabi oilseed, tolerates lighter soils and limited water supply.',
                'expected_yield' => (6 * $land_area) . ' - ' . (9 * $land_area) . ' Quintals',
                'ideal_practices' => 'Sow in October; 2 irrigations during flowering and pod development.'
            ];
        }
    }
    // Zaid / Summer or Annual
    else {
        if ($water_availability === 'Canal' || $water_availability === 'Well/Borewell' || $water_availability === 'Drip Irrigation') {
            $recommendations[] = [
                'crop' => 'Tomato',
                'suitability' => 'High',
                'reason' => 'High commercial market demand; performs well with drip irrigation and staking in fertile loam.',
                'expected_yield' => (180 * $land_area) . ' - ' . (250 * $land_area) . ' Quintals',
                'ideal_practices' => 'Use hybrid disease-resistant seeds, drip fertigation and plastic mulching.'
            ];
            $recommendations[] = [
                'crop' => 'Watermelon / Muskmelon',
                'suitability' => 'High',
                'reason' => 'Warm sunny weather promotes high sugar content (Brix) and rapid fruit maturity in 70-80 days.',
                'expected_yield' => (120 * $land_area) . ' - ' . (180 * $land_area) . ' Quintals',
                'ideal_practices' => 'Drip irrigation with silver-black mulch; bee hives in vicinity improve fruit set.'
            ];
        } else {
            $recommendations[] = [
                'crop' => 'Green Gram (Summer Moong)',
                'suitability' => 'Medium',
                'reason' => 'Short 60-65 days catch crop that fixes nitrogen before main kharif season.',
                'expected_yield' => (4 * $land_area) . ' - ' . (6 * $land_area) . ' Quintals',
                'ideal_practices' => 'Requires 2-3 light irrigations; harvest as soon as 80% pods turn black.'
            ];
        }
    }

    return $recommendations;
}
