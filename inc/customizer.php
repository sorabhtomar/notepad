<?php
/**
 * Notepad Theme Customizer support
 *
 * @package WordPress
 * @subpackage Notepad
 * @since Notepad 1.0
 */

/**
 * Add postMessage support for site title, description and 
 * reorganize other elements for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function notepad_customize_organizer($wp_customize) {
    $wp_customize->get_setting('blogname')->transport = 'postMessage';
    $wp_customize->get_setting('blogdescription')->transport = 'postMessage';

    // reorganize background settings in customizer
    $wp_customize->get_control( 'background_color'  )->section   = 'background_image';
    $wp_customize->get_section( 'background_image'  )->title     = __('Background Settings','smartshop');
    $wp_customize->get_section( 'background_image' )->description = __('Please note that background color and image settings work only for Boxed Layout','smartshop'); 
    
    
    // Rename the label to "Display Site Title & Tagline" in order to make this option extra clear.
    $wp_customize->get_control('display_header_text')->label = __('Display Site Title &amp; Tagline', 'notepad');
    
    // reorganize header settings in cusotmizer
    $wp_customize->get_control( 'header_textcolor'  )->section   = 'header_image';
    $wp_customize->get_control( 'display_header_text' )->section = 'header_image'; 
    $wp_customize->get_section( 'header_image'  )->title     = __('Header Settings','smartshop');
    
    $wp_customize->get_section( 'header_image'  )->priority     = 30;
    $wp_customize->get_section( 'background_image' )->priority  = 30; 
}

add_action('customize_register', 'notepad_customize_organizer', 12);


/**
 * Implement Theme Customizer additions and adjustments.
 *
 * @since Notepad 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function notepad_customize_register($wp_customize) {

    /** ===============
     * Extends CONTROLS class to add textarea
     */
    class notepad_customize_textarea_control extends WP_Customize_Control {

        public $type = 'textarea';

        public function render_content() {
            ?>

            <label>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                <textarea rows="5" style="width:98%;" <?php $this->link(); ?>><?php echo esc_textarea($this->value()); ?></textarea>
            </label>

            <?php
        }

    }

    // Add new section for theme layout and color schemes
    $wp_customize->add_section('notepad_theme_layout_settings', array(
        'title' => __('Color Scheme', 'notepad'),
        'priority' => 30,
    ));

      // Add setting for primary color
    $wp_customize->add_setting('notepad_theme_primary_color', array(
        'default' => '#EF7A7A', 
        'sanitize_callback' => 'notepad_sanitize_hex_color',
        'sanitize_js_callback' => 'notepad_sanitize_escaping',
    ));
    
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'notepad_theme_primary_color',
        array(
            'label' => 'Primary Color',
            'section' => 'notepad_theme_layout_settings',
            'settings' => 'notepad_theme_primary_color',
        )
    ));

    // Add setting for secondary color
    $wp_customize->add_setting('notepad_theme_secondary_color', array(
        'default' => '#FFF', 
        'sanitize_callback' => 'notepad_sanitize_hex_color',
        'sanitize_js_callback' => 'notepad_sanitize_escaping',
    ));
    
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'notepad_theme_secondary_color',
        array(
            'label' => 'Secondary Color',
            'section' => 'notepad_theme_layout_settings',
            'settings' => 'notepad_theme_secondary_color',
        )
    ));

    // Add footer text section
    $wp_customize->add_section('notepad_footer', array(
        'title' => 'Footer Text', // The title of section
        'priority' => 75,
    ));

    $wp_customize->add_setting('notepad_footer_footer_text', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'sanitize_js_callback' => 'notepad_sanitize_escaping',
    ));
    
    $wp_customize->add_control(new notepad_customize_textarea_control($wp_customize, 'notepad_footer_footer_text', array(
        'section' => 'notepad_footer', // id of section to which the setting belongs
        'settings' => 'notepad_footer_footer_text',
    )));
    
    // Add custom CSS section 
    $wp_customize->add_section(
        'notepad_custom_css_section', array(
        'title' => __('Custom CSS', 'smartshop'),
        'priority' => 80,
    ));

    $wp_customize->add_setting(
        'notepad_custom_css', array(
        'default' => '',
        'sanitize_callback' => 'notepad_sanitize_custom_css',
        'sanitize_js_callback' => 'notepad_sanitize_escaping',
    ));

    $wp_customize->add_control(
        new notepad_customize_textarea_control(
        $wp_customize, 'notepad_custom_css', array(
        'label' => __('Add your custom css here and design live! (for advanced users)', 'smartshop'),
        'section' => 'notepad_custom_css_section',
        'settings' => 'notepad_custom_css'
    )));
}

