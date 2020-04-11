<?php
/**
 * Add new/custom capability meta box.
 */

namespace MembersControl\Admin;

/**
 * Class to handle the new cap meta box on the edit/new role screen.
 */
final class Meta_Box_Custom_Cap {

	private static $instance;

	protected function __construct() {

		add_action( 'memberscontrol_load_role_edit', array( $this, 'load' ) );
		add_action( 'memberscontrol_load_role_new',  array( $this, 'load' ) );
	}

	public function load() {

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	public function add_meta_boxes( $screen_id, $role = '' ) {

		// If role isn't editable, bail.
		if ( $role && ! memberscontrol_is_role_editable( $role ) )
			return;

		// Add the meta box.
		add_meta_box( 'newcapdiv', esc_html__( 'Custom Capability', 'memberscontrol' ), array( $this, 'meta_box' ), $screen_id, 'side', 'core' );
	}

	/**
	 * Outputs the meta box HTML.
	 */
	public function meta_box() { ?>

		<p>
			<input type="text" id="memberscontrol-new-cap-field" class="widefat" />
		</p>

		<p>
			<button type="button" class="button-secondary" id="memberscontrol-add-new-cap"><?php echo esc_html_x( 'Add New', 'capability', 'memberscontrol' ); ?></button>
		</p>
	<?php }

	/**
	 * Returns the instance.
	 */
	public static function get_instance() {

		if ( ! self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
}

Meta_Box_Custom_Cap::get_instance();
