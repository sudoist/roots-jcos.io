<?php

// Remove the comments.
add_action( 'beans_post_after_markup', 'remove_comments_on_home', 5 );
function remove_comments_on_home() {
	beans_remove_action( 'beans_comments_template' );
}

// Load Beans document.
beans_load_document();
