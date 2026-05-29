/**
 * Term Thumbnails — Admin JS
 *
 * Handles the "Set/Remove thumbnail" UI in:
 *   - Term list table (REST API via api-fetch)
 *   - Add-term form    (hidden input, normal PHP POST)
 *   - Edit-term form   (hidden input, normal PHP POST)
 */

import './admin.css';
import apiFetch from '@wordpress/api-fetch';
import domReady from '@wordpress/dom-ready';

/**
 * @param {HTMLElement|null|undefined} el
 * @param {boolean} visible
 */
function setVisible( el, visible ) {
	if ( ! el ) {
		return;
	}
	el.classList.toggle( 'is-hidden', ! visible );
}

domReady( () => {
	function pickImage( buttonText ) {
		return new Promise( ( resolve ) => {
			const frame = wp.media( {
				title: buttonText,
				library: { type: 'image' },
				button: { text: buttonText },
				multiple: false,
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

	function getRestBase( taxonomySlug ) {
		return window.termThumbnails?.restBases?.[ taxonomySlug ] ?? null;
	}

	function apiSetThumbnail( termId, attachmentId ) {
		const taxonomy = new URLSearchParams( window.location.search ).get(
			'taxonomy'
		);
		const restBase = getRestBase( taxonomy );

		if ( ! restBase ) {
			return Promise.reject(
				new Error(
					`Term Thumbnails: taxonomy "${ taxonomy }" is not available in the REST API.`
				)
			);
		}

		return apiFetch( {
			path: `/wp/v2/${ restBase }/${ termId }`,
			method: 'POST',
			data: { meta: { _thumbnail_id: attachmentId } },
		} );
	}

	function apiDeleteThumbnail( termId ) {
		const taxonomy = new URLSearchParams( window.location.search ).get(
			'taxonomy'
		);
		const restBase = getRestBase( taxonomy );

		if ( ! restBase ) {
			return Promise.reject(
				new Error(
					`Term Thumbnails: taxonomy "${ taxonomy }" is not available in the REST API.`
				)
			);
		}

		return apiFetch( {
			path: `/wp/v2/${ restBase }/${ termId }`,
			method: 'POST',
			data: { meta: { _thumbnail_id: null } },
		} );
	}

	function showPreview( container, attachment ) {
		container.querySelectorAll( '.term-thumbnail' ).forEach( ( el ) => {
			el.remove();
		} );
		container.prepend( buildThumbElement( attachment ) );
		setVisible(
			container.querySelector( '.add-term-thumbnail' ),
			false
		);
		setVisible(
			container.querySelector( '.remove-term-thumbnail' ),
			true
		);
	}

	function clearPreview( container ) {
		container.querySelectorAll( '.term-thumbnail' ).forEach( ( el ) => {
			el.remove();
		} );
		setVisible(
			container.querySelector( '.add-term-thumbnail' ),
			true
		);
		setVisible(
			container.querySelector( '.remove-term-thumbnail' ),
			false
		);
	}

	function getFieldContainer( button ) {
		return button.closest( '.term-thumbnail-field' );
	}

	document.addEventListener( 'click', async ( event ) => {
		const addBtn = event.target.closest( '.add-term-thumbnail' );
		if ( addBtn ) {
			event.preventDefault();
			const container = getFieldContainer( addBtn );

			if ( ! container ) {
				return;
			}
			const termId = addBtn.dataset.termId
				? Number( addBtn.dataset.termId )
				: null;
			const idField = addBtn.dataset.idField
				? document.querySelector( addBtn.dataset.idField )
				: null;

			try {
				const attachment = await pickImage(
					addBtn.textContent.trim()
				);

				if ( termId ) {
					await apiSetThumbnail( termId, attachment.id );
				} else if ( idField ) {
					idField.value = String( attachment.id );
				}

				showPreview( container, attachment );
			} catch ( err ) {
				// eslint-disable-next-line no-console
				console.error(
					'Term Thumbnails: failed to set thumbnail',
					err
				);
			}

			return;
		}

		const removeBtn = event.target.closest( '.remove-term-thumbnail' );
		if ( removeBtn ) {
			event.preventDefault();
			const container = getFieldContainer( removeBtn );

			if ( ! container ) {
				return;
			}
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
				console.error(
					'Term Thumbnails: failed to remove thumbnail',
					err
				);
			}
		}
	} );

	const addTagForm = document.getElementById( 'addtag' );
	const addTagSubmit = addTagForm?.querySelector( '#submit' );

	if ( addTagForm && addTagSubmit ) {
		addTagSubmit.addEventListener( 'click', () => {
			setTimeout( () => {
				if ( addTagForm.querySelector( '#tag-name' )?.value === '' ) {
					const removeBtn = addTagForm.querySelector(
						'.remove-term-thumbnail'
					);
					removeBtn?.click();
				}
			}, 1000 );
		} );
	}
} );
