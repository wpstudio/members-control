<?php
/**
 * Handles the settings screen.
 */

namespace MembersControl\Admin;

/**
 * Sets up and handles the plugin settings screen.
 */
final class Settings_Page {

	public $name = 'memberscontrol-settings';

	public $settings_page = '';

	public $views = array();

	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	private function __construct() {}

	private function includes() {

		// Include the settings functions.
		require_once( memberscontrol_plugin()->dir . 'admin/functions-settings.php' );

		// Load settings view classes.
		require_once( memberscontrol_plugin()->dir . 'admin/views/class-view.php'         );
		require_once( memberscontrol_plugin()->dir . 'admin/views/class-view-general.php' );
		require_once( memberscontrol_plugin()->dir . 'admin/views/class-view-addons.php'  );
	}

	private function setup_actions() {

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 25 );
		add_action( 'wp_ajax_mbrs_toggle_addon', array( $this, 'toggle_addon' ) );
	}

	public function toggle_addon() {
		
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mbrs_toggle_addon' ) ) {
			die();
		}

		$addon = ! empty( $_POST['addon'] ) ? sanitize_text_field( $_POST['addon'] ) : false;

		if ( false === $addon ) {
			wp_send_json_error( array(
				'msg' => esc_html__( 'No add-on provided.', 'memberscontrol' )
			) );
		}

		// Grab the currently active add-ons
		$active_addons = get_option( 'memberscontrol_active_addons', array() );

		if ( ! in_array( $addon, $active_addons ) ) { // Activate the addon
			$active_addons[] = $addon;
			$response = array(
				'status' => 'active',
				'action_label' => esc_html__( 'Active', 'memberscontrol' ),
				'msg' => esc_html__( 'Add-on activated', 'memberscontrol' )
			);

			// Run the add-on's activation hook
			memberscontrol_plugin()->run_addon_activator( $addon );

		} else { // Deactivate the addon
			$key = array_search( $addon, $active_addons );
			unset( $active_addons[$key] );
			$response = array(
				'status' => 'inactive',
				'action_label' => esc_html__( 'Activate', 'memberscontrol' ),
				'msg' => esc_html__( 'Add-on deactivated', 'memberscontrol' )
			);
		}

		update_option( 'memberscontrol_active_addons', $active_addons );

		wp_send_json_success( $response );
	}

	/**
	 * Register a view.
	 */
	public function register_view( $view ) {

		if ( ! $this->view_exists( $view->name ) )
			$this->views[ $view->name ] = $view;
	}

	/**
	 * Unregister a view.
	 */
	public function unregister_view( $name ) {

		if ( $this->view_exists( $name ) )
			unset( $this->view[ $name ] );
	}

	/**
	 * Get a view object
	 */
	public function get_view( $name ) {

		return $this->view_exists( $name ) ? $this->views[ $name ] : false;
	}

	/**
	 * Check if a view exists.
	 */
	public function view_exists( $name ) {

		return isset( $this->views[ $name ] );
	}

	/**
	 * Sets up custom admin menus.
	 */
	public function admin_menu() {

		// Create the settings page.
		$this->settings_page = add_submenu_page( 'memberscontrol', esc_html_x( 'Settings', 'admin screen', 'memberscontrol' ), esc_html_x( 'Settings', 'admin screen', 'memberscontrol' ), apply_filters( 'memberscontrol_settings_capability', 'manage_options' ), 'memberscontrol-settings', array( $this, 'settings_page' ) );
		$this->addons_page = add_submenu_page( 'memberscontrol', esc_html_x( 'Add-Ons', 'admin screen', 'memberscontrol' ), _x( '<span style="color: #8CBD5A;">Add-Ons</span>', 'admin screen', 'memberscontrol' ), apply_filters( 'memberscontrol_settings_capability', 'manage_options' ), 'memberscontrol-settings&view=add-ons', array( $this, 'settings_page' ) );

		if ( $this->settings_page ) {

			do_action( 'memberscontrol_register_settings_views', $this );

			uasort( $this->views, 'memberscontrol_priority_sort' );

			// Register setings.
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			// Page load callback.
			add_action( "load-{$this->settings_page}", array( $this, 'load' ) );

			// Enqueue scripts/styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		}
	}

	/**
	 * Runs on page load.
	 */
	public function load() {

		// Print custom styles.
		add_action( 'admin_head', array( $this, 'print_styles' ) );

		// Add help tabs for the current view.
		$view = $this->get_view( memberscontrol_get_current_settings_view() );

		if ( $view ) {
			$view->load();
			$view->add_help_tabs();
		}
	}

	/**
	 * Print styles to the header.
	 */
	public function print_styles() { ?>

		<style type="text/css">
			
		</style>
	<?php }

	/**
	 * Enqueue scripts/styles.
	 */
	public function enqueue( $hook_suffix ) {

		if ( $this->settings_page !== $hook_suffix )
			return;

		$view = $this->get_view( memberscontrol_get_current_settings_view() );

		if ( $view )
			$view->enqueue();
	}

	/**
	 * Registers the plugin settings.
	 */
	function register_settings() {

		foreach ( $this->views as $view )
			$view->register_settings();
	}

	/**
	 * Renders the settings page.
	 */
	public function settings_page() { ?>

		<div class="wrap">
			<h1><?php echo esc_html_x( 'MembersControl', 'admin screen', 'memberscontrol' ); ?></h1>

			<?php $this->get_view( memberscontrol_get_current_settings_view() )->template(); ?>

		</div><!-- wrap -->
	<?php }


	/**
	 * Outputs the list of views.
	 */
	private function filter_links() { ?>

		<ul class="filter-links">

			<?php foreach ( $this->views as $view ) :

				// Determine current class.
				$class = $view->name === memberscontrol_get_current_settings_view() ? 'class="current"' : '';

				// Get the URL.
				$url = memberscontrol_get_settings_view_url( $view->name );

				if ( 'general' === $view->name )
					$url = remove_query_arg( 'view', $url ); ?>

				<li class="<?php echo sanitize_html_class( $view->name ); ?>">
					<a href="<?php echo esc_url( $url ); ?>" <?php echo $class; ?>><?php echo esc_html( $view->label ); ?></a>
				</li>

			<?php endforeach; ?>

		</ul>
	<?php }

	/**
	 * Adds help tabs.
	 * @return     void
	 */
	public function add_help_tabs() {}
}

Settings_Page::get_instance();
