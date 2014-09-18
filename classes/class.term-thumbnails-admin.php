<?php
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}



/**
 *  Term Thumbnails Class
 */
class Term_Thumbnails_Admin
{



	/**
	 * Version number
	 *
	 * @var string
	 **/
	protected $version = '1.0.0';



	/**
	 *
	 * Constructor
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since 1.0.0
	 */
	public function __construct()
	{

		add_action( 'wp_loaded', array( $this, 'register_tax_hooks' ) );
		add_action( 'admin_print_scripts-edit-tags.php', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'delete_term', array( $this, 'delete_term' ), 10, 4 );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_ajax_delete-term-thumbnail', array( $this, 'ajax_delete_term_thumbnail' ) );
		add_action( 'wp_ajax_get-term-thumbnail', array( $this, 'ajax_get_term_thumbnail' ) );
		add_action( 'wp_ajax_set-term-thumbnail', array( $this, 'ajax_set_term_thumbnail' ) );

	} // end __construct



	/**
	 * Register javascripts
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function admin_enqueue_scripts()
	{

		wp_register_script( 'term-thumbnails', plugins_url( '../javascript/term-thumbnails.js', __FILE__ ), array(), $this->version, TRUE );
		wp_enqueue_script( 'term-thumbnails' );

	} // end admin_enqueue_scripts



	/**
	 * Term thumbnail on add tag screen
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function add_form_fields()
	{

		wp_enqueue_media();

		$taxonomy = get_taxonomy( $_GET['taxonomy'] );
		$taxonomy = $taxonomy->labels->singular_name;

		?>

		<div class="form-field">
			<label for="term-thumbnail"><?php _e( 'Thumbnail' ); ?></label>
			<div>
				<a class="button remove-term-thumbnail" id="remove-term-thumbnail-new" href="#" data-id-field="#term-thumbnail-id-new" style="display: none"><?php printf( __( 'Remove %s image', 'term-thumbnails' ), $taxonomy ); ?></a>
				<a class="button add-term-thumbnail" href="#" data-id-field="#term-thumbnail-id-new"><?php printf( __( 'Set %s image', 'term-thumbnails' ), $taxonomy ); ?></a>

				<input name="term-thumbnail-id" value="" id="term-thumbnail-id-new" type="hidden">
			</div>
		</div>

		<?php

	} // end add_form_fields



	/**
	 * Display term image
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function ajax_delete_term_thumbnail()
	{

		$this->delete_term_thumbnail( $_REQUEST['term_id'] );

	} // end ajax_get_term_thumbnail



	/**
	 * Display term image
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function ajax_get_term_thumbnail()
	{

		die( '<p class="term-thumbnail">' . wp_get_attachment_image( $_REQUEST['attachment_id'], 'thumbnail' ) . '</p>' );

	} // end ajax_get_term_thumbnail



	/**
	 * Set term image
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function ajax_set_term_thumbnail()
	{

		$this->set_term_thumbnail( $_REQUEST['term_id'], $_REQUEST['attachment_id'] );
		die( '<p class="term-thumbnail">' . wp_get_attachment_image( $_REQUEST['attachment_id'], 'thumbnail' ) . '</p>' );

	} // end ajax_get_term_thumbnail



	/**
	 * Save new term thumbnail
	 *
	 * @access public
	 * @param int $term_id Term ID
	 * @param int $tt_id Taxonomy term ID
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function created_term_thumbnail( $term_id = FALSE, $tt_id = FALSE )
	{

		$this->set_term_thumbnail( $term_id, $_POST['term-thumbnail-id'] );

	} // end created_term_thumbnail



	/**
	 * Cleanup after term is deleted
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function delete_term( $term, $tt_id, $taxonomy, $deleted_term )
	{

		$this->delete_term_thumbnail( $term );

	} // end delete_term



	/**
	 * Delete term thumbnail
	 *
	 * @access protected
	 * @param int $term_id Term ID
	 * @param int $attachment_id Attachment ID
	 * @author Ralf Hortt
	 * @since  1.0.0
	 **/
	protected function delete_term_thumbnail( $term_id )
	{

		$term_id = intval( $term_id );

		if ( 0 == $term_id )
			return;

		$options = get_option( 'term-thumbnails' );
		unset( $options[$term_id] );
		update_option( 'term-thumbnails', $options );

	} // end delete_term_thumbnail



