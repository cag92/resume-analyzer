<!DOCTYPE html>
<html>
<head>
    <title>AI Resume Analyzer</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">
    <h1>🚀 AI Resume Analyzer</h1>
    <p>Upload your resume and compare it to a job description using AI.</p>

    <form action="analyze.php" method="post" enctype="multipart/form-data">

        <label><b>Upload Resume (PDF or TXT)</b></label>
        <input type="file" name="resume_file" accept=".pdf,.txt">

        <p style="text-align:center;">— OR —</p>

        <label><b>Paste Resume</b></label>
        <textarea name="resume" rows="6"></textarea>

        <label><b>Job Description</b></label>
        <textarea name="job" rows="6" required></textarea>

        <button type="submit">Analyze Resume</button>

    </form>
</div>

</body>
</html>