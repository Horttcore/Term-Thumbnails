import {
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	FocalPointPicker,
	PanelBody,
	SelectControl,
	__experimentalToolsPanelItem as ToolsPanelItem,
	__experimentalToolsPanel as ToolsPanel,
} from '@wordpress/components';
import { useEntityProp, useEntityRecord } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { image as imageIcon } from '@wordpress/icons';
import { Placeholder, Spinner } from '@wordpress/components';

const scaleOptions = [
	{ value: 'cover',   label: __( 'Cover',   'term-thumbnails' ) },
	{ value: 'contain', label: __( 'Contain', 'term-thumbnails' ) },
	{ value: 'fill',    label: __( 'Fill',    'term-thumbnails' ) },
];

const aspectRatioOptions = [
	{ label: __( 'Original',          'term-thumbnails' ), value: ''    },
	{ label: __( 'Square - 1:1',      'term-thumbnails' ), value: '1'   },
	{ label: __( 'Standard - 4:3',    'term-thumbnails' ), value: '4/3' },
	{ label: __( 'Portrait - 3:4',    'term-thumbnails' ), value: '3/4' },
	{ label: __( 'Classic - 3:2',     'term-thumbnails' ), value: '3/2' },
	{ label: __( 'Classic Portrait - 2:3', 'term-thumbnails' ), value: '2/3' },
	{ label: __( 'Wide - 16:9',       'term-thumbnails' ), value: '16/9' },
	{ label: __( 'Tall - 9:16',       'term-thumbnails' ), value: '9/16' },
];

/**
 * Convert a FocalPointPicker value ({ x, y } in 0–1 range)
 * to a CSS object-position string, e.g. "30% 75%".
 */
function focalPointToObjectPosition( focalPoint ) {
	if ( ! focalPoint ) return undefined;
	return `${ Math.round( focalPoint.x * 100 ) }% ${ Math.round( focalPoint.y * 100 ) }%`;
}

