<?php

beans_remove_action ( 'beans_post_archive_title' );

add_filter( 'beans_loop_query_args', 'beans_child_view_query_args' );

function beans_child_view_query_args() {
    return array(
		'category_name' => 'work', // work category slug
        'paged' => get_query_var( 'paged' )
    );
}

// Enqueue UIkit assets.
add_action( 'beans_uikit_enqueue_scripts', 'example_enqueue_uikit_assets' );

function example_enqueue_uikit_assets() {
    beans_uikit_enqueue_components( array( 'grid' ), 'add-ons' );
}

// Display posts in a responsive dynamic grid.
add_action( 'beans_before_load_document', 'example_posts_grid' );

function example_posts_grid() {
    // Add grid.
    beans_wrap_inner_markup( 'beans_content', 'example_posts_grid', 'div', array(
        'data-uk-grid' => "{gutter: 20, controls: '#tm-grid-filter'}",
    ) );
    beans_wrap_markup( 'beans_post', 'example_post_grid_column', 'div', array(
        'class' => 'uk-width-large-1-3 uk-width-medium-1-2',
    ) );

    // Move the posts pagination after the new grid markup.
    beans_modify_action_hook( 'beans_posts_pagination', 'example_posts_grid_after_markup' );
}

// Add description of page
add_action( 'beans_content_before_markup', 'beans_child_home_add_title' );

function beans_child_home_add_title() {
    ?>
        <div class="uk-container uk-container-center uk-panel-box">
            <h1>Portfolio</h1>
            <h4>Some of my work in listed flashy filterable grid...</h4>
        </div>
    <?php	
}

// Add posts filter.
add_action( 'beans_content_before_markup', 'example_posts_filter' );

function example_posts_filter() {
    ?>
        <ul id="tm-grid-filter" class="uk-subnav uk-container-center uk-margin-top">
            <li data-uk-filter="">
                <a class="uk-link" href="#">All</a>
            </li>
            <li data-uk-filter="design">
                <a class="uk-link" href="#">Design</a>
            </li>
            <li data-uk-filter="development">
                <a class="uk-link" href="#">Development</a>
            </li>
            <li data-uk-filter="ecommerce">
                <a class="uk-link" href="#">E-Commerce</a>
            </li>
            <li data-uk-filter="marketing">
                <a class="uk-link" href="#">Marketing</a>
            </li>
        </ul>
    <?php
}

// Add filter data to each post.
add_filter( 'example_post_grid_column_attributes', 'example_post_attributes' );

function example_post_attributes( $attributes ) {
    // Get the categories and build and array with its slugs.
    $categories = wp_list_pluck( get_the_category( get_the_ID() ), 'slug' );
	$filter = array();
	$tags = get_the_tags(get_the_ID());
	foreach($tags as $tag) {
		$filter[] = $tag->slug;
    }

    return array_merge(
        $attributes,
        array(
            'data-uk-filter' => implode( ',', (array) $filter ), // automatically escaped.
        ) 
    );
}

// Move the post image above the post title.
beans_modify_action_hook( 'beans_post_image', 'beans_post_title_before_markup' );

// Add filter show excerpts on grid
add_filter( 'the_content', 'example_post_excerpt' );

function example_post_excerpt( $content ) {
    // Return the excerpt() if it exists.
    if ( has_excerpt() ) {
        return '<p>' . get_the_excerpt() . '</p><p>' . beans_post_more_link() . '</p>';
    }

    return $content;
}

// Load the document. Always Keep this at the bottom of the page template.
beans_load_document();
