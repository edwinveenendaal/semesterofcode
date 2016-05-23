<div class="vals-intro block-padding">
    <div class="block">
        <h4 id="title-section-how-it-works" ><?php echo variable_get('vals_intro_header', 'no intro'); ?>
        </h4>
        <div class="block-padding-no-border">
            <?php
            echo _VALS_USE_TIME_SCHEDULE ? variableGetFromStruct('vals_intro_text', 'value', 'no intro') :
                    variableGetFromStruct('vals_intro_text_no_schedule', 'value', 'no intro')
            ;
            ?>
        </div>
    </div>
</div>