add_action('customize_register', 'notepad_customize_register');


/**
 * Bind JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @since Notepad 1.0
 */
function notepad_customize_preview_js() {
    wp_enqueue_script('notepad_customizer', get_template_directory_uri() . '/js/customizer.js', array('customize-preview'), '20131205', true);
}

add_action('customize_preview_init', 'notepad_customize_preview_js');

/* 
 * Sanitize Hex Color for 
 * Primary and Secondary Color options
 * 
 * @since Notepad 1.4
 */
function notepad_sanitize_hex_color( $color ) {
    if ( $unhashed = sanitize_hex_color_no_hash( $color ) ) {
        return '#' . $unhashed;
    }
    return $color;
}

/* 
 * Sanitize Custom CSS 
 * 
 * @since Notepad 1.4
 */

function notepad_sanitize_custom_css( $input) {
    $input = wp_kses_stripslashes( $input);
    return $input;
}	

/* 
 * Sanitize numeric values 
 * 
 * @since Notepad 1.4
 */
function notepad_sanitize_integer( $input ) {
    if( is_numeric( $input ) ) {
    return intval( $input );
    }
}

/*
 * Escaping for input values
 * 
 * @since Notepad 1.4
 */
function notepad_sanitize_escaping( $input) {
    $input = esc_attr( $input);
    return $input;
}


/*
 * Sanitize Checkbox input values
 * 
 * @since Notepad 1.2
 */
function notepad_sanitize_checkbox( $input ) {
    if ( $input ) {
            $output = '1';
    } else {
            $output = false;
    }
    return $output;
}

/*
 * Sanitize layout options 
 * 
 * @since Notepad 1.4
 */
function notepad_sanitize_layout_option($layout_option){
    if ( ! in_array( $layout_option, array( 'full-width','boxed' ) ) ) {
		$layout_option = 'boxed';
	}

	return $layout_option;
}

/**
 * Change theme colors based on theme options from customizer.
 *
 * @since Notepad 1.0
 */
function notepad_color_style() {
	$primary_color = get_theme_mod('notepad_theme_primary_color');
        $secondary_color = get_theme_mod('notepad_theme_secondary_color'); 

	// If no custom options for text are set, let's bail
	if ( $primary_color == '#ef7a7a' || $primary_color == '#EF7A7A' ) {
            return;
        }
	// If we get this far, we have custom styles.
	?>
	<style type="text/css" id="notepad-colorscheme-css">

                #footercontainer,
                .pagination .page-numbers:hover,
                li span.current,
                li a:hover.page-numbers,
                button:hover,
                input:hover[type="button"],
                input:hover[type="reset"],
                input:hover[type="submit"],
                .button:hover,
                .entry-content .button:hover,
                .main-navigation ul ul,
                .footer-wrap
                {
                    background: <?php echo $primary_color; ?> ;
                }

                ::selection,
                ::-webkit-selection,
                ::-moz-selection,
                .more-link:hover,
                .widget_search #searchsubmit
                {
                    background:<?php echo $primary_color; ?> ;
                    color:<?php echo $secondary_color; ?> ;
                }

                .site-title a,
                .sidebar a,
                .entry-header .entry-title a,
                .entry-header .entry-title,
                .main-navigation ul ul a:hover,
                .entry-header h1 a:visited {
                    color:<?php echo $primary_color; ?> ;
                }
                
                .main-navigation ul ul a,
                .more-link {
                    color:<?php echo $secondary_color; ?> ;
                }

	</style>
        <style type="text/css" id="notepad-custom-css">
            <?php echo trim( get_theme_mod( 'notepad_custom_css' ) ); ?>
        </style>
	<?php
}
add_action('wp_head','notepad_color_style');