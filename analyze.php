<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load PDF parser
require 'vendor/autoload.php';
use Smalot\PdfParser\Parser;

// Get API key from environment (Render)
$OPENAI_API_KEY = getenv("OPENAI_API_KEY");
var_dump($OPENAI_API_KEY);

// INPUT HANDLING (PDF + TEXT)

$resumeText = $_POST['resume'] ?? '';
$job = $_POST['job'] ?? '';

// Handle file upload
if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === 0) {

    $fileTmp = $_FILES['resume_file']['tmp_name'];
    $fileName = $_FILES['resume_file']['name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($ext === "pdf") {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($fileTmp);
            $resumeText = $pdf->getText();
        } catch (Exception $e) {
            $resumeText = "Error reading PDF.";
        }
    } elseif ($ext === "txt") {
        $resumeText = file_get_contents($fileTmp);
    }
}

// Validate input
if (trim($resumeText) === '' || trim($job) === '') {
    die("Resume and Job Description are required.");
}

// SIMPLE SKILL MATCHING

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

$score = count($matched) / max(count($jobSkills), 1) * 100;

// AI ANALYSIS

$aiOutput = "AI temporarily unavailable.";

if ($OPENAI_API_KEY && strlen($job) > 50) {

    $prompt = "Analyze this resume vs job description.

Give:
- Match score
- Strengths
- Missing skills
- Suggestions

Resume:
$resumeText

Job:
$job";

    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ]
    ];

    $options = [
        "http" => [
            "header" => "Content-Type: application/json\r\n" .
                        "Authorization: Bearer $OPENAI_API_KEY\r\n",
            "method" => "POST",
            "content" => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);

    $response = @file_get_contents(
        "https://api.openai.com/v1/chat/completions",
        false,
        $context
    );

    if ($response !== false) {
        $result = json_decode($response, true);

        if (isset($result["choices"][0]["message"]["content"])) {
            $aiOutput = $result["choices"][0]["message"]["content"];
        }
    } else {
        $aiOutput = "⚠️ AI rate limit reached or API error.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">

<h2>Match Score: <?php echo round($score); ?>%</h2>

<div class="progress">
    <div class="progress-fill" style="width: <?php echo $score; ?>%">
        <?php echo round($score); ?>%
    </div>
</div>

<h3>Matched Skills</h3>
<ul>
<?php foreach ($matched as $m) echo "<li class='good'>✔ " . htmlspecialchars($m) . "</li>"; ?>
</ul>

<h3>Missing Skills</h3>
<ul>
<?php foreach ($missing as $m) echo "<li class='bad'>✖ " . htmlspecialchars($m) . "</li>"; ?>
</ul>

<h3>Job Description</h3>
<p><?php echo nl2br(htmlspecialchars($job)); ?></p>

<hr>

<h2>AI Analysis</h2>
<p style="color:orange;">(AI may be limited due to usage limits)</p>

<div class="ai-box">
<?php echo nl2br(htmlspecialchars($aiOutput)); ?>
</div>

<br>
<a href="index.php">Analyze Another</a>

</div>

</body>
</html>