	/**
	 * Tag Color input field on edit tag screen
	 *
	 * @access public
	 * @param obj $tag Tag object
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function edit_form_fields( $tag )
	{

		wp_enqueue_media();
		$term_id = $tag->term_id;
		$taxonomy = get_taxonomy( $_GET['taxonomy'] );
		$taxonomy = $taxonomy->labels->singular_name;
		?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term-thumbnail"><?php _e( 'Thumbnail' ); ?></label>
			</th>
			<td>

				<?php if ( has_term_thumbnail( $term_id ) ) : ?>
					<p class="term-thumbnail">
						<?php echo get_term_thumbnail( $term_id, 'thumbnail' ) ?>
					</p>
				<?php endif; ?>

				<a class="button remove-term-thumbnail" id="remove-term-thumbnail-<?php echo $term_id ?>" href="#" data-id-field="#term-thumbnail-id-<?php echo $term_id ?>" <?php if ( !has_term_thumbnail( $term_id ) ) echo 'style="display: none"'; ?>><?php printf( __( 'Remove %s image', 'term-thumbnails' ), $taxonomy ); ?></a>
				<a class="button add-term-thumbnail" href="#" data-id-field="#term-thumbnail-id-<?php echo $term_id ?>" <?php if ( has_term_thumbnail( $term_id ) ) echo 'style="display: none"'; ?>><?php printf( __( 'Set %s image', 'term-thumbnails' ), $taxonomy ); ?></a>

				<input name="term-thumbnail-id" value="<?php if ( has_term_thumbnail( $term_id ) ) echo get_term_thumbnail_id( $term_id ) ?>" id="term-thumbnail-id-<?php echo $term_id ?>" type="hidden">
			</td>
		</tr>

		<?php

	} // end edit_form_fields



	/**
	 * Save term thumbnail
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function edit_term()
	{
		if ( isset( $_POST['term-thumbnail-id'] ) && '' == $_POST['term-thumbnail-id'] )
			$this->delete_term_thumbnail( $_POST['tag_ID'] );
		else
			$this->set_term_thumbnail( $_POST['tag_ID'], $_POST['term-thumbnail-id'] );

	} // end edit term



	/**
	 * Load plugin textdomain
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain( 'term-thumbnails', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );

	} // end load_plugin_textdomain



	/**
	 * Add tag color column to taxonomies
	 *
	 * @access public
	 * @param array $columns Columns
	 * @return array Columns
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function manage_edit_taxonomy_custom_column( $columns )
	{

		$columns['thumbnail'] = __( 'Thumbnail' );
		return $columns;

	} // end manage_edit_taxonomy_custom_column



	/**
	 * Display thumbnail on taxonomies screen
	 *
	 * @access public
	 * @param str $content
	 * @param str $column_name Column name
	 * @param int $term_id Term ID
	 * @return str Content
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function manage_taxonomy_custom_column( $content, $column_name, $term_id )
	{

		$taxonomy = get_taxonomy( $_GET['taxonomy'] );
		$taxonomy = $taxonomy->labels->singular_name;

		switch ( $column_name ) :

			case 'thumbnail':


				echo get_term_thumbnail( $term_id, 'thumbnail', array( 'class' => 'term-thumbnail' ) );

				?>

				<a class="button remove-term-thumbnail" href="#" data-ajax="1" data-id="<?php echo $term_id ?>" <?php if ( !has_term_thumbnail( $term_id ) ) echo 'style="display: none"'; ?>><?php printf( __( 'Remove %s image', 'term-thumbnails' ), $taxonomy ); ?></a>
				<a class="button add-term-thumbnail" href="#" data-ajax="1" data-id="<?php echo $term_id ?>" <?php if ( has_term_thumbnail( $term_id ) ) echo 'style="display: none"'; ?>><?php printf( __( 'Set %s image', 'term-thumbnails' ), $taxonomy ); ?></a>

				<?php

			break;

		endswitch;

	} // end manage_taxonomy_custom_column



	/**
	 * Set term thumbnail
	 *
	 * @access protected
	 * @param int $term_id Term ID
	 * @param int $attachment_id Attachment ID
	 * @author Ralf Hortt
	 * @since  1.0.0
	 **/
	protected function set_term_thumbnail( $term_id, $attachment_id )
	{

		$term_id = intval( $term_id );
		$attachment_id = intval( $attachment_id );

		if ( 0 == $term_id || 0 == $attachment_id )
			return;

		$options = get_option( 'term-thumbnails' );
		$options[$term_id] = $attachment_id;
		update_option( 'term-thumbnails', $options );

	} // end set_term_thumbnail



	/**
	 * Register hooks
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since  1.0.0
	 **/
	public function register_tax_hooks()
	{

		$taxonomies = apply_filters( 'term-thumbnail-taxonomies', get_taxonomies() );

		foreach ( $taxonomies as $taxonomy ) :

			if ( FALSE === apply_filters( $taxonomy . '-has-thumbnails', TRUE ) )
				continue;

			add_action( $taxonomy . '_add_form_fields', array( $this, 'add_form_fields' ) );
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_form_fields' ) );
			add_action( 'edited_' . $taxonomy, array( $this , 'edit_term' ) );
			add_action( 'created_' . $taxonomy, array( $this , 'created_term_thumbnail' ) );
			add_filter( 'manage_edit-' . $taxonomy . '_columns', array( $this, 'manage_edit_taxonomy_custom_column' ) );
			add_filter( 'manage_' . $taxonomy . '_custom_column', array( $this, 'manage_taxonomy_custom_column' ), 10, 3 );

		endforeach;

	} // end register_tax_hooks



}

new Term_Thumbnails_Admin;
