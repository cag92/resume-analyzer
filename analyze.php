<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get API key from Render
$OPENAI_API_KEY = getenv("OPENAI_API_KEY");

if (!$OPENAI_API_KEY) {
    die("Missing API key.");
}

// Validate upload
if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== 0) {
    die("File upload failed.");
}

// Read inputs
$resumeText = file_get_contents($_FILES['resume']['tmp_name']);
$rawJobDesc = $_POST['jobdesc'];

// Clean text
function cleanText($text) {
    $text = strtolower($text);
    return preg_replace("/[^a-z0-9 ]/", "", $text);
}

$cleanResume = cleanText($resumeText);
$cleanJob = cleanText($rawJobDesc);

// Skills list
$skills = ["python","java","sql","html","css","javascript","react","aws","docker"];

function findSkills($text, $skills) {
    $found = [];
    foreach ($skills as $skill) {
        if (strpos($text, $skill) !== false) {
            $found[] = $skill;
        }
    }
    return $found;
}

// Match logic
$resumeSkills = findSkills($cleanResume, $skills);
$jobSkills = findSkills($cleanJob, $skills);

$matched = array_intersect($resumeSkills, $jobSkills);
$missing = array_diff($jobSkills, $resumeSkills);

$score = count($matched) / max(count($jobSkills), 1) * 100;

// Highlight missing skills
foreach ($missing as $skill) {
    $rawJobDesc = str_ireplace(
        $skill,
        "<span class='highlight'>$skill</span>",
        $rawJobDesc
    );
}

// 🤖 AI Section

$aiOutput = "AI temporarily unavailable (rate limit reached).";

if (strlen($rawJobDesc) > 50) {

    $prompt = "Compare this resume to the job description and give:
    - Match score
    - Strengths
    - Missing skills
    - Suggestions

    Resume:
    $resumeText

    Job:
    $rawJobDesc";

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

    // suppress error to avoid crash
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
<?php foreach ($matched as $m) echo "<li class='good'>✔ $m</li>"; ?>
</ul>

<h3>Missing Skills</h3>
<ul>
<?php foreach ($missing as $m) echo "<li class='bad'>✖ $m</li>"; ?>
</ul>

<h3>Job Description Analysis</h3>
<p><?php echo $rawJobDesc; ?></p>

<hr>

<h2>AI Analysis</h2>
<p style="color:orange;">(May be limited due to API usage)</p>

<div class="ai-box">
<?php echo nl2br(htmlspecialchars($aiOutput)); ?>
</div>

<br>
<a href="index.php">Analyze Another</a>

</div>

</body>
</html>