<?php
/**
 * Admin Menu Handler
 *
 * @package    RelatedPosts
 * @subpackage Admin
 * @since      1.0.0
 */

namespace WDA_RelatedPosts;

// If this file is called directly, abort.
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Class Admin_Menu
 * Handles the admin interface for Related Posts plugin
 *
 * @since 1.0.0
 */
class Admin_Menu {
    /**
     * Option name
     *
     * @since 1.0.0
     * @var string
     */
    private $option_name = 'related_posts_settings';

    /**
     * Option group
     *
     * @since 1.0.0
     * @var string
     */
    private $option_group = 'related_posts_settings_group';

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings') );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Register the admin menu
     *
     * @since 1.0.0
     */
    public function add_plugin_menu() {
        add_menu_page(
            esc_html__( 'Related Posts Settings', 'related-posts' ),
            esc_html__( 'Related Posts', 'related-posts' ),
            'manage_options',
            'related-posts-settings',
            array( $this, 'render_settings_page' ),
            'dashicons-grid-view',
            30
        );
    }

    /**
     * Register plugin settings
     *
     * @since 1.0.0
     */
    public function register_settings() {
        register_setting(
            $this->option_group,
            $this->option_name,
            array(
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
                'default' => $this->get_default_options()
            )
        );
    }

    /**
     * Enqueue admin assets
     *
     * @since 1.0.0
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_related-posts-settings' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'related-posts-admin',
            WDA_RELATEDPOSTS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WDA_RELATEDPOSTS_VERSION
        );
    }

    /**
     * Get default options
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
     * Sanitize settings
     *
     * @since 1.0.0
     * @param array $input The values to sanitize
     * @return array Sanitized values
     */
    public function sanitize_settings( $input ) {
        if ( !is_array( $input ) ) {
            return $this->get_default_options();
        }

        $sanitized = array();

        // Enable/Disable
        $sanitized['enable_related_posts'] = isset( $input['enable_related_posts'] );

        // Text fields
        $sanitized['heading'] = sanitize_text_field( $input['heading'] ?? '' );

        // Numeric fields
        $sanitized['posts_per_row'] = absint( $input['posts_per_row'] ?? 3 );
        $sanitized['number_of_posts'] = absint( $input['number_of_posts'] ?? 5 );
        $sanitized['description_length'] = absint( $input['description_length'] ?? 20 );

        // Validate and sanitize order options
        $valid_orders = array( 'asc', 'desc' );
        $sanitized['post_order'] = in_array( $input['post_order'], $valid_orders ) ? $input['post_order'] : 'desc';

        $valid_orderby = array( 'date', 'title', 'random' );
        $sanitized['order_by'] = in_array( $input['order_by'], $valid_orderby ) ? $input['order_by'] : 'date';

        // Checkboxes
        $sanitized['show_thumbnail'] = isset( $input['show_thumbnail'] );
        $sanitized['show_date'] = isset( $input['show_date'] );
        $sanitized['show_title'] = isset( $input['show_title'] );
        $sanitized['show_description'] = isset( $input['show_description'] );
        $sanitized['show_author'] = isset( $input['show_author'] );

        return $sanitized;
    }

