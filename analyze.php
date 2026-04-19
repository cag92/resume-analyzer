<?php
require_once("config.php");

// Clean text
function cleanText($text) {
    $text = strtolower($text);
    $text = preg_replace("/[^a-z0-9 ]/", "", $text);
    return $text;
}

// Inputs
$resumeFile = $_FILES['resume']['tmp_name'];
$rawJobDesc = $_POST['jobdesc'];

$resumeText = file_get_contents($resumeFile);

// Clean versions
$cleanResume = cleanText($resumeText);
$cleanJob = cleanText($rawJobDesc);

// Skill list
$skills = ["python","java","sql","html","css","javascript","react","aws","docker"];

// Find skills
function findSkills($text, $skills) {
    $found = [];
    foreach ($skills as $skill) {
        if (strpos($text, $skill) !== false) {
            $found[] = $skill;
        }
    }
    return $found;
}

$resumeSkills = findSkills($cleanResume, $skills);
$jobSkills = findSkills($cleanJob, $skills);

// Score
$matched = array_intersect($resumeSkills, $jobSkills);
$missing = array_diff($jobSkills, $resumeSkills);
$score = count($matched) / max(count($jobSkills), 1) * 100;

// Highlight missing
foreach ($missing as $skill) {
    $rawJobDesc = str_ireplace(
        $skill,
        "<span class='highlight'>$skill</span>",
        $rawJobDesc
    );
}


//AI SECTION

$prompt = "
You are an AI resume analyzer.

Compare this resume to the job description.

Give:
- Match score (0-100)
- 3 strengths
- 3 missing skills
- Suggestions

Resume:
$resumeText

Job Description:
$rawJobDesc
";

$data = [
    "model" => "gpt-4o-mini",
    "messages" => [
        ["role" => "user", "content" => $prompt]
    ]
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $OPENAI_API_KEY
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$aiOutput = $result["choices"][0]["message"]["content"] ?? "AI unavailable.";

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
<div class="ai-box">
<?php echo nl2br(htmlspecialchars($aiOutput)); ?>
</div>

<br>
<a href="index.php">Analyze Another</a>

</div>

</body>
</html>