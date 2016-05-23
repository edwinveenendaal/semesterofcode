<?php

class ThreadUIBuilder {

    private $output = '';
    private $parents = array();
    private $children = array();
    private $entity_type;
    private $entity_id;

    public function __construct($entity_id, $entity_type) {
        $this->entity_id = $entity_id;
        $this->entity_type = $entity_type;
    }

    /**
     *
     * @param array $comments
     */
    function init($records) {
        if ($records) {
            foreach ($records as $record) {
                if ($record ['parent_id'] === NULL) {
                    $this->parents [$record ['id']] [] = $record;
                } else {
                    $this->children [$record ['parent_id']] [] = $record;
                }
            }
        }
        $count = sizeof($records);
        $this->output .= "<div>";
        $this->output .= "	<h2 style='display:inline-block'>";
        $this->output .= t('Comments') . " ";
        $this->output .= "		(<div style='display:inline;' id='comment-total-" . $this->entity_type . "-" . $this->entity_id . "'>" . $count . "</div>)";
        $this->output .= "	</h2>";
        $this->output .= "	&nbsp;";
        $this->output .= "<input style='display:inline-block' id='comment-toggle-" . $this->entity_id . "-" . $this->entity_type . "' class='comment-toggle' type='button' value='" . ($count ? t('show') : t('create')) . "'/>";
        $this->output .= "</div>";
    }

    function renderSingleComment($comment) {
        $this->output = '';
        $this::format_comment($comment);
        return $this->output;
    }

    /**
     *
     * @param array $comment
     * @param int $depth
     */
    private function format_comment($comment) {
        if (isset($comment ['parent_id'])) {
            $class_display = 'threaded-comment-wrapper';
            $post_type = t('Replied on');
        } else {
            $class_display = 'initial-threaded-comment-wrapper';
            $post_type = t('Posted on');
        }
        $id = $comment ['id'];
        $this->output .= "<div id='threaded-comment-wrapper-$id' class='" . $class_display . "'>";
        $this->output .= "	<div class='threaded-comment'>";
        $this->output .= "	<div id='msg_threaded-comment-wrapper-$id'></div>";
        if (Users::isAdmin()) {
            //$this->output .=  "			&nbsp;";
            $this->output .= "			<div class='totheright'><a href='#' onclick='ajaxCall(\"comment\", \"delete\", {id: $id}, \"threaded-comment-wrapper-$id\");'>" . t('delete') . "</a>";
            $this->output .= "			</div>";
        }
        $this->output .= "		<div class='threaded-comment-header'>";
        $this->output .= "			<span class='comment_author'>"; //<a href='#'>";
        $this->output .= "			${comment['name']}";
        $this->output .= "			</span>";
        $this->output .= "			&nbsp;(${comment['type']}) - &nbsp;";
        $this->output .= $post_type;
        $this->output .= "			&nbsp;";
        // TODO check date
        $this->output .= date('F j, Y, g:i a', strtotime($comment ['date_posted']));

        $this->output .= "		</div>"; // end header
        $this->output .= "		<div class='threaded-comment-body'>";
        $this->output .= $comment['description'];
        $this->output .= "			<br/>";
        $this->output .= '			<a class="reply-comment" href="">reply</a>';
        $this->output .= "		</div>"; // end body
        $this->output .= "	</div>";
        $this->output .= $this->getPostNewCommentForm($comment);
    }

    function getPostNewCommentForm($comment) {
        $css = '';
        $handler = $comment['id']; // is it???
        if ($handler == NULL) {
            $css = '-top';
            $handler = 0;
        }
        $target = 'threaded-comment-wrapper-' . $handler;
        $output = '<div id="reply-comment-form-' . $handler . '" class="reply-comment-form' . $css . ' threaded-comment-wrapper' . $css . '">';
        $form = drupal_get_form("vals_soc_comment_form", $handler, $target, $this->entity_id, $this->entity_type);
        $output .= renderForm($form, $target, true);

        $output .= '</div>';
        return $output;
    }

    private function print_parent($comment, $depth = 0) {
        foreach ($comment as $c) {
            $this->format_comment($c, $depth);
            if (isset($this->children [$c ['id']])) {
                $this->print_parent($this->children [$c ['id']], $depth + 1);
            }
            $this->output .= '</div>';
        }
    }

    public function print_comments() {

        $this->output .= '<div class="comments-parent-container" id="comments-parent-container-' . $this->entity_id . '-' . $this->entity_type . '">';
        $this->output .= '	<div class="existing-comments-container" id="existing-comments-container-' . $this->entity_id . '-' . $this->entity_type . '">';
        foreach ($this->parents as $c) {
            $this->output .= $this->print_parent($c);
        }
        $this->output .= '</div>';
        $this->output .= "<h3>" . t('Post New Comment') . "</h3>";
        $this->output .= $this->getPostNewCommentForm(NULL); // top level new post
        $this->output .= '</div>';
        return $this->output;
    }

}
