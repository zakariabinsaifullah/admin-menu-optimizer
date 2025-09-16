<?php
/**
 * Plugin Name: Admin Menu Optimizer
 * Description: Allow you to reorder, rename, admin menu items.
 * Version: 1.0.1
 * Author: Zakaria Binsaifullah
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: admin-menu-optimizer
 */

// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Final Class Admin_Menu_Optimizer
 * Main plugin class.
 * 
 * @since 1.0.0
 */
final class Admo_Admin_Menu_Optimizer {
    
        /**
        * Plugin version.
        * 
        * @var string
        */
        const VERSION = '1.0.1';

        /**
         * Instance of this class.
         * 
         * @var Admo_Admin_Menu_Optimizer
         * 
         * @since 1.0.0
         */
        private static $instance = null;
    
        /**
        * Class constructor.
        */
        private function __construct() {
            $this->define_constants();
            add_action('plugins_loaded', [$this, 'init_plugin']);
        }

        /**
         * Define the required plugin constants.
         * 
         * @return void
         */
        public function define_constants() {
            define('ADMO_VERSION', self::VERSION);
            define('ADMO_FILE', __FILE__);
            define('ADMO_PATH', __DIR__);
            define('ADMO_URL', plugins_url('', ADMO_FILE));
            define('ADMO_ASSETS', ADMO_URL . '/assets');
        }

        /**
         * Initialize the plugin.
         * 
         * @return void
         */
        public function init_plugin() {
            add_action('admin_menu', [$this, 'add_admin_menu']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
            add_action('wp_ajax_admo_save_order', [$this, 'save_order']);
            add_filter('custom_menu_order', '__return_true');
            add_filter('menu_order', [$this, 'apply_custom_order']);
            // Hook the AJAX handler.
            add_action('wp_ajax_admo_save_title', [$this, 'save_title']);
            add_action('admin_menu', [$this, 'apply_custom_titles'], 100);
        }

    /**
     * Add a top-level menu for the plugin.
     * 
     * @return void
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Admin Menu Optimizer', 'admin-menu-optimizer'), 
            __('Menu Optimizer', 'admin-menu-optimizer'),      
            'manage_options',                                  
            'admin-menu-optimizer',                            
            [$this, 'settings_page'],                         
            'dashicons-menu',                     
        );
    }

    /**
     * Display the settings page.
     */
    public function settings_page() {
        global $menu;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Admin Menu Optimizer', 'admin-menu-optimizer'); ?></h1>
            <div class="our-products">
                <span class="products-label">
                    <?php esc_html_e('Our Products', 'admin-menu-optimizer'); ?>
                </span>
                <div class="products-list">
                    <a href="https://gutslider.com/" target="_blank" class="product-item">
                        <?php esc_html_e('GutSlider', 'admin-menu-optimizer'); ?>
                    </a>
                    <a href="https://gmap.gutenbergkits.com/" target="_blank" class="product-item">
                        <?php esc_html_e('Gmap', 'admin-menu-optimizer'); ?>
                    </a>
                    <a href="https://accordion.gutenbergkits.com/" target="_blank" class="product-item">
                        <?php esc_html_e('Easy Accordion', 'admin-menu-optimizer'); ?>
                    </a>
                </div>
            </div>
            <p><?php esc_html_e('Drag and drop to reorder admin menu items. You can also rename items. Click "Save Changes" to apply.', 'admin-menu-optimizer'); ?></p>
            <ul id="admo-menu-list">
                <?php
                    $serial = 0;
                    foreach ($menu as $item) {
                        $serial++;
                        $title = $item[0];
                        if (empty($title)) {
                            continue;
                        }
                        ?>
                        <li data-slug="<?php echo esc_attr($item[2]); ?>">
                            <span class="menu-order"><?php echo esc_html($serial); ?></span>
                            <span class="menu-title">
                                <?php 
                                    // remove numbers from the title
                                    $title = preg_replace('/[0-9]+/', '', $title);
                                    echo wp_kses( $title, [] );
                                ?>
                            </span>
                            <button class="edit-menu-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#2271b1" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                </svg>
                            </button>
                        </li>
                        <?php
                    }
                ?>
            </ul>
            <button id="admo-save-order" class="button button-primary"><?php esc_html_e('Save Changes', 'admin-menu-optimizer'); ?></button>
        </div>
        <?php
    }


    /**
     * Enqueue scripts and styles.
     * 
     * @param string $hook The current admin page.
     * 
     * @return void
     */
    public function enqueue_scripts($hook) {
        if ($hook === 'toplevel_page_admin-menu-optimizer') {
            wp_enqueue_script('sortable-js', ADMO_ASSETS . '/js/sortable.min.js', [], ADMO_VERSION, true);
            wp_enqueue_style('alert-box', ADMO_ASSETS . '/css/alertbox.css', [], ADMO_VERSION, 'all');
            wp_enqueue_script('alert-box', ADMO_ASSETS . '/js/alertbox.js', [], ADMO_VERSION, true);
            wp_enqueue_script('admo-script', ADMO_ASSETS . '/js/admo-script.js', ['sortable-js', 'jquery', 'alert-box'], ADMO_VERSION, true);
            wp_enqueue_style('admo-style', ADMO_ASSETS . '/css/admo-style.css', [], ADMO_VERSION, 'all');
            wp_localize_script('admo-script', 'admo_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('admo_nonce'),
            ]);
        }
    }

