<?php 
function event_submission_fields(){
    ?>
    <div id="ltr-event-submission" style="border: 1px solid #ccc; padding: 1rem; width: 90%; max-width: 700px; margin: 0 auto;">
        <div style="text-align: left;">
            <h2>Request an Event</h2>
            <p style="border-bottom: 1px solid #ccc; display: inline-block; padding-bottom: 0.25rem;">
                Fill out the form below:
            </p>
        </div>

        <form id="ltr-event-link" method="post">
            <div class="input" style="padding: 0.5rem 0;">
                <label for="ltr-event-title">Event Title:</label><br>
                <input type="text" name="ltr-event-title" id="ltr-event-title" placeholder="Title of the Event" required style="width: 100%; padding: 0.5rem;">
            </div>

            <div class="input" style="padding: 0.5rem 0;">
                <label for="ltr-event-date">Event Date:</label><br>
                <input type="date" name="ltr-event-date" id="ltr-event-date" required style="width: 100%; padding: 0.5rem;">
            </div>

            <div class="input" style="padding: 0.5rem 0;">
                <label for="ltr-event-start">Start Time:</label><br>
                <input type="time" name="ltr-event-start" id="ltr-event-start" required style="width: 100%; padding: 0.5rem;">
            </div>

            <div class="input" style="padding: 0.5rem 0;">
                <label for="ltr-event-end">End Time:</label><br>
                <input type="time" name="ltr-event-end" id="ltr-event-end" required style="width: 100%; padding: 0.5rem;">
            </div>

            <div class="input" style="padding: 0.5rem 0;">
                <label for="ltr-event-url">Event URL:</label><br>
                <input type="url" name="ltr-event-url" id="ltr-event-url" placeholder="https://example.com" required style="width: 100%; padding: 0.5rem;">
            </div>

            <div class="input" style="padding: 0.5rem 0;">
                <label for="ltr-event-details">Additional Details:</label><br>
                <textarea name="ltr-event-details" id="ltr-event-details" rows="4" placeholder="Include any relevant notes or details..." style="width: 100%; padding: 0.5rem;"></textarea>
            </div>

            <div id="ltr-submit" style="padding: 1rem 0;">
                <button type="submit" name="ltr-submit-event-button" class="submit-btn" style="padding: 0.5rem 1rem;">Submit!</button>
            </div>
        </form>
    </div>
    <?php
}
?>