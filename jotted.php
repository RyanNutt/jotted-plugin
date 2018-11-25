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

        add_shortcode( 'jotted', '\Jotted\Jotted::shortcode' );

        add_action( 'wp_enqueue_scripts', '\Jotted\Jotted::enqueue_scripts' );
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
                'label' => __( 'Layout', 'jotted' ),
                'section' => 'jotted_settings',
                'type' => 'select',
                'options' => [
                    'default' => __( 'Default', 'jotted' ),
                    'bin' => __( 'Bin', 'jotted' ),
                    'pen' => __( 'Pen', 'jotted' )
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
    public static function shortcode( $atts ) {
        global $post; 
        $atts = shortcode_atts( [
            'css' => false,
            'js' => false,
            'html' => false,
            'id' => false,
            'layout' => get_option( 'jotted_layout' ),
            'style' => get_option( 'jotted_style' )
                ], $atts, 'jotted' );

        // Need to clean up a few fields
        $atts[ 'id' ] = $atts[ 'id' ] === false ? uniqid( 'jotted' ) : $atts[ 'id' ];
        $atts[ 'layout' ] = $atts[ 'layout' ] === false ? 'ace' : $atts[ 'layout' ];
        $atts[ 'style' ] = $atts[ 'style' ] === false ? 'width:100%;height:400px;border:1px solid silver;' : $atts[ 'style' ];

        // Need attached media for later
        $media = get_children([
            'post_parent' => $post->ID,
            'post_type' => 'attachment',
            'numberposts' => -1,
            'post_status' => 'any'
        ]);
        
        $out = sprintf( '<div id="%1$s" style="%2$s" data-jotted></div>', esc_attr( $atts[ 'id' ] ), esc_attr( $atts[ 'style' ] ) );

        $plugins = [];
        if ( $atts[ 'layout' ] == 'pen' ) {
            $plugins[] = 'pen';
        }

        $highlighter = get_option( 'jotted_highlight' );
        if ( $highlighter === false || $highlighter == 'ace' ) {
            $plugins[] = 'ace';
        }
        else if ( $highlighter == 'codemirror' ) {
            $plugins[] = 'codemirror';
        }
        
        // Build the files array
        $files = [];
        foreach (['html', 'css', 'js'] as $type) {
            if ($atts[$type] !== false) {
                $attachment = self::find_file($atts[$type], $media);
                if (self::is_base64( $atts[$type])) {
                    $files[] = [
                        'type' => $type,
                        'content' => base64_decode($atts[$type])
                    ];
                }
                else if ($attachment !== false) {
                    $file_contents = file_get_contents(get_attached_file($attachment->ID, true));
                    $files[] = [
                        'type' => $type,
                        'content' => $file_contents
                    ];
                }
                else {
                    // Whatever is there, stripped of escapes because it
                    // was probably entered in the WYSIWYG editor
                    $files[] = [
                        'type' => $type,
                        'content' => html_entity_decode($atts[$type])
                    ];
                }
            }
        }

        $out .= "\n<script>
  new Jotted(document.querySelector('#{$atts[ 'id' ]}'), {
      runScripts: false,
    files: " . json_encode($files) . ",
    plugins: " . json_encode( $plugins ) . "
  });
</script>";

        return $out;
    }

    /**
     * Enqueue scripts and styles if the shortcode is present.
     * 
     * Loads the jotted JS and CSS files and also the Ace Editor or 
     * CodeMirror files depending on settings. 
     * 
     * @global type $post
     */
    public static function enqueue_scripts() {
        global $post;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'jotted' ) ) {
            // Jotted JS and CSS; we always need these
            wp_enqueue_script( 'jotted', plugins_url( 'ext/jotted.min.js', __FILE__ ), [], null );
            wp_enqueue_style( 'jotted', plugins_url( 'ext/jotted.min.css', __FILE__ ), false, null );

            // Check for the JS/CSS for highlighters
            $highlighter = get_option( 'jotted_highlight' );
            if ( $highlighter === false ) {
                // Ace editor is default
                $highlighter = 'ace';
            }

            if ( $highlighter === 'ace' ) {
                $js = apply_filters( 'jotted_javascript', [
                    'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.1/ace.js'
                        ] );
                $css = apply_filters( 'jotted_css', [] );
            }
            else if ( $highlighter === 'codemirror' ) {
                $js = apply_filters( 'jotted_javascript', [
                    'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/codemirror.min.js',
                    'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/mode/javascript/javascript.min.js',
                    'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/mode/css/css.min.js',
                    'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/mode/xml/xml.min.js',
                    'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/mode/htmlmixed/htmlmixed.min.js'
                        ] );
                $css = apply_filters( 'jotted_css', [
                    'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/codemirror.min.css'
                        ] );
            }
            else {
                $js = apply_filters( 'jotted_javascript', [] );
                $css = apply_filters( 'jotted_css', [] );
            }

            if ( !empty( $js ) ) {
                foreach ( $js as $script ) {
                    wp_enqueue_script( md5( $script ), $script, [], null );
                }
            }
            if ( !empty( $css ) ) {
                foreach ( $css as $sheet ) {
                    wp_enqueue_style( md5( $sheet ), $sheet, false, null );
                }
            }
        }
    }

    /** @see https://stackoverflow.com/a/34982057/1561431 */
    private static function is_base64( $data ) {
        if ( preg_match( '%^[a-zA-Z0-9/+]*={0,2}$%', $data ) ) {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * Check if a file is available in a list of WordPress attachments
     * @param type $filename
     * @param type $media_list
     * @return boolean
     */
    private static function find_file($filename, $media_list) {
        if (empty($media_list)) {
            return false; 
        }
        $filename = strtolower($filename);
        
        foreach ($media_list as $media) {
            if (strtolower(basename($media->guid)) == $filename) {
                return $media; 
            }
        }
        
        return false; 
    }

}
