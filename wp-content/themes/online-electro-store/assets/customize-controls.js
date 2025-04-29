( function( api ) {

	// Extends our custom "online-electro-store" section.
	api.sectionConstructor['online-electro-store'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );