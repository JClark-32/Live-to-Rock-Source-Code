<?php
function blog_submission_fields() { ?>
    <div id="ltr-blog-submission" style="border: 1px solid #ccc; padding: 1rem; max-width: 700px; margin: 0 auto;">
        <h2>Submit a Blog</h2>
        <form method="post">
            <label>Blog Title:</label><br>
            <input type="text" name="ltr-blog-title" style="width: 100%; padding: 0.5rem;"><br><br>

            <label>Blog URL (optional):</label><br>
            <input type="url" name="ltr-blog-url" style="width: 100%; padding: 0.5rem;"><br><br>

            <label>Author:</label><br>
            <input type="text" name="ltr-blog-author" style="width: 100%; padding: 0.5rem;"><br><br>

            <label>Blog Content:</label><br>
            <textarea name="ltr-blog-text" rows="6" style="width: 100%; padding: 0.5rem;"></textarea><br><br>

            <button type="submit" name="ltr-submit-blog-button" style="padding: 0.5rem 1rem;">Submit</button>
        </form>
    </div>
<?php } ?>