<form action="analyze.php" method="post" enctype="multipart/form-data">

    <label>Upload Resume (PDF or TXT)</label>
    <input type="file" name="resume_file" accept=".pdf,.txt" required>

    <label>OR Paste Resume</label>
    <textarea name="resume"></textarea>

    <label>Job Description</label>
    <textarea name="job" required></textarea>

    <button type="submit">Analyze</button>

</form>