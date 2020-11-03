import SessionHelper from '../helpers/session-helper';

export default class APIHelper {
	constructor() {
		this.$ = jQuery;
		this.sessionHelper = new SessionHelper();
	}

	getCart() {
		return this.$.ajax( {
			method: "GET",
			url: this.getCartUrl(),
			dataType: 'json',
			timeout: 5000,
		} );
	}

	generateUrl( urlPath ) {
		if ( ! window.ll_vdm_options.api.client_name ) {
			return null;
		}

		return window.ll_vdm_options.api.base_url + encodeURIComponent( window.ll_vdm_options.api.client_name ) + '/' + urlPath.replace( /^\/+|\/+$/g, '' );
	}

	getCartUrl() {
		let urlPath = '/cart/';
		const sessionId = this.sessionHelper.getId();
		if ( sessionId ) {
			urlPath += sessionId;
		}
		return this.generateUrl( urlPath );
	}
}
