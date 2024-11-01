<?php

/**
 * Wikify Settings API
 *
 */
if ( !class_exists('Wikify_Settings_API' ) ):
class Wikify_Settings_API {

    private $settings_api;

    function __construct() {
        $this->settings_api = new WeDevs_Settings_API;

        add_action( 'admin_init', array($this, 'wikify_admin_init') );
        add_action( 'admin_menu', array($this, 'wikify_admin_menu') );
    }

    function wikify_admin_init() {
		//Remember: In the future, admins may be allowed to wikify more than one CPT, so the settings page shouldn't be attached to the wp-admin's Wiki CPT section (or any other).
        //set the settings
        $this->settings_api->set_sections( $this->wikify_get_settings_sections() );
        $this->settings_api->set_fields( $this->get_wikify_settings_fields() );
        flush_rewrite_rules();

        //initialize settings
        $this->settings_api->admin_init();
    }

    function wikify_admin_menu() {
        add_options_page( 'Wikify', 'Wikify', 'delete_posts', 'wikify', array($this, 'wikify_plugin_page') );
    }

    function wikify_get_settings_sections() {
        $sections = array(
            array(
                'id'    => 'wikify_basic',
                'title' => __( 'General', 'wikify' )
            )           
        );

        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_wikify_settings_fields() {

		
		// GET CREATE PAGE ID
		$createPageID = wikify_option( "create_page_id", "wikify_basic", "0" );
		if(intval($createPageID) == 0) {
			$createPageID = intval(get_post_by_meta_value("wikifyPageType", "create", "page"));
			if($createPageID == 0){
				$createPageID = wp_insert_post( 
					array(
						'post_author' => 1,
						'post_name' => 'create',
						'post_status' => 'publish' ,
						'post_title' => 'Create New Wiki',
						'post_type' => 'page',		
					)
				);
				update_post_meta(intval($createPageID), "wikifyPageType", "create");				
			}
		}
    
		
        $settings_fields = array(
            'wikify_basic' => array(         
                array(
                    'name'              => 'wiki_slug',
                    'label'             => __( 'Wiki Slug', 'wikify' ),
                    'desc'              => __( 'E.g. https://example.com/wiki/hello-world/.', 'wikify' ),
                    'type'              => 'text',
                    'default'           => 'wiki',
                    'sanitize_callback' => 'sanitize_text_field'
                ),              
                array(
                    'name'              => 'create_page_id',
                    'label'             => __( 'Create Page', 'wikify' ),
                    'desc'              => __( 'This is the WP page that will be used for the "Create Page" editor.', 'wikify' ),
                    'type'              => 'select',
                    'default'           => $createPageID,
                    'options' 			=> wikify_get_pages()
                )			
            )		
        );
             

        return $settings_fields;
    }

    function wikify_plugin_page() {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

}
endif;
