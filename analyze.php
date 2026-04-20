<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
use Smalot\PdfParser\Parser;

$OPENAI_API_KEY = getenv("OPENAI_API_KEY");

// INPUT
$resumeText = $_POST['resume'] ?? '';
$job = $_POST['job'] ?? '';

// FILE UPLOAD
if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === 0) {
    $fileTmp = $_FILES['resume_file']['tmp_name'];
    $fileName = $_FILES['resume_file']['name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($ext === "pdf") {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($fileTmp);
        $resumeText = $pdf->getText();
    } elseif ($ext === "txt") {
        $resumeText = file_get_contents($fileTmp);
    }
}

// VALIDATION
if (trim($resumeText) === '' || trim($job) === '') {
    die("Resume and Job Description are required.");
}

// LIMIT SIZE
$resumeText = substr($resumeText, 0, 4000);
$job = substr($job, 0, 2000);

// SKILLS
$skills = ["python","java","sql","html","css","javascript","react","aws","docker"];

function findSkills($text, $skills) {
    $found = [];
    foreach ($skills as $skill) {
        if (stripos($text, $skill) !== false) {
            $found[] = $skill;
        }
    }
    return $found;
}

$resumeSkills = findSkills($resumeText, $skills);
$jobSkills = findSkills($job, $skills);

$matched = array_intersect($resumeSkills, $jobSkills);
$missing = array_diff($jobSkills, $resumeSkills);

$score = round(count($matched) / max(count($jobSkills), 1) * 100);

// LABEL
if ($score > 80) $label = "Excellent Match";
elseif ($score > 60) $label = "Good Match";
elseif ($score > 40) $label = "Moderate Match";
else $label = "Weak Match";

// AI
$aiOutput = "AI unavailable.";

if ($OPENAI_API_KEY) {

    $prompt = "You are an expert career coach.

Analyze this resume vs job description.

Return:
1. Match score (0-100)
2. 3 strengths
3. 3 missing skills
4. 3 improvement suggestions

Resume:
$resumeText

Job:
$job";

    $data = [
        "model" => "gpt-4.1-mini",
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ]
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $OPENAI_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode == 200) {
        $result = json_decode($response, true);
        $aiOutput = $result['choices'][0]['message']['content'] ?? "AI error.";
    }
}
function highlightSkills($text, $matched, $missing) {

    // Highlight matched skills (green)
    foreach ($matched as $skill) {
        $text = preg_replace(
            "/(" . preg_quote($skill, '/') . ")/i",
            "<span class='highlight-good'>$1</span>",
            $text
        );
    }

    // Highlight missing skills (red)
    foreach ($missing as $skill) {
        $text = preg_replace(
            "/(" . preg_quote($skill, '/') . ")/i",
            "<span class='highlight-bad'>$1</span>",
            $text
        );
    }

    return $text;
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="css/styles.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container">

<h2>📊 Match Score: <?php echo $score; ?>% (<?php echo $label; ?>)</h2>

<div class="progress">
    <div class="progress-fill" style="width: <?php echo $score; ?>%">
        <?php echo $score; ?>%
    </div>
</div>

<h3>📊 Skill Match Visualization</h3>
<canvas id="skillsChart"></canvas>

<h3>📈 Overall Match</h3>
<canvas id="scoreChart" style="max-width:300px;"></canvas>

<h3>✅ Matched Skills</h3>
<ul>
<?php foreach ($matched as $m) echo "<li class='good'>✔ $m</li>"; ?>
</ul>

<h3>❌ Missing Skills</h3>
<ul>
<?php foreach ($missing as $m) echo "<li class='bad'>✖ $m</li>"; ?>
</ul>

<hr>

<h3>🧾 Extracted Resume</h3>
<div class="box">
<?php $safeText = htmlspecialchars($resumeText);
$highlighted = highlightSkills($safeText, $matched, $missing);
echo nl2br($highlighted); ?>
</div>

<hr>

<h2>🤖 AI Analysis</h2>
<div class="ai-box">
<?php echo nl2br(htmlspecialchars($aiOutput)); ?>
</div>

<a href="index.php">Analyze Another</a>

</div>

<script>
const matchedCount = <?php echo count($matched); ?>;
const missingCount = <?php echo count($missing); ?>;
const scoreValue = <?php echo $score; ?>;

// BAR CHART
new Chart(document.getElementById('skillsChart'), {
    type: 'bar',
    data: {
        labels: ['Matched Skills', 'Missing Skills'],
        datasets: [{
            data: [matchedCount, missingCount]
        }]
    },
    options: {
        plugins: { legend: { display: false } }
    }
});

// DOUGHNUT CHART
new Chart(document.getElementById('scoreChart'), {
    type: 'doughnut',
    data: {
        labels: ['Match', 'Remaining'],
        datasets: [{
            data: [scoreValue, 100 - scoreValue]
        }]
    },
    options: {
        cutout: '70%'
    }
});
</script>

</body>
</html>