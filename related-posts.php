<?php
/**
 * Plugin Name: Related Posts
 * Plugin URI:  https://github.com/sahabuddinsgr/related-posts
 * Description: Displays related posts based on post categories with customizable settings.
 * Version:     1.0.0
 * Author:      Md Sahabuddin
 * Author URI:  https://github.com/sahabuddinsgr
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: related-posts
 * Requires at least: 5.6
 * Requires PHP: 7.2
 */

namespace WDA_RelatedPosts;

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Related Posts Plugin Class
 *
 * @since 1.0.0
 */
class Related_Posts {
    /**
     * Single instance of the class
     *
     * @since 1.0.0
     * @var Related_Posts
     */
    private static $instance = null;

    /**
     * Plugin options
     *
     * @since 1.0.0
     * @var array
     */
    private $options;

    /**
     * Prevent direct object creation
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->define_constants();
        $this->init_hooks();
        $this->load_dependencies();
        $this->load_options();
    }

    /**
     * Get single instance of class
     *
     * @since 1.0.0
     * @return Related_Posts
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Define plugin constants
     *
     * @since 1.0.0
     */
    private function define_constants() {
        define( 'WDA_RELATEDPOSTS_VERSION', '1.0.0' );
        define( 'WDA_RELATEDPOSTS_FILE', __FILE__ );
        define( 'WDA_RELATEDPOSTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
        define( 'WDA_RELATEDPOSTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        define( 'WDA_RELATEDPOSTS_BASENAME', plugin_basename( __FILE__ ) );
    }

    /**
     * Initialize WordPress hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        add_filter( 'the_content', array( $this, 'display_related_posts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'activate_' . WDA_RELATEDPOSTS_BASENAME, array( $this, 'activate' ) );
        add_action( 'deactivate_' . WDA_RELATEDPOSTS_BASENAME, array( $this, 'deactivate' ) );
    }

    /**
     * Load required files and dependencies
     *
     * @since 1.0.0
     */
    private function load_dependencies() {
        require_once WDA_RELATEDPOSTS_PLUGIN_PATH . 'includes/Admin_Menu.php';
        new Admin_Menu();
    }

    /**
     * Load plugin options
     *
     * @since 1.0.0
     */
    private function load_options() {
        $this->options = wp_parse_args(
            get_option( 'related_posts_settings', array() ),
            $this->get_default_options()
        );
    }

    /**
     * Get default plugin options
     *
     * @since 1.0.0
     * @return array Default options
     */
    private function get_default_options() {
        return array(
            'enable_related_posts' => true,
            'heading' => esc_html__( 'Posts You May Like', 'related-posts' ),
            'posts_per_row' => 3,
            'number_of_posts' => 5,
            'post_order' => 'desc',
            'order_by' => 'date',
            'show_thumbnail' => true,
            'show_date' => true,
            'show_title' => true,
            'show_description' => true,
            'description_length' => 20,
            'show_author' => true
        );
    }

    /**
     * Plugin activation hook
     *
     * @since 1.0.0
     */
    public function activate() {
        if ( !get_option( 'related_posts_settings' ) ) {
            add_option( 'related_posts_settings', $this->get_default_options() );
        }
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation hook
     *
     * @since 1.0.0
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Enqueue plugin styles
     *
     * @since 1.0.0
     */
    public function enqueue_styles() {
        if ( is_single() && $this->options['enable_related_posts'] ) {
            wp_enqueue_style(
                'related-posts-style',
                WDA_RELATEDPOSTS_PLUGIN_URL . 'assets/css/related-posts.css',
                array(),
                WDA_RELATEDPOSTS_VERSION
            );
        }
    }

    /**
     * Display related posts after content
     *
     * @since 1.0.0
     * @param string $content Post content
     * @return string Modified content
     */
    public function display_related_posts($content) {
        if ( !is_single() || !$this->options['enable_related_posts'] ) {
            return $content;
        }

        $related_posts = $this->get_related_posts();
        if ( empty($related_posts) ) {
            return $content;
        }

        $related_posts_html = $this->generate_related_posts_html($related_posts);
        return $content . $related_posts_html;
    }

    /**
     * Get related posts
     *
     * @since 1.0.0
     * @return array Array of post objects
     */
    private function get_related_posts() {
        $post_id = get_the_ID();
        $categories = wp_get_post_categories( $post_id );

        if ( empty( $categories ) ) {
            return array();
        }

        $args = array(
            'category__in' => $categories,
            'post__not_in' => array( $post_id ),
            'posts_per_page' => $this->options['number_of_posts'],
            'orderby' => $this->options['order_by'] === 'random' ? 'rand' : $this->options['order_by'],
            'order' => strtoupper( $this->options['post_order'] ),
            'post_status' => 'publish'
        );

        $query = new \WP_Query( $args );
        return $query->posts;
    }

    /**
     * Generate HTML for related posts
     *
     * @since 1.0.0
     * @param array $posts Array of post objects
     * @return string Generated HTML
     */
    private function generate_related_posts_html( $posts ) {
        ob_start();
        include WDA_RELATEDPOSTS_PLUGIN_PATH . 'includes/templates/related-posts.php';
        return ob_get_clean();
    }
}

// Initialize the plugin
Related_Posts::get_instance();