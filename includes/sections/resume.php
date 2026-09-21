<div class="resume-section" id="resume">
    <?php section_head('resume'); ?>

    <div class="education-exprerience">
        <?php
        resume_column(
            'education',
            s('resume_education_title', 'Education'),
            'assets/images/education.png',
            'education'
        );

        resume_column(
            'experience',
            s('resume_experience_title', 'Experience'),
            'assets/images/exprerience.png',
            'experience'
        );
        ?>
    </div>
</div>
