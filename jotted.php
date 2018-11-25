<?php

namespace Jotted;

/*
  Plugin Name:    Jotted
  Plugin URI:     https://compsci.rocks
  Description:    Shortcode plugin that lets you embed a Jotted HTML / CSS editor into a WordPress post or page
  Version:        0.0.1
  Author:         CompSci.rocks
  Author URI:     https://compsci.rocks
  Text Domain:    jotted
 */

Jotted::init();

/**
 *     add_settings_field( 'our_first_field', 'Field Name', array( $this, 'field_callback' ), 'smashing_fields', 'our_first_section' );

 */
class Jotted {
    /**
     * Add any actions needed on the admin side. 
     */
    public static function init() {
        add_action( 'admin_menu', '\Jotted\Jotted::register_settings', 10 );
        add_action( 'admin_init', '\Jotted\Jotted::setup_sections' );
        add_action( 'admin_init', '\Jotted\Jotted::settings_fields' );
        
        add_shortcode('jotted', '\Jotted\Jotted::shortcode');
    }

    public static function register_settings() {
        $page_title = __( 'Jotted Plugin Settings', 'jotted' );
        $menu_title = __( 'Jotted', 'jotted' );
        $capability = 'manage_options';
        $slug = 'jotted';
        $callback = '\Jotted\Jotted::settings_page';

        add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
    }

    public static function setup_sections() {
        add_settings_section( 'jotted_settings', false, false, 'jotted_fields' );
        //add_settings_field( 'jotted_field_style', 'Field Name', '\Jotted\Jotted::settings_fields', 'jotted_fields', 'jotted_settings' );
    }

    public static function settings_fields( $args ) {
        $fields = [
            [
                'uid' => 'jotted_style',
                'label' => __( 'CSS Style', 'jotted' ),
                'section' => 'jotted_settings',
                'type' => 'text',
                'options' => false,
                'placeholder' => '',
                'helper' => '',
                'supplemental' => __( 'Default CSS styling for Jotted editor' ),
                'default' => 'width:100%;height:400px;border:1px solid silver;',
                'field_style' => 'width: 400px;'
            ],
            [
                'uid' => 'jotted_highlight',
                'label' => __( 'Editor highlighter', 'jotted' ),
                'section' => 'jotted_settings',
                'type' => 'select',
                'options' => [
                    'none' => __( 'None', 'jotted' ),
                    'ace' => __( 'Ace Editor', 'jotted' ),
                    'codemirror' => __( 'Code Mirror', 'jotted' )
                ],
                'default' => 'ace',
                'helper' => '',
                'supplemental' => ''
            ],
            [
                'uid' => 'jotted_layout',
                'label' => __('Layout', 'jotted'),
                'section' => 'jotted_settings',
                'type' => 'select',
                'options' => [
                    'default' => __('Default', 'jotted'),
                    'bin' => __('Bin', 'jotted'),
                    'pen' => __('Pen', 'jotted')
                ],
                'default' => 'default',
                'helper' => '',
                'supplemental' => ''
            ]
        ];
        foreach ( $fields as $field ) {
            add_settings_field( $field[ 'uid' ], $field[ 'label' ], '\Jotted\Jotted::field_callback', 'jotted_fields', $field[ 'section' ], $field );
            register_setting( 'jotted_fields', $field[ 'uid' ] );
        }
    }

    public static function field_callback( $args ) {
        $value = get_option( $args[ 'uid' ] ); // Get the current value, if there is one
        if ( !$value ) { // If no value exists
            $value = $args[ 'default' ]; // Set to our default
        }
        $style = isset( $args[ 'field_style' ] ) ? $args[ 'field_style' ] : '';
        // Check which type of field we want
        switch ( $args[ 'type' ] ) {
            case 'text': // If it is a text field
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" style="%5$s" />', $args[ 'uid' ], $args[ 'type' ], $args[ 'placeholder' ], $value, $style );
                break;
            case 'select': // If it is a select dropdown
                if ( !empty( $args[ 'options' ] ) && is_array( $args[ 'options' ] ) ) {
                    $options_markup = '';
                    foreach ( $args[ 'options' ] as $key => $label ) {
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value, $key, false ), $label );
                    }
                    printf( '<select name="%1$s" id="%1$s">%2$s</select>', $args[ 'uid' ], $options_markup );
                }
                break;
        }

        // If there is help text
        if ( $helper = $args[ 'helper' ] ) {
            printf( '<span class="helper"> %s</span>', $helper ); // Show it
        }

        // If there is supplemental text
        if ( $supplemental = $args[ 'supplemental' ] ) {
            printf( '<p class="description">%s</p>', $supplemental ); // Show it
        }
    }

    public static function settings_page() {
        ?>
        <div class="wrap">
            <h2><?php _e( 'Jotted Settings', 'jotted' ); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'jotted_fields' );
                do_settings_sections( 'jotted_fields' );
                submit_button();
                ?>
            </form>
        </div> <?php
    }
    
    /**
     * Shortcode handler for Jotted editors
     * @param type $atts
     */
    public static function shortcode($atts) {
        
        return 'this is the editor...'; 
    }

}
