<?php
$structure = is_array($structure ?? null) ? $structure : [];
?>

<section class="bilingual-editor-section">
    <div class="bilingual-editor-heading is-english">
        <span>EN</span>

        <div>
            <strong>Official English Team Profile</strong>
            <small>
                Complete every English pair used by an active official so the
                organization diagram remains consistent in both languages.
            </small>
        </div>
    </div>

    <div class="form-group">
        <label for="position_name_en">Position — English</label>
        <input
            type="text"
            id="position_name_en"
            name="position_name_en"
            maxlength="150"
            value="<?= esc(old(
                'position_name_en',
                $structure['position_name_en'] ?? ''
            )) ?>"
            placeholder="Example: Chair, Secretary, Sports Section"
        >
    </div>

    <div class="form-group">
        <label for="division_en">Division — English</label>
        <input
            type="text"
            id="division_en"
            name="division_en"
            maxlength="150"
            value="<?= esc(old(
                'division_en',
                $structure['division_en'] ?? ''
            )) ?>"
            placeholder="Example: Core Team, Sports, Social Affairs"
        >
    </div>

    <div class="form-group">
        <label for="description_en">Role Description — English</label>
        <textarea
            id="description_en"
            name="description_en"
            rows="4"
        ><?= esc(old(
            'description_en',
            $structure['description_en'] ?? ''
        )) ?></textarea>
    </div>

    <div class="form-group">
        <label for="short_bio_en">Short Biography — English</label>
        <textarea
            id="short_bio_en"
            name="short_bio_en"
            rows="4"
        ><?= esc(old(
            'short_bio_en',
            $structure['short_bio_en'] ?? ''
        )) ?></textarea>
    </div>
</section>
