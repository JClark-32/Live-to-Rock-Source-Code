<?php
function submission_fields(){
    ?>
    <div id="ltr-video-submission">
        <h2>Post Your Life Performance?</h2>
        <p>Paste YouTube URL here:</p>
        <form id="ltr-video-link" method="post">
            <div class="input">
                <input type="url" name="ltr-video-url" placeholder="YouTube URL" required>
            </div>
            <div id="ltr-submit">
                <button type="submit" name="ltr-submit-video-button" class="submit-btn">Submit!</button>
            </div>
        </form>
    </div>
    <?php
}
?>