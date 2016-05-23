<?php
module_load_include('php', 'vals_soc', 'includes/classes/ThreadedComments');
module_load_include('php', 'vals_soc', 'includes/classes/ThreadUIBuilder');


function initComments($entity_id, $entity_type){
	$threads = ThreadedComments::getInstance()->getThreadsForEntity($entity_id, $entity_type);
    $nr_comments = count($threads);
	$threaded_comments_builder = new ThreadUIBuilder($entity_id, $entity_type);
	$threaded_comments_builder -> init($threads);
	$rendered_threads = $threaded_comments_builder->print_comments();
	$post_forms =  getCommentsJsHandler($entity_id, $entity_type, $nr_comments);
	return $rendered_threads . $post_forms;
}

 function getCommentsJsHandler($entity_id, $entity_type, $nr_comments=99){
	$output = '
	<script type="text/javascript">
        var hideText = "'.t("hide").'";
		var showText = "'.($nr_comments ? t("show") : t("create")) .'";
		$jq(function(){	
			$jq(document).off("click", ".reply-comment");
			$jq(document).on("click", ".reply-comment", function(e){			
				e.preventDefault();
				$jq(this).parent().parent().next(".reply-comment-form").toggle("slow");
			});

			$jq("#comment-toggle-'.$entity_id.'-'.$entity_type.'").on("click", function(e){
				var link = $jq(this);
				
				e.preventDefault();
				$jq("#comments-parent-container-'.$entity_id.'-'.$entity_type.'").toggle("slow", function() {
					if (link.attr("value") == showText) {
						link.attr("value", hideText);
					} else {
						link.attr("value",showText);
					}
				});
			});

		});
	</script>
	';
	return $output;
}