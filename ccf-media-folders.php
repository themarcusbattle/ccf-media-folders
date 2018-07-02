<?php
/**
 * Plugin Name: Custom Contact Forms Media Folders
 * Version: 0.1.0
 * Author: Marcus Battle, 10up
 * Description: Organize media uploaded via Custom Contact Forms into folders in the WordPress Media Library.
 */

class CCF_Media_Folders {

    /**
     * The unique instance of the plugin.
     *
     * @var CCF_Media_Folders
     */
    private static $instance;

    /**
     * The instance of CCF_Media_Folders.
     *
     * @since 0.1.0
     */
    public static function get_instance() {

        if (null === self::$instance) {
            self::$instance = new self();
        }
 
        return self::$instance;
    }

    /**
     * The hooks.
     *
     * @since 0.1.0
     *
     * @author Marcus Battle
     */
    public function hooks() {
        add_action( 'init', [ $this, 'create_media_folder_taxonomy' ], 0 );
        add_action( 'attachment_updated', [ $this, 'assign_attachment_to_folder' ], 10, 3 );
    }

    /**
     * Creates a 'folder' taxonomy for WP Attachments.
     *
     * @since 0.1.0
     *
     * @author Marcus Battle
     */
    public function create_media_folder_taxonomy() {

        // @TODO: Check to see if term exits

        register_taxonomy(
            'folder',
            'attachment',
            array(
                'label' => __( 'Folders', 'custom-contact-forms' ),
                'rewrite' => array( 'slug' => 'folders' ),
                'hierarchical' => true,
            )
        );
    }

    /**
     * Assigns the uploaded media to a folder that matches the Custom Contact Form of the submission.
     *
     * @since 0.1.0
     *
     * @author Marcus Battle
     *
     * @param int     $post_ID      Post ID.
     * @param WP_Post $post_after   Post object following the update.
     * @param WP_Post $post_before  Post object before the update.
     */
    public function assign_attachment_to_folder( int $post_ID, WP_Post $post_after, WP_Post $post_before ) {

        if ( ! $post_after->post_parent ) {
            return;
        }

        // Only assign category to the upload if it was uploaded during a form submission.
        if ( 'ccf_submission' === get_post_type( $post_after->post_parent ) ) {

            $media_folder = $this->create_media_folder_on_upload( $post_after );

            wp_set_object_terms( $post_ID, $media_folder, 'folder' );
        }

    }

    /**
     * Creates a 'folder' to match the post name of the Custom Contact Form.
     *
     * @since 0.1.0
     *
     * @author Marcus Battle
     *
     * @param WP_Post $post_name The Submission Post.
     *
     * @return string $media_folder The 'folder' term that matches the Custom Contact Form post_name.
     */
    private function create_media_folder_on_upload( WP_Post $submission = null ) : string {

        $media_folder = '';

        // Get the Custom Contact Form ID.
        $form_ID = isset( $submission->post_parent ) ? wp_get_post_parent_id( $submission->post_parent ) : 0;

        // Return if no Custom Contact Form exists.
        if ( ! $form_ID ) {
            return $media_folder;
        }

        // Return if no title has been set for the form.
        if ( ! $form_title = get_the_title( $form_ID ) ) {
            return $media_folder;
        }

        // Create the slug for the media folder.
        $media_folder = sanitize_title( $form_title );

        // Check to see if the term exists.
        if ( $folder_exists = term_exists( $media_folder, 'folder' ) ) {
            return $media_folder;
        }

        // Create the media 'folder'.
        $folder = wp_insert_term( $form_title, 'folder' );

        return $media_folder;
    }
}

add_action( 'plugins_loaded', array( CCF_Media_Folders::get_instance(), 'hooks' ) );