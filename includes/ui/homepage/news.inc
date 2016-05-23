<div class="vals-news block-padding" style="height:100%">
	<div class="block">
		<h4 id="title-section-how-it-works"><?php echo _VALS_USE_TIME_SCHEDULE ?  t('News and Timeline'):
            t('News');?></h4>
        <?php 
        if (_VALS_USE_TIME_SCHEDULE){
            module_load_include('inc', 'vals_soc', 'includes/ui/homepage/timeline');
        } else {
            //No output. We let the news consume the whole width echo "";
        }
        ?>
            
        <div class="block-padding-no-border">
            <?php echo variableGetFromStruct('vals_news_text', 'value', 'No news to announce'); ?>
        </div>
	</div>
</div>
