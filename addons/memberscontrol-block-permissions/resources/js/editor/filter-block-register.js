/**
 * Block registration filter.
 */

const { assign }    = lodash;
const { addFilter } = wp.hooks;

addFilter( 'blocks.registerBlockType', 'memberscontrol/block/permissions/register', ( settings, name ) => {

	settings.attributes = assign( settings.attributes, {
		blockPermissionsCondition: {
			type: 'string'
		},
		blockPermissionsType: {
			type: 'string'
		},
		blockPermissionsUserStatus: {
			type: 'string'
		},
		blockPermissionsRoles: {
			type: 'array'
		},
		blockPermissionsCap: {
			type: 'string'
		},
		blockPermissionsMessage: {
			type: 'string'
		}
	} );

	return settings;
} );
