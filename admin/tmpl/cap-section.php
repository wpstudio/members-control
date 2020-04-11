<?php
/**
 * Underscore JS template for edit capabilities tab sections.
 */
?>
<div id="memberscontrol-tab-{{ data.id }}" class="{{ data.class }}">

	<table class="wp-list-table widefat fixed memberscontrol-roles-select">

		<thead>
			<tr>
				<th class="column-cap"><?php esc_html_e( 'Capability', 'memberscontrol' ); ?></th>
				<th class="column-grant"><?php esc_html_e( 'Grant', 'memberscontrol' ); ?></th>
				<th class="column-deny"><?php esc_html_e( 'Deny', 'memberscontrol' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="column-cap"><?php esc_html_e( 'Capability', 'memberscontrol' ); ?></th>
				<th class="column-grant"><?php esc_html_e( 'Grant', 'memberscontrol' ); ?></th>
				<th class="column-deny"><?php esc_html_e( 'Deny', 'memberscontrol' ); ?></th>
			</tr>
		</tfoot>

		<tbody></tbody>
	</table>
</div>
