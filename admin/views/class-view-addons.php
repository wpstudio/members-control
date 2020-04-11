<?php
/**
 * Handles the add-ons settings view.
 */

namespace MembersControl\Admin;

/**
 * Sets up and handles the add-ons settings view.
 */
class View_Addons extends View {

	public function enqueue() {
		wp_enqueue_style( 'memberscontrol-admin' );
		wp_enqueue_script( 'memberscontrol-settings' );
		wp_localize_script( 'memberscontrol-settings', 'memberscontrolAddons', array(
			'nonce' => wp_create_nonce( 'mbrs_toggle_addon' )
		) );
	}

	public function template() {

		require_once( memberscontrol_plugin()->dir . 'admin/class-addon.php'      );
		require_once( memberscontrol_plugin()->dir . 'admin/functions-addons.php' );
		add_thickbox();

		do_action( 'memberscontrol_register_addons' );

		$addons = memberscontrol_get_addons(); ?>

		<div class="widefat">

			<div class="memberscontrol-addons">
				
				<?php if ( $addons ) : ?>

					<?php foreach ( $addons as $addon ) : ?>
						
						<?php
							$this->addon_card( $addon );
						?>

					<?php endforeach; ?>

				<?php else : ?>

					<div class="error notice">
						<p>
							<strong><?php esc_html_e( 'There are currently no add-ons to show. Please try again later.', 'memberscontrol' ); ?></strong>
						</p>
					</div>

				<?php endif; ?>

			</div>

		</div><!-- .widefat -->

		<script>
			jQuery(document).ready(function($) {
				$('.mepr-upgrade-activate-link').click(function(e){
					var url = $(this).data('url');
					$('#mepr_cta_upgrade_link').prop('href', url);
				});
			});
		</script>
	<?php }

	/**
	 * Renders an individual add-on plugin card.
	 */
	public function addon_card( $addon ) { ?>

		<div class="plugin-card memberscontrol-addon plugin-card-<?php echo esc_attr( $addon->name ); ?>">

			<div class="plugin-card-top">

				<div class="name column-name">
					<h3>
						<?php if ( $addon->url ) : ?>
							<a href="<?php echo esc_url( $addon->url ); ?>" target="_blank">
						<?php endif; ?>

							<?php echo esc_html( $addon->title ); ?>

							<?php if ( file_exists( memberscontrol_plugin()->dir . "img/{$addon->name}.svg" ) ) : ?>

								<span class="plugin-icon memberscontrol-svg-link">
									<?php include memberscontrol_plugin()->dir . "img/{$addon->name}.svg"; ?>
								</span>

							<?php elseif ( $addon->icon_url ) : ?>

								<img class="plugin-icon" src="<?php echo esc_url( $addon->icon_url ); ?>" alt="" />

							<?php endif; ?>

						<?php if ( $addon->url ) : ?>
							</a>
						<?php endif; ?>
					</h3>
				</div>

				<div class="desc column-description" style="margin-right:0;">
					<?php echo wpautop( wp_kses_post( $addon->excerpt ) ); ?>
				</div>

				<div class="addon-activate">
					<span class="activate-toggle activate-addon" data-addon="<?php echo $addon->name; ?>">
						<svg aria-hidden="true" class="<?php echo memberscontrol_is_addon_active( $addon->name ) ? 'active' : ''; ?>" focusable="false" data-prefix="fas" data-icon="toggle-on" class="svg-inline--fa fa-toggle-on fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M384 64H192C86 64 0 150 0 256s86 192 192 192h192c106 0 192-86 192-192S490 64 384 64zm0 320c-70.8 0-128-57.3-128-128 0-70.8 57.3-128 128-128 70.8 0 128 57.3 128 128 0 70.8-57.3 128-128 128z"></path></svg>
						<span class="action-label"><?php echo memberscontrol_is_addon_active( $addon->name ) ? esc_html__( 'Active', 'memberscontrol' ) : esc_html__( 'Activate', 'memberscontrol' ); ?></span>
					</span>
				</div>

			</div><!-- .plugin-card-top -->

		</div><!-- .plugin-card -->

	<?php }

	/**
	 * Adds help tabs.
	 */
	public function add_help_tabs() {

		// Get the current screen.
		$screen = get_current_screen();

		// Roles/Caps help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'overview',
				'title'    => esc_html__( 'Overview', 'memberscontrol' ),
				'callback' => array( $this, 'help_tab_overview' )
			)
		);

		// Roles/Caps help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'download',
				'title'    => esc_html__( 'Download', 'memberscontrol' ),
				'callback' => array( $this, 'help_tab_download' )
			)
		);

		// Roles/Caps help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'purchase',
				'title'    => esc_html__( 'Purchase', 'memberscontrol' ),
				'callback' => array( $this, 'help_tab_purchase' )
			)
		);

		// Set the help sidebar.
		$screen->set_help_sidebar( memberscontrol_get_help_sidebar_text() );
	}

	/**
	 * Displays the overview help tab.
	 */
	public function help_tab_overview() { ?>

		<p>
			<?php esc_html_e( 'The Add-Ons screen allows you to view available add-ons for the MembersControl plugin. You can download some plugins directly. Others may be available to purchase.', 'memberscontrol' ); ?>
		</p>
	<?php }

	/**
	 * Displays the download help tab.
	 */
	public function help_tab_download() { ?>

		<p>
			<?php esc_html_e( 'Some plugins may be available for direct download. In such cases, you can click the download button to get a ZIP file of the plugin.', 'memberscontrol' ); ?>
		</p>
	<?php }

	/**
	 * Displays the purchase help tab.
	 */
	public function help_tab_purchase() { ?>

		<p>
			<?php esc_html_e( 'Some add-ons may require purchase before downloading them. Clicking the purchase button will take you off-site to view the add-on in more detail.', 'memberscontrol' ); ?>
		</p>
	<?php }
}
