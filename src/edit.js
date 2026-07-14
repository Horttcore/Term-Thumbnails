import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	Placeholder,
	SelectControl,
	Spinner,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { image as imageIcon } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

const aspectRatioOptions = [
	{ label: __( 'Original', 'term-thumbnails' ), value: '' },
	{ label: __( 'Square - 1:1', 'term-thumbnails' ), value: '1' },
	{ label: __( 'Standard - 4:3', 'term-thumbnails' ), value: '4/3' },
	{ label: __( 'Portrait - 3:4', 'term-thumbnails' ), value: '3/4' },
	{ label: __( 'Classic - 3:2', 'term-thumbnails' ), value: '3/2' },
	{ label: __( 'Classic Portrait - 2:3', 'term-thumbnails' ), value: '2/3' },
	{ label: __( 'Wide - 16:9', 'term-thumbnails' ), value: '16/9' },
	{ label: __( 'Tall - 9:16', 'term-thumbnails' ), value: '9/16' },
];

const scaleOptions = [
	{ label: __( 'Cover', 'term-thumbnails' ), value: 'cover' },
	{ label: __( 'Contain', 'term-thumbnails' ), value: 'contain' },
	{ label: __( 'Fill', 'term-thumbnails' ), value: 'fill' },
];

function getImageSource( media, sizeSlug ) {
	return (
		media?.media_details?.sizes?.[ sizeSlug ]?.source_url ||
		media?.media_details?.sizes?.full?.source_url ||
		media?.source_url ||
		''
	);
}

export default function Edit( { attributes, setAttributes, context } ) {
	const {
		aspectRatio,
		height,
		isLink,
		linkTarget,
		rel,
		scale,
		sizeSlug,
		width,
	} = attributes;
	const taxonomy = context?.taxonomy || '';
	const termId = Number( context?.termId || 0 );
	const termData = context?.termData;
	const contextTermLink = termData?.link || '';
	const contextAttachmentId = Number(
		termData?.meta?.term_thumbnail_id || 0
	);

	const { attachmentId, media, termLink, isLoading } = useSelect(
		( select ) => {
			if ( ! taxonomy || ! termId ) {
				return {
					attachmentId: 0,
					media: null,
					termLink: '',
					isLoading: false,
				};
			}

			const core = select( coreStore );
			const term = core.getEditedEntityRecord(
				'taxonomy',
				taxonomy,
				termId
			);
			const nextAttachmentId =
				contextAttachmentId ||
				Number( term?.meta?.term_thumbnail_id || 0 );
			const record = nextAttachmentId
				? core.getEntityRecord(
						'postType',
						'attachment',
						nextAttachmentId
				  )
				: null;

			return {
				attachmentId: nextAttachmentId,
				media: record,
				termLink: contextTermLink || term?.link || '',
				isLoading: term === undefined || record === undefined,
			};
		},
		[ taxonomy, termId, contextAttachmentId, contextTermLink ]
	);

	const source = getImageSource( media, sizeSlug );
	const blockProps = useBlockProps( {
		style: { width, height, aspectRatio },
	} );
	const imageStyle = {
		...( aspectRatio
			? {
					width: '100%',
					height: '100%',
					objectFit: scale || 'cover',
			  }
			: {} ),
		...( ! aspectRatio && height ? { height } : {} ),
	};

	const imageSizes = media?.media_details?.sizes
		? [
				{ label: __( 'Full size', 'term-thumbnails' ), value: 'full' },
				...Object.keys( media.media_details.sizes ).map( ( size ) => ( {
					label: size.replace( /_/g, ' ' ),
					value: size,
				} ) ),
		  ]
		: [ { label: __( 'Full size', 'term-thumbnails' ), value: 'full' } ];

	const controls = (
		<InspectorControls>
			<PanelBody title={ __( 'Image settings', 'term-thumbnails' ) }>
				<SelectControl
					label={ __( 'Image size', 'term-thumbnails' ) }
					value={ sizeSlug || 'post-thumbnail' }
					options={ imageSizes }
					onChange={ ( value ) =>
						setAttributes( { sizeSlug: value } )
					}
				/>
				<SelectControl
					label={ __( 'Aspect ratio', 'term-thumbnails' ) }
					value={ aspectRatio || '' }
					options={ aspectRatioOptions }
					onChange={ ( value ) =>
						setAttributes( {
							aspectRatio: value || undefined,
						} )
					}
				/>
				{ aspectRatio && (
					<SelectControl
						label={ __( 'Scale', 'term-thumbnails' ) }
						value={ scale || 'cover' }
						options={ scaleOptions }
						onChange={ ( value ) =>
							setAttributes( {
								scale: value,
							} )
						}
					/>
				) }
				<ToggleControl
					label={ __( 'Link to term', 'term-thumbnails' ) }
					checked={ Boolean( isLink ) }
					onChange={ ( value ) => setAttributes( { isLink: value } ) }
				/>
				{ isLink && (
					<>
						<ToggleControl
							label={ __( 'Open in new tab', 'term-thumbnails' ) }
							checked={ linkTarget === '_blank' }
							onChange={ ( value ) =>
								setAttributes( {
									linkTarget: value ? '_blank' : '_self',
								} )
							}
						/>
						<TextControl
							label={ __( 'Link relation', 'term-thumbnails' ) }
							value={ rel || '' }
							onChange={ ( value ) =>
								setAttributes( { rel: value } )
							}
						/>
					</>
				) }
			</PanelBody>
		</InspectorControls>
	);

	if ( ! taxonomy || ! termId ) {
		return (
			<>
				{ controls }
				<figure { ...blockProps }>
					<Placeholder
						icon={ imageIcon }
						label={ __( 'Term Thumbnail', 'term-thumbnails' ) }
						instructions={ __(
							'Place this block inside a term template or terms query.',
							'term-thumbnails'
						) }
						withIllustration
					/>
				</figure>
			</>
		);
	}

	if ( isLoading ) {
		return (
			<>
				{ controls }
				<figure { ...blockProps }>
					<Placeholder
						icon={ imageIcon }
						label={ __( 'Term Thumbnail', 'term-thumbnails' ) }
					>
						<Spinner />
					</Placeholder>
				</figure>
			</>
		);
	}

	if ( ! attachmentId || ! media || ! source ) {
		return (
			<>
				{ controls }
				<figure { ...blockProps }>
					<Placeholder
						icon={ imageIcon }
						label={ __( 'Term Thumbnail', 'term-thumbnails' ) }
						instructions={ __(
							'Set a thumbnail on the taxonomy term to preview it here.',
							'term-thumbnails'
						) }
						withIllustration
					/>
				</figure>
			</>
		);
	}

	return (
		<>
			{ controls }
			<figure { ...blockProps }>
				{ isLink && termLink ? (
					<a
						href={ termLink }
						target={ linkTarget }
						rel={ rel || undefined }
					>
						<img
							src={ source }
							alt={ media.alt_text || '' }
							className={
								aspectRatio ? 'has-aspect-ratio' : undefined
							}
							style={ imageStyle }
						/>
					</a>
				) : (
					<img
						src={ source }
						alt={ media.alt_text || '' }
						className={
							aspectRatio ? 'has-aspect-ratio' : undefined
						}
						style={ imageStyle }
					/>
				) }
			</figure>
		</>
	);
}
