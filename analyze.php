<?php

function cleanText($text) {
    $text = strtolower($text);
    $text = preg_replace("/[^a-z0-9 ]/", "", $text);
    return $text;
}

// Get inputs
$resumeFile = $_FILES['resume']['tmp_name'];
$rawJobDesc = $_POST['jobdesc'];

$resumeText = file_get_contents($resumeFile);

// Clean versions
$cleanResume = cleanText($resumeText);
$cleanJob = cleanText($rawJobDesc);

// Skills list
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

<a href="index.php">Analyze Another</a>

</div>

</body>
</html>