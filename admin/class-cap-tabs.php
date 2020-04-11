<?php
/**
 * Edit Capabilities tab section on the edit/new role screen.
 */

namespace MembersControl\Admin;

/**
 * Handles building the edit caps tabs.
 */
final class Cap_Tabs {

	public $role;

	public $added_caps = array();

	public $has_caps = array();

	public $sections = array();

	public $controls = array();

	public $sections_json = array();

	public $controls_json = array();

	public function __construct( $role = '', $has_caps = array() ) {

		// Check if there were explicit caps passed in.
		if ( $has_caps )
			$this->has_caps = $has_caps;

		// Check if we have a role.
		if ( $role ) {
			$this->role = memberscontrol_get_role( $role );

			// If no explicit caps were passed in, use the role's caps.
			if ( ! $has_caps )
				$this->has_caps = $this->role->caps;
		}

		// Add sections and controls.
		$this->register();

		// Print custom JS in the footer.
		add_action( 'admin_footer', array( $this, 'localize_scripts' ), 0 );
		add_action( 'admin_footer', array( $this, 'print_templates'  )    );
	}

	/**
	 * Registers the sections (and each section's controls) that will be used for
	 * the tab content.
	 */
	public function register() {

		// Hook before registering.
		do_action( 'memberscontrol_pre_edit_caps_manager_register' );

		$groups = memberscontrol_get_cap_groups();

		uasort( $groups, 'memberscontrol_priority_sort' );

		// Get and loop through the available capability groups.
		foreach ( $groups as $group ) {

			$caps = $group->caps;

			// Remove added caps.
			if ( $group->diff_added )
				$caps = array_diff( $group->caps, $this->added_caps );

			// Add group's caps to the added caps array.
			$this->added_caps = array_unique( array_merge( $this->added_caps, $caps ) );

			// Create a new section.
			$this->sections[] = $section = new Cap_Section( $this, $group->name, array( 'icon' => $group->icon, 'label' => $group->label ) );

			// Get the section json data.
			$this->sections_json[] = $section->json();

			// Create new controls for each cap.
			foreach ( $caps as $cap ) {

				$this->controls[] = $control = new Cap_Control( $this, $cap, array( 'section' => $group->name ) );

				// Get the control json data.
				$this->controls_json[] = $control->json();
			}
		}

		// Create a new "All" section.
		$this->sections[] = $section = new Cap_Section( $this, 'all', array( 'icon' => 'dashicons-plus', 'label' => esc_html__( 'All', 'memberscontrol' ) ) );

		// Get the section json data.
		$this->sections_json[] = $section->json();

		// Create new controls for each cap.
		foreach ( $this->added_caps as $cap ) {

			$this->controls[] = $control = new Cap_Control( $this, $cap, array( 'section' => 'all' ) );

			// Get the control json data.
			$this->controls_json[] = $control->json();
		}

		// Hook after registering.
		do_action( 'memberscontrol_edit_caps_manager_register' );
	}

	/**
	 * Displays the cap tabs.
	 */
	public function display() { ?>

		<div id="tabcapsdiv" class="postbox">

			<h2 class="hndle"><?php printf( esc_html__( 'Edit Capabilities: %s', 'memberscontrol' ), '<span class="memberscontrol-which-tab"></span>' ); ?></h2>

			<div class="inside">

				<div class="memberscontrol-cap-tabs">
					<?php $this->tab_nav(); ?>
					<div class="memberscontrol-tab-wrap"></div>
				</div><!-- .memberscontrol-cap-tabs -->

			</div><!-- .inside -->

		</div><!-- .postbox -->
	<?php }

	/**
	 * Outputs the tab nav.
	 */
	public function tab_nav() { ?>

		<ul class="memberscontrol-tab-nav">

		<?php foreach ( $this->sections as $section ) : ?>

			<?php $icon = preg_match( '/dashicons-/', $section->icon ) ? sprintf( 'dashicons %s', sanitize_html_class( $section->icon ) ) : esc_attr( $section->icon ); ?>

			<li class="memberscontrol-tab-title">
				<a href="<?php echo esc_attr( "#memberscontrol-tab-{$section->section}" ); ?>"><i class="<?php echo $icon; ?>"></i> <span class="label"><?php echo esc_html( $section->label ); ?></span></a>
			</li>

		<?php endforeach; ?>

		</ul><!-- .memberscontrol-tab-nav -->
	<?php }

	/**
	 * Passes our sections and controls data as json to the `edit-role.js` file.
	 */
	public function localize_scripts() {

		wp_localize_script( 'memberscontrol-edit-role', 'memberscontrol_sections', $this->sections_json );
		wp_localize_script( 'memberscontrol-edit-role', 'memberscontrol_controls', $this->controls_json );
	}

	/**
	 * Outputs the Underscore JS templates.
	 */
	public function print_templates() { ?>

		<script type="text/html" id="tmpl-memberscontrol-cap-section">
			<?php memberscontrol_get_underscore_template( 'cap-section' ); ?>
		</script>

		<script type="text/html" id="tmpl-memberscontrol-cap-control">
			<?php memberscontrol_get_underscore_template( 'cap-control' ); ?>
		</script>
	<?php }
}
