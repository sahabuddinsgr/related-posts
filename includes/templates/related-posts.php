<?php
/**
 * Template for displaying related posts
 *
 * @package RelatedPosts
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( !defined('ABSPATH') ) {
    exit;
}

$posts_per_row = absint( $this->options['posts_per_row'] ?? 3 );
$heading = sanitize_text_field( $this->options['heading'] ?? '' );
?>

<div class="related-posts-wrapper">
    <?php if ( $heading ): ?>
        <h3 class="related-posts-heading"><?php echo esc_html( $heading ); ?></h3>
    <?php endif; ?>

    <div class="related-posts-grid" style="display: grid; grid-template-columns: repeat(<?php echo esc_attr( $posts_per_row ); ?>, 1fr);">
        <?php 
        foreach ( $posts as $related_post ): 
            // Ensure post data is properly set up
            setup_postdata( $posts );
        ?>
            <article class="related-post-item">
                <?php if ( !empty($this->options['show_thumbnail']) ): ?>
                    <?php if ( has_post_thumbnail( $related_post ) ): ?>
                        <a href="<?php echo esc_url( get_permalink( $related_post )) ; ?>" 
                           class="related-post-thumbnail"
                           aria-label="<?php echo esc_attr( get_the_title( $related_post ) ); ?>">
                            <?php echo wp_kses_post( get_the_post_thumbnail( $related_post, 'medium' ) ); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="related-post-content">
                    <?php if ( !empty($this->options['show_title']) ): ?>
                        <h4 class="related-post-title">
                            <a href="<?php echo esc_url( get_permalink( $related_post ) ); ?>">
                                <?php echo esc_html( get_the_title( $related_post ) ); ?>
                            </a>
                        </h4>
                    <?php endif; ?>

                    <?php if ( !empty( $this->options['show_date'] ) ): ?>
                        <time class="related-post-date" datetime="<?php echo esc_attr( get_the_date( 'c', $related_post ) ); ?>">
                            <?php echo esc_html( get_the_date( '', $related_post ) ); ?>
                        </time>
                    <?php endif; ?>

                    <?php if ( !empty( $this->options['show_author'] ) ): ?>
                        <div class="related-post-author">
                            <?php
                            $author_id = get_post_field( 'post_author', $related_post );
                            printf(
                                /* translators: %s: author name */
                                esc_html__( '__By %s', 'related-posts' ),
                                '<span class="author-name">' . esc_html( get_the_author_meta( 'display_name', $author_id)) . '</span>'
                            );
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( !empty( $this->options['show_description'] ) ): ?>
                        <div class="related-post-excerpt">
                            <?php
                                $excerpt_length = !empty( $this->options['description_length'] ) 
                                ? absint( $this->options['description_length'] ) 
                                : 55;
                        
                                // Get post excerpt or generate from content
                                $excerpt = has_excerpt( $related_post ) 
                                    ? get_the_excerpt( $related_post )
                                    : wp_strip_all_tags( strip_shortcodes( $related_post->post_content ) );
                                
                                // Trim to desired length
                                $words = explode( ' ', $excerpt );
                                if ( count( $words ) > $excerpt_length)  {
                                    $words = array_slice( $words, 0, $excerpt_length );
                                    $excerpt = implode( ' ', $words ) . '&hellip;';
                                }
                                
                                echo wp_kses_post( $excerpt );
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>

<?php wp_reset_postdata(); ?>