/**
 * Module: TYPO3/CMS/L10nmgr/ContextMenuActions
 *
 * JavaScript to handle the click action of the "Hello World" context menu item
 * @exports TYPO3/CMS/ExtensionKey/ContextMenuActions
 */
define(['jquery'], function ($) {
	'use strict';

	/**
	 * @exports TYPO3/CMS/L10nmgr/ContextMenuActions
	 */
	var ContextMenuActions = {};

	/**
	 * open url within content container.
	 * Use data attribute "data-url"
	 *
	 * @param {string} table
	 * @param {int} uid of the page
	 */
	ContextMenuActions.openUrl = function (table, uid) {
		var $url = $(this).data('url');
		if ($url) {
			top.TYPO3.Backend.ContentContainer.setUrl($url);
		}
	};

	return ContextMenuActions;
});
