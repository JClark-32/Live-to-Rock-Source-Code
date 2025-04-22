<?php 
function liner_submission_fields(){
    ?>
    <div id="ltr-liner-submission">
        <div style="text-align: center;">
            <h2>Have a request?</h2>
            <p style="border-bottom: 2px solid #ccc; display: inline-block; padding-bottom: 0.25rem;">
                Paste URL link here:
            </p>
        </div>
        <form id="ltr-liner-link" method="post">
            <div class="input" style="padding:1rem;">
                <input type="url" name="ltr-liner-url" placeholder="Requested URL" required>
            </div>
            <div id="ltr-submit" style="padding:1rem;">
                <button type="submit" name="ltr-submit-liner-button" class="submit-btn">Submit!</button>
            </div>
        </form>
    </div>
    <?php
}
?>