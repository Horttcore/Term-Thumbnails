import './index.css';
import { registerBlockType } from '@wordpress/blocks';
import metadata from '../block.json';
import Edit from './edit.js';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null, // server-side rendered
} );

/**
 * Term Thumbnails — Admin JS
 *
 * Handles the "Set/Remove thumbnail" UI in:
 *   - Term list table (REST API via api-fetch)
 *   - Add-term form    (hidden input, normal PHP POST)
 *   - Edit-term form   (hidden input, normal PHP POST)
 */

import apiFetch from '@wordpress/api-fetch';
import domReady from '@wordpress/dom-ready';

domReady(() => {
	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Open the WP media picker and resolve with a single attachment object.
	 *
	 * @param {string} buttonText  Text shown on the media-frame select button.
	 * @return {Promise<Object>}   Resolves with the selected attachment's attributes.
	 */
	function pickImage( buttonText ) {
		return new Promise( ( resolve ) => {
			const frame = wp.media( {
				title: buttonText,
				library: { type: 'image' },
				button: { text: buttonText },
				multiple: false, // single selection only
			} );

			frame.on( 'select', () => {
				const attachment = frame.state().get( 'selection' ).first();
				if ( attachment ) {
					resolve( attachment.toJSON() );
				}
			} );

			frame.open();
		} );
	}

	/**
	 * Build a thumbnail <img> element from an attachment object.
	 *
	 * Uses the `thumbnail` size URL when available, falls back to full.
	 *
	 * @param {Object} attachment  Attachment attributes from wp.media.
	 * @return {HTMLElement}
	 */
	function buildThumbElement( attachment ) {
		const img = document.createElement( 'img' );
		const src =
			attachment.sizes?.thumbnail?.url ??
			attachment.sizes?.full?.url ??
			attachment.url;

		img.src = src;
		img.alt = attachment.alt ?? '';
		img.className = 'term-thumbnail';

		const wrapper = document.createElement( 'p' );
		wrapper.className = 'term-thumbnail';
		wrapper.appendChild( img );

		return wrapper;
	}

	// -------------------------------------------------------------------------
	// REST-API backed actions (term list table)
	// -------------------------------------------------------------------------

	/**
	 * Resolve the REST base for a given taxonomy slug.
	 *
	 * Falls back to the raw slug if not found in the server-provided map.
	 * e.g. 'category' → 'categories', 'post_tag' → 'tags'
	 *
	 * @param {string} taxonomySlug  Raw taxonomy slug from the URL query string.
	 * @return {string}
	 */
	function getRestBase( taxonomySlug ) {
		return window.termThumbnails?.restBases?.[ taxonomySlug ] ?? taxonomySlug;
	}

	/**
	 * Set a term thumbnail via the REST API.
	 *
	 * Writes to the core term endpoint:
	 *   POST /wp/v2/{restBase}/{termId}  { meta: { _thumbnail_id: attachmentId } }
	 *
	 * The taxonomy slug is read from the current URL (?taxonomy=…).
	 *
	 * @param {number} termId
	 * @param {number} attachmentId
	 * @return {Promise}
	 */
	function apiSetThumbnail( termId, attachmentId ) {
		const taxonomy = new URLSearchParams( window.location.search ).get( 'taxonomy' );
		const restBase = getRestBase( taxonomy );

		return apiFetch( {
			path: `/wp/v2/${ restBase }/${ termId }`,
			method: 'POST',
			data: { meta: { _thumbnail_id: attachmentId } },
		} );
	}

	/**
	 * Delete a term thumbnail via the REST API.
	 *
	 * @param {number} termId
	 * @return {Promise}
	 */
	function apiDeleteThumbnail( termId ) {
		const taxonomy = new URLSearchParams( window.location.search ).get( 'taxonomy' );
		const restBase = getRestBase( taxonomy );

		return apiFetch( {
			path: `/wp/v2/${ restBase }/${ termId }`,
			method: 'POST',
			data: { meta: { _thumbnail_id: 0 } },
		} );
	}

	// -------------------------------------------------------------------------
	// UI helpers
	// -------------------------------------------------------------------------

	/**
	 * Show the thumbnail preview inside a button's parent container.
	 *
	 * Removes any existing preview first to avoid duplicates.
	 *
	 * @param {HTMLElement} container  The element that wraps both buttons and the preview.
	 * @param {Object}      attachment Attachment attributes.
	 */
	function showPreview( container, attachment ) {
		container.querySelector( '.term-thumbnail' )?.remove();
		container.prepend( buildThumbElement( attachment ) );
		container.querySelector( '.add-term-thumbnail' )?.classList.add( 'hidden' );
		container.querySelector( '.remove-term-thumbnail' )?.classList.remove( 'hidden' );
	}

	/**
	 * Clear the thumbnail preview inside a button's parent container.
	 *
	 * @param {HTMLElement} container
	 */
	function clearPreview( container ) {
		container.querySelector( '.term-thumbnail' )?.remove();
		container.querySelector( '.add-term-thumbnail' )?.classList.remove( 'hidden' );
		container.querySelector( '.remove-term-thumbnail' )?.classList.add( 'hidden' );
	}

	// -------------------------------------------------------------------------
	// Event delegation
	// -------------------------------------------------------------------------

	document.addEventListener( 'click', async ( event ) => {
		// --- Add thumbnail ---
		const addBtn = event.target.closest( '.add-term-thumbnail' );
		if ( addBtn ) {
			event.preventDefault();
			const container = addBtn.parentElement;
			const termId = addBtn.dataset.termId ? Number( addBtn.dataset.termId ) : null;
			const idField = addBtn.dataset.idField
				? document.querySelector( addBtn.dataset.idField )
				: null;

			try {
				const attachment = await pickImage( addBtn.textContent.trim() );

				if ( termId ) {
					// List-table row: persist immediately via REST.
					await apiSetThumbnail( termId, attachment.id );
				} else if ( idField ) {
					// Add/edit form: store in hidden input, saved on PHP form submit.
					idField.value = attachment.id;
				}

				showPreview( container, attachment );
			} catch ( err ) {
				// eslint-disable-next-line no-console
				console.error( 'Term Thumbnails: failed to set thumbnail', err );
			}

			return;
		}

		// --- Remove thumbnail ---
		const removeBtn = event.target.closest( '.remove-term-thumbnail' );
		if ( removeBtn ) {
			event.preventDefault();
			const container = removeBtn.parentElement;
			const termId = removeBtn.dataset.termId
				? Number( removeBtn.dataset.termId )
				: null;
			const idField = removeBtn.dataset.idField
				? document.querySelector( removeBtn.dataset.idField )
				: null;

			try {
				if ( termId ) {
					await apiDeleteThumbnail( termId );
				} else if ( idField ) {
					idField.value = '';
				}

				clearPreview( container );
			} catch ( err ) {
				// eslint-disable-next-line no-console
				console.error( 'Term Thumbnails: failed to remove thumbnail', err );
			}
		}
	} );

	// -------------------------------------------------------------------------
	// Add-term form: clear thumbnail after successful submission
	// -------------------------------------------------------------------------

	const addTagForm = document.getElementById( 'addtag' );
	const addTagSubmit = addTagForm?.querySelector( '#submit' );

	if ( addTagForm && addTagSubmit ) {
		addTagSubmit.addEventListener( 'click', () => {
			// Wait for WP to reset the form (it empties fields ~500 ms after submit).
			setTimeout( () => {
				if ( addTagForm.querySelector( '#tag-name' )?.value === '' ) {
					const removeBtn = addTagForm.querySelector( '.remove-term-thumbnail' );
					removeBtn?.click();
				}
			}, 1000 );
		} );
	}
} );
