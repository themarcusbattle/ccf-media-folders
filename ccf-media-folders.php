<?php
/**
 * Plugin Name: Constant Contact Forms File Uploads
 * Version: 0.1.0
 * Author: Marcus Battle, 10up
 */

class CCC_File_Uploads {

    /**
     * The unique instance of the plugin.
     *
     * @var WP_Kickass_Plugin
     */
    private static $instance;

    public static function get_instance() {

        if (null === self::$instance) {
            self::$instance = new self();
        }
 
        return self::$instance;
    }

    public function hooks() {
        add_action( 'init', [ $this, 'create_media_taxonomies' ], 0 );
        add_action( 'attachment_updated', [ $this, 'assign_attachment_to_folder' ], 10, 3 );
        // add_action( 'wp_insert_post_parent', [ $this, 'assign_attachment_to_folder' ], 10, 4 );
    }

    public function create_media_taxonomies() {

        register_taxonomy(
            'folder',
            'attachment',
            array(
                'label' => __( 'Folder' ),
                'rewrite' => array( 'slug' => 'folder' ),
                'hierarchical' => true,
            )
        );
    }

    public function assign_attachment_to_folder( $post_ID, $post_after, $post_before ) {
        
        if ( 'ccf_submission' === get_post_type( $post_after->post_parent ) ) {
            $folder_set = wp_set_object_terms( $post_ID, 'share-your-story', 'folder' );
        }
        echo '<pre>'; print_r( $folder_set );
        echo 'wait'; exit;
    }
}

add_action( 'plugins_loaded', array( CCC_File_Uploads::get_instance(), 'hooks' ) );