    /**
     * Render the settings page
     *
     * @since 1.0.0
     */
    public function render_settings_page() {
        if ( !current_user_can( 'manage_options' )) {
            return;
        }

        $options = get_option( $this->option_name, $this->get_default_options() );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <?php settings_errors(); ?>

            <form method="post" action="options.php" class="related-posts-settings-form">
                <?php
                settings_fields( $this->option_group );
                do_settings_sections( 'related-posts-settings' );
                ?>
                
                <div class="related-posts-settings-grid">
                    <!-- General Settings -->
                    <div class="settings-column">
                        <h2 class="settings-title"><?php esc_html_e( 'General Settings', 'related-posts' ); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="enable_related_posts">
                                        <?php esc_html_e( 'Enable Related Posts', 'related-posts' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" 
                                               id="enable_related_posts"
                                               name="<?php echo esc_attr( $this->option_name ); ?>[enable_related_posts]"
                                               <?php checked( $options['enable_related_posts'], true ); ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <p class="description">
                                        <?php esc_html_e( 'Enable or disable related posts display.', 'related-posts' ); ?>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="heading">
                                        <?php esc_html_e( 'Heading Text', 'related-posts' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="heading"
                                           class="regular-text"
                                           name="<?php echo esc_attr( $this->option_name ); ?>[heading]"
                                           value="<?php echo esc_attr( $options['heading'] ); ?>">
                                    <p class="description">
                                        <?php esc_html_e( 'The heading text shown above related posts.', 'related-posts' ); ?>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="posts_per_row">
                                        <?php esc_html_e( 'Posts per Row', 'related-posts' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="posts_per_row"
                                            name="<?php echo esc_attr( $this->option_name ); ?>[posts_per_row]">
                                        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                            <option value="<?php echo esc_attr( $i ); ?>"
                                                    <?php selected( $options['posts_per_row'], $i ); ?>>
                                                <?php echo esc_html( $i ); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e( 'Number of posts to display per row.', 'related-posts' ); ?>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="number_of_posts">
                                        <?php esc_html_e( 'Number of Posts', 'related-posts' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number"
                                           id="number_of_posts"
                                           class="small-text"
                                           name="<?php echo esc_attr( $this->option_name ); ?>[number_of_posts]"
                                           value="<?php echo esc_attr( $options['number_of_posts'] ); ?>"
                                           min="1"
                                           max="20">
                                    <p class="description">
                                        <?php esc_html_e( 'Total number of related posts to display.', 'related-posts' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Display Settings -->
                    <div class="settings-column">
                        <h2 class="settings-title"><?php esc_html_e( 'Display Options', 'related-posts' ); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Post Elements', 'related-posts' ); ?></th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="checkbox"
                                                   name="<?php echo esc_attr( $this->option_name ); ?>[show_thumbnail]"
                                                   <?php checked( $options['show_thumbnail'], true ); ?>>
                                            <?php esc_html_e( 'Show Featured Image', 'related-posts' ); ?>
                                        </label><br>

                                        <label>
                                            <input type="checkbox"
                                                   name="<?php echo esc_attr( $this->option_name ); ?>[show_title]"
                                                   <?php checked( $options['show_title'], true ); ?>>
                                            <?php esc_html_e( 'Show Title', 'related-posts' ); ?>
                                        </label><br>

                                        <label>
                                            <input type="checkbox"
                                                   name="<?php echo esc_attr( $this->option_name ); ?>[show_date]"
                                                   <?php checked( $options['show_date'], true ); ?>>
                                            <?php esc_html_e( 'Show Date', 'related-posts' ); ?>
                                        </label><br>

                                        <label>
                                            <input type="checkbox"
                                                   name="<?php echo esc_attr( $this->option_name ); ?>[show_author]"
                                                   <?php checked( $options['show_author'], true ); ?>>
                                            <?php esc_html_e( 'Show Author', 'related-posts' ); ?>
                                        </label><br>

                                        <label>
                                            <input type="checkbox"
                                                   name="<?php echo esc_attr( $this->option_name ); ?>[show_description]"
                                                   <?php checked( $options['show_description'], true ); ?>>
                                            <?php esc_html_e( 'Show Description', 'related-posts' ); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="description_length">
                                        <?php esc_html_e( 'Description Length', 'related-posts' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number"
                                           id="description_length"
                                           class="small-text"
                                           name="<?php echo esc_attr( $this->option_name ); ?>[description_length]"
                                           value="<?php echo esc_attr( $options['description_length'] ); ?>"
                                           min="1"
                                           max="100">
                                    <p class="description">
                                        <?php esc_html_e( 'Number of words in the description.', 'related-posts' ); ?>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="post_order">
                                        <?php esc_html_e( 'Post Order', 'related-posts' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="post_order"
                                            name="<?php echo esc_attr( $this->option_name ); ?>[post_order]">
                                        <option value="desc" <?php selected( $options['post_order'], 'desc' ); ?>>
                                            <?php esc_html_e( 'Descending', 'related-posts' ); ?>
                                        </option>
                                        <option value="asc" <?php selected( $options['post_order'], 'asc' ); ?>>
                                            <?php esc_html_e( 'Ascending', 'related-posts' ); ?>
                                        </option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="order_by">
                                        <?php esc_html_e( 'Order By', 'related-posts' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="order_by" name="<?php echo esc_attr( $this->option_name ); ?>[order_by]">
                                        <option value="date" <?php selected( $options['order_by'], 'date' ); ?>>
                                            <?php esc_html_e( 'Date', 'related-posts' ); ?>
                                        </option>
                                        <option value="title" <?php selected( $options['order_by'], 'title' ); ?>>
                                            <?php esc_html_e( 'Title', 'related-posts' ); ?>
                                        </option>
                                        <option value="random" <?php selected( $options['order_by'], 'random' ); ?>>
                                            <?php esc_html_e( 'Random', 'related-posts' ); ?>
                                        </option>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e( 'Choose how to order the related posts.', 'related-posts' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <?php
    }
}