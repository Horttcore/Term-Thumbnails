import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder, Spinner } from '@wordpress/components';
import { useEntityProp, useEntityRecord } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { image as imageIcon } from '@wordpress/icons';

export default function Edit( { context } ) {
	const { taxonomy, termId } = context;
	const blockProps = useBlockProps();

	// Read _thumbnail_id from the term's REST meta.
	// This works because MetaRegistration.php calls register_term_meta
	// with show_in_rest: true for every supported taxonomy.
	const [ meta ] = useEntityProp( 'taxonomy', taxonomy, 'meta', termId );
	const attachmentId = meta?._thumbnail_id;

	// Fetch the media entity so we have the URL and alt text.
	const { record: media, isResolving } = useEntityRecord(
		'root',
		'media',
		attachmentId || 0
	);

	// No term in context yet (e.g. block dropped outside a Query Loop).
	if ( ! termId || ! taxonomy ) {
		return (
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
		);
	}

	// Meta is still loading.
	if ( meta === undefined || isResolving ) {
		return (
			<figure { ...blockProps }>
				<Placeholder
					icon={ imageIcon }
					label={ __( 'Term Thumbnail', 'term-thumbnails' ) }
				>
					<Spinner />
				</Placeholder>
			</figure>
		);
	}

	// Term has no thumbnail set.
	if ( ! attachmentId || ! media ) {
		return (
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
		);
	}

	const src =
		media.media_details?.sizes?.[ 'post-thumbnail' ]?.source_url ??
		media.media_details?.sizes?.full?.source_url ??
		media.source_url;

	return (
		<figure { ...blockProps }>
			<img
				src={ src }
				alt={ media.alt_text ?? '' }
			/>
		</figure>
	);
}
