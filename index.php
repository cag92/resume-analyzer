<!DOCTYPE html>
<html>
<head>
    <title>AI Resume Analyzer</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js" defer></script>
</head>
<body>

<div class="container">
    <h1>AI Resume Analyzer</h1>
    <p>Compare your resume to a job description</p>

    <form action="analyze.php" method="post" enctype="multipart/form-data">

        <label>Upload Resume (TXT)</label>
        <input type="file" name="resume" required>

        <label>Job Description</label>
        <textarea name="jobdesc" required></textarea>

        <button type="submit">Analyze</button>

    </form>

    <div id="loading">Analyzing...</div>
</div>

</body>
</html>