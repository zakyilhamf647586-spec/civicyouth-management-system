<?php
$activity = is_array($activity ?? null) ? $activity : [];
?>

<section class="bilingual-editor-section">
    <div class="bilingual-editor-heading is-english">
        <span>EN</span>

        <div>
            <strong>Official English Activity Content</strong>
            <small>
                Draft may be saved while incomplete. Every populated Indonesian
                field must have an English pair before review or publication.
            </small>
        </div>
    </div>

    <div class="form-group">
        <label for="title_en">Activity Name — English</label>
        <input
            type="text"
            id="title_en"
            name="title_en"
            maxlength="150"
            value="<?= esc(old(
                'title_en',
                $activity['title_en'] ?? ''
            )) ?>"
            placeholder="Example: RW 01 Community Service"
        >
    </div>

    <div class="form-group">
        <label for="location_en">Location — English</label>
        <input
            type="text"
            id="location_en"
            name="location_en"
            maxlength="200"
            value="<?= esc(old(
                'location_en',
                $activity['location_en'] ?? ''
            )) ?>"
        >
    </div>

    <div class="form-group">
        <label for="summary_en">Public Summary — English</label>
        <textarea
            id="summary_en"
            name="summary_en"
            rows="3"
            maxlength="220"
        ><?= esc(old(
            'summary_en',
            $activity['summary_en'] ?? ''
        )) ?></textarea>
    </div>

    <div class="form-group">
        <label for="description_en">Activity Description — English</label>
        <textarea
            id="description_en"
            name="description_en"
            rows="7"
        ><?= esc(old(
            'description_en',
            $activity['description_en'] ?? ''
        )) ?></textarea>
    </div>

    <div class="form-group">
        <label for="result_en">Results and Impact — English</label>
        <textarea
            id="result_en"
            name="result_en"
            rows="5"
        ><?= esc(old(
            'result_en',
            $activity['result_en'] ?? ''
        )) ?></textarea>
    </div>
</section>
