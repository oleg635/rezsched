<?php
/*
 * Template Name: Custom ETN Event
 * Template Post Type: etn
 */

// Get the global post variable
global $post;

// Check if the current post is an "etn" post type
if ( 'etn' === $post->post_type ) {
    // Get the template post with ID 430
    $template_post = get_post( 430 );

    // If the template post is found, load its content
    if ( $template_post ) {
        // Output the content of the template post
        echo apply_filters( 'the_content', $template_post->post_content );
    } else {
        // If template post is not found, display a message or fallback content
        echo 'Custom template not found.';
    }
} else {
    // If not an "etn" post type, fallback to default behavior
    get_header();

    // Start the loop.
    while ( have_posts() ) :
        the_post();

        // Get the template part for single post content
        get_template_part( 'template-parts/content', 'single' );

        // If comments are open or we have at least one comment, load up the comment template.
        if ( comments_open() || get_comments_number() ) :
            comments_template();
        endif;

    // End of the loop.
    endwhile;

    // Include the Greenshift footer
    get_footer();
}
?>