    /**
     * Save menu order via AJAX.
     * 
     * @return void
     */
    public function save_order() {
        // Verify nonce first
        check_ajax_referer('admo_nonce', 'nonce');
        
        // Sanitize the entire $_POST['order'] input
        $order = isset($_POST['order']) ? 
            array_map('sanitize_text_field', wp_unslash($_POST['order'])) : 
            null;
        
        // Additional validation
        if (!is_array($order) || empty($order)) {
            wp_send_json_error(esc_html__('Invalid order data.', 'admin-menu-optimizer'));
            exit;
        }
    
        // Validate that all items are strings or numeric
        $validated_order = array_filter($order, function($item) {
            return is_string($item) || is_numeric($item);
        });
    
        // Ensure the filtered array matches the original
        if (count($validated_order) !== count($order)) {
            wp_send_json_error(esc_html__('Invalid order items.', 'admin-menu-optimizer'));
            exit;
        }
        
        // Save the sanitized order
        update_option('admo_menu_order', $validated_order);
        wp_send_json_success(esc_html__('Menu order saved.', 'admin-menu-optimizer'));
        exit;
    }

    /**
     * Save menu item title via AJAX.
     * 
     * @return void
     */
    public function save_title() {
        // Verify nonce first
        check_ajax_referer('admo_nonce', 'nonce');
    
        // Sanitize and validate inputs in one step
        $slug = isset($_POST['slug']) ? 
            sanitize_text_field(wp_unslash($_POST['slug'])) : 
            null;
        $new_title = isset($_POST['new_title']) ? 
            sanitize_text_field(wp_unslash($_POST['new_title'])) : 
            null;
    
        // Comprehensive input validation
        if (empty($slug) || empty($new_title)) {
            wp_send_json_error(esc_html__('Invalid data. Slug and title are required.', 'admin-menu-optimizer'));
            exit;
        }
    
        // Additional validation for slug and title
        if (strlen($slug) > 100 || strlen($new_title) > 100) {
            wp_send_json_error(esc_html__('Slug or title is too long.', 'admin-menu-optimizer'));
            exit;
        }
    
        // Retrieve and update stored titles
        $stored_titles = get_option('admo_menu_titles', []);
        $stored_titles[$slug] = $new_title;
    
        // Update option with sanitized data
        update_option('admo_menu_titles', $stored_titles);
    
        wp_send_json_success(esc_html__('Menu title updated.', 'admin-menu-optimizer'));
        exit;
    }

    /**
     * Apply custom menu titles.
     * 
     * @return void
     */
    public function apply_custom_titles() {
        global $menu;

        $stored_titles = get_option('admo_menu_titles', []);

        foreach ($menu as &$item) {
            $slug = $item[2];
            if (!empty($stored_titles[$slug])) {
                $item[0] = $stored_titles[$slug];
            }
        }
    }

    /**
     * Apply custom menu order.
     * 
     * @param array $menu_order The current menu order.
     * 
     * @return array
     */
    public function apply_custom_order($menu_order) {
        $stored_order = get_option('admo_menu_order', []);
        if (!empty($stored_order)) {
            return $stored_order;
        }
        return $menu_order;
    }

    /**
     * Run the plugin.
     * 
     * @return Admo_Admin_Menu_Optimizer
     */
    public static function init() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

/**
 * Initialize the plugin.
 * 
 * @return Admo_Admin_Menu_Optimizer
 */
function admo_admin_menu_optimizer() {
    return Admo_Admin_Menu_Optimizer::init();
}

// Kick-off the plugin.
admo_admin_menu_optimizer();