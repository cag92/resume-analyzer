<!DOCTYPE html>
<html>
<head>
    <title>AI Resume Analyzer</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">
    <h1>AI Resume Analyzer</h1>

    <form action="analyze.php" method="post" enctype="multipart/form-data">

        <label>Upload Resume (PDF or TXT)</label>
        <input type="file" name="resume_file" accept=".pdf,.txt">

        <p>OR</p>

        <label>Paste Resume</label>
        <textarea name="resume" rows="6"></textarea>

        <label>Job Description</label>
        <textarea name="job" rows="6" required></textarea>

        <br><br>
        <button type="submit">Analyze</button>

    </form>
</div>

</body>
</html>