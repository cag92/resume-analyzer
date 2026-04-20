<div class="header">🚀 AI Resume Analyzer</div>

<div class="container">
    <div class="card">
        <h2>Analyze Your Resume</h2>

        <form action="analyze.php" method="post" enctype="multipart/form-data">

            <label>Upload Resume</label>
            <input type="file" name="resume_file">

            <p style="text-align:center;">OR</p>

            <textarea name="resume" placeholder="Paste resume..."></textarea>

            <textarea name="job" placeholder="Paste job description..." required></textarea>

            <button type="submit">Analyze</button>
        </form>
    </div>
</div>