export default function Edit( { attributes, setAttributes, context } ) {
	const { aspectRatio, scale, sizeSlug, width, height, focalPoint } = attributes;
	const { taxonomy, termId } = context;

	const blockProps = useBlockProps();

	const [ meta ] = useEntityProp( 'taxonomy', taxonomy, 'meta', termId );
	const attachmentId = meta?._thumbnail_id;

	const { record: media, isResolving } = useEntityRecord(
		'root',
		'media',
		attachmentId || 0
	);

	// Available image sizes from the media record.
	const imageSizeOptions = media?.media_details?.sizes
		? Object.keys( media.media_details.sizes ).map( ( size ) => ( {
				value: size,
				label: size
					.replace( /_/g, ' ' )
					.replace( /\b\w/g, ( c ) => c.toUpperCase() ),
		  } ) )
		: [];

	// Source URL for the selected size.
	const src =
		media?.media_details?.sizes?.[ sizeSlug ]?.source_url ??
		media?.media_details?.sizes?.full?.source_url ??
		media?.source_url;

	// Focal point is meaningful when the image is cropped (aspect ratio set
	// with cover/fill scale, or when explicit dimensions clip the image).
	const showFocalPoint = !! aspectRatio && scale !== 'contain';

	// Inline styles applied to the <img>.
	const imgStyle = {
		...( aspectRatio
			? { aspectRatio, objectFit: scale, height: '100%' }
			: {} ),
		...( width  ? { width  } : {} ),
		...( height ? { height } : {} ),
		...( showFocalPoint && focalPoint
			? { objectPosition: focalPointToObjectPosition( focalPoint ) }
			: {} ),
	};

	const imgClassName = aspectRatio ? 'has-aspect-ratio' : undefined;

	const controls = (
		<InspectorControls>
			<ToolsPanel
				label={ __( 'Settings', 'term-thumbnails' ) }
				resetAll={ () =>
					setAttributes( {
						aspectRatio:  undefined,
						scale:        'cover',
						width:        undefined,
						height:       undefined,
						focalPoint:   undefined,
					} )
				}
			>
				<ToolsPanelItem
					label={ __( 'Aspect ratio', 'term-thumbnails' ) }
					isShownByDefault
					hasValue={ () => !! aspectRatio }
					onDeselect={ () =>
						setAttributes( { aspectRatio: undefined, focalPoint: undefined } )
					}
				>
					<SelectControl
						label={ __( 'Aspect ratio', 'term-thumbnails' ) }
						value={ aspectRatio ?? '' }
						options={ aspectRatioOptions }
						onChange={ ( value ) =>
							setAttributes( {
								aspectRatio: value || undefined,
								// Clear focal point when removing the ratio.
								focalPoint: value ? focalPoint : undefined,
							} )
						}
					/>
				</ToolsPanelItem>

				{ aspectRatio && (
					<ToolsPanelItem
						label={ __( 'Scale', 'term-thumbnails' ) }
						isShownByDefault
						hasValue={ () => scale !== 'cover' }
						onDeselect={ () => setAttributes( { scale: 'cover' } ) }
					>
						<SelectControl
							label={ __( 'Scale', 'term-thumbnails' ) }
							value={ scale }
							options={ scaleOptions }
							onChange={ ( value ) =>
								setAttributes( {
									scale: value,
									// Focal point only makes sense for cover/fill.
									focalPoint: value === 'contain' ? undefined : focalPoint,
								} )
							}
						/>
					</ToolsPanelItem>
				) }

				{ showFocalPoint && (
					<ToolsPanelItem
						label={ __( 'Focal point', 'term-thumbnails' ) }
						isShownByDefault={ false }
						hasValue={ () => !! focalPoint }
						onDeselect={ () => setAttributes( { focalPoint: undefined } ) }
					>
						<FocalPointPicker
							label={ __( 'Focal point', 'term-thumbnails' ) }
							url={ src }
							value={ focalPoint ?? { x: 0.5, y: 0.5 } }
							onChange={ ( value ) =>
								setAttributes( { focalPoint: value } )
							}
						/>
					</ToolsPanelItem>
				) }
			</ToolsPanel>

			{ imageSizeOptions.length > 0 && (
				<PanelBody title={ __( 'Image size', 'term-thumbnails' ) }>
					<SelectControl
						label={ __( 'Resolution', 'term-thumbnails' ) }
						value={ sizeSlug }
						options={ imageSizeOptions }
						onChange={ ( value ) =>
							setAttributes( { sizeSlug: value } )
						}
						help={ __(
							'Select the image size to use in the block.',
							'term-thumbnails'
						) }
					/>
				</PanelBody>
			) }
		</InspectorControls>
	);

	// ---- Placeholder states ----

	if ( ! termId || ! taxonomy ) {
		return (
			<>
				{ controls }
				<figure { ...blockProps }>
					<Placeholder
						icon={ imageIcon }
						label={ __( 'Term Thumbnail', 'term-thumbnails' ) }
						instructions={ __(
							'This block displays the thumbnail of the current taxonomy term. Place it inside a Query Loop or a taxonomy archive template.',
							'term-thumbnails'
						) }
					/>
				</figure>
			</>
		);
	}

	if ( meta === undefined || isResolving ) {
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

	if ( ! attachmentId || ! media ) {
		return (
			<>
				{ controls }
				<figure { ...blockProps }>
					<Placeholder
						icon={ imageIcon }
						label={ __( 'Term Thumbnail', 'term-thumbnails' ) }
						instructions={ __(
							'No thumbnail set for this term.',
							'term-thumbnails'
						) }
					/>
				</figure>
			</>
		);
	}

	return (
		<>
			{ controls }
			<figure { ...blockProps }>
				<img
					src={ src }
					alt={ media.alt_text ?? '' }
					className={ imgClassName }
					style={ imgStyle }
				/>
			</figure>
		</>
	);
}
