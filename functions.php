<?php

if ( ! class_exists( 'Timber' ) ) {
	add_action( 'admin_notices', function() {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
		} );
	return;
}

Timber::$dirname = array('templates', 'views');

class FalconeSite extends TimberSite {

	function __construct() {
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
        add_filter( 'post_gallery', array($this, 'modify_post_gallery'), 10, 3);
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
        add_action( 'init', array($this, 'give_editor_privileges'));
        add_action( 'widgets_init', 'falcone_widgets_init');
		parent::__construct();
	}

    function give_editor_privileges() {
        // get the the role object
        $editor = get_role('editor');
        // add $cap capability to this role object
        $editor->add_cap('edit_theme_options');
        $editor->add_cap('edit_theme');
    }

    function modify_post_gallery($output, $attr, $instance) {
        global $post;

        if (isset($attr['orderby'])) {
            $attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
            if (!$attr['orderby'])
                unset($attr['orderby']);
        }

        extract(shortcode_atts(array(
            'order' => 'ASC',
            'orderby' => 'menu_order ID',
            'id' => $post->ID,
            'itemtag' => 'dl',
            'icontag' => 'dt',
            'captiontag' => 'dd',
            'columns' => 3,
            'size' => 'thumbnail',
            'include' => '',
            'exclude' => ''
        ), $attr));

        $id = intval($id);
        if ('RAND' == $order) $orderby = 'none';

        if (!empty($include)) {
            $include = preg_replace('/[^0-9,]+/', '', $include);
            $_attachments = get_posts(array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));

            $attachments = array();
            foreach ($_attachments as $key => $val) {
                $attachments[$val->ID] = $_attachments[$key];
            }
        }

        if (empty($attachments)) return '';

        // Here's your actual output, you may customize it to your need
        $output = "<ul class=\"clearing-thumbs small-block-grid-2 medium-block-grid-4\" data-clearing>\n";

        // Now you loop through each attachment
        foreach ($attachments as $id => $attachment) {
            // Fetch all data related to attachment
            $img = wp_prepare_attachment_for_js($id);
            $img_thumb = wp_get_attachment_image_src($id);
            $img_medium = wp_get_attachment_image_src($id, 'medium');
            $img_full = wp_get_attachment_image_src($id, 'full');

            $alt = $img['alt'];

            // Store the caption
            $caption = $img['caption'];

            $output .= "<li>\n";

            $output .= "<a class=\"th\" href=\"{$img_full[0]}\">
                <img data-caption=\"{$caption}\" src=\"{$img_medium[0]}\" class=\"attachment-thumbnail\" alt=\"{$alt}\">
            </a>";

            $output .= "</li>\n";
        }

        $output .= "</ul>\n";

        return $output;
    }

	function register_post_types() {
        register_post_type( 'results', array(
            'labels' => array(
                'name' => 'Results',
                'singular_name' => 'Result',
            ),
            'taxonomies' => array('debt'),
            'description' => 'Results and testimonials from satisfied clients.',
            'public' => true,
            'has_archive' => true,
            'menu_position' => 20,
            'supports' => array( 'title', 'editor', 'custom-fields' )
        ));

        register_post_type( 'videos', array(
            'labels' => array(
                'name' => 'Video Gallery',
                'singular_name' => 'Video Gallery',
            ),
            'description' => 'Videos from Damian Falcone & Co.',
            'public' => true,
            'has_archive' => true,
            'menu_position' => 20,
            'supports' => array( 'title', 'editor', 'custom-fields' )
        ));
	}

	function register_taxonomies() {
        $debt_taxonomy_labels = array(
            'name'              => _x( 'Debt Types', 'taxonomy general name' ),
            'singular_name'     => _x( 'Debt Type', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Debt Types' ),
            'all_items'         => __( 'All Debt Types' ),
            'parent_item'       => __( 'Parent Debt Type' ),
            'parent_item_colon' => __( 'Parent Debt Type:' ),
            'edit_item'         => __( 'Edit Debt Type' ),
            'update_item'       => __( 'Update Debt Type' ),
            'add_new_item'      => __( 'Add New Debt Type' ),
            'new_item_name'     => __( 'New Debt Type Name' ),
            'menu_name'         => __( 'Debt Type' )
        );

        $debt_taxonomy_args = array(
            'hierarchical'      => true,
            'labels'            => $debt_taxonomy_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'debt' )
        );

        register_taxonomy('debt', 'results', $debt_taxonomy_args);
	}

	function add_to_context( $context ) {
        //$context['foo'] = 'bar';
        //$context['stuff'] = 'I am a value set in your functions.php file';
        //$context['notes'] = 'These values are available everytime you call Timber::get_context();';
		$context['menu'] = new TimberMenu(14);
        $context['accreditation_menu'] = new TimberMenu(3);
        $context['licenses_menu'] = new TimberMenu(2);
        $context['debt_types_menu'] = new TimberMenu(13);
        $context['footer_menu_1'] = new TimberMenu(15);
        $context['footer_menu_2'] = new TimberMenu(16);
        $context['footer_menu_3'] = new TimberMenu(17);
        $context['final_menu'] = new TimberMenu(18);
		$context['site'] = $this;
        $context['dynamic_sidebar'] = Timber::get_widgets('page_sidebar');
        $context['falcone_options'] = array(
            'falcone_contact_email' => get_option('falcone_contact_email'),
            'falcone_contact_phone' => get_option('falcone_contact_phone'),
            'falcone_contact_facebook' => get_option('falcone_contact_facebook'),
            'falcone_contact_twitter' => get_option('falcone_contact_twitter'),
            'falcone_contact_youtube' => get_option('falcone_contact_youtube'),
            'falcone_contact_linkedin' => get_option('falcone_contact_linkedin'),
            'falcone_contact_hiring' => get_option('falcone_contact_hiring')
        );

		return $context;
	}

	function add_to_twig( $twig ) {
		/* this is where you can add your own fuctions to twig */
        /** @var \Twig_Environment $twig */
        // FOR DEBUG PURPOSES ONLY
//        $twig->enableDebug();
//        $twig->addExtension(new Twig_Extension_Debug());
        $twig->addExtension( new Twig_Extension_StringLoader() );
        $twig->addFilter( 'myfoo', new Twig_Filter_Function( 'myfoo' ) );
        return $twig;
	}

}

$falconeSite = new FalconeSite();

function falcone_widgets_init() {
    register_sidebar( array(
        'name'          => 'Page sidebar',
        'id'            => 'page_sidebar',
        'before_widget' => '<div>',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="text-center">',
        'after_title'   => '</h2>',
    ) );
}

function theme_settings_page()
{
	?>
	<div class="wrap">
		<h1>Theme Panel</h1>
		<form method="post" action="options.php">
			<?php
			settings_fields("falcone_contact");
			do_settings_sections("theme-options");
			submit_button();
			?>
		</form>
	</div>
	<?php
}

function add_theme_menu_item()
{
	add_menu_page("Theme Panel", "Theme Panel", "edit_theme_options", "theme-panel", "theme_settings_page", null, 99);
}

add_action("admin_menu", "add_theme_menu_item");

function falcone_display_email_element()
{
	?>
	<input type="text" name="falcone_contact_email" id="falcone_contact_email" value="<?php echo get_option('falcone_contact_email'); ?>" />
	<?php
}

function falcone_display_phone_element()
{
    ?>
    <input type="text" name="falcone_contact_phone" id="falcone_contact_phone" value="<?php echo get_option('falcone_contact_phone'); ?>" />
    <?php
}

function falcone_display_facebook_element()
{
	?>
	<input type="text" name="falcone_contact_facebook" id="falcone_contact_facebook" value="<?php echo get_option('falcone_contact_facebook'); ?>" />
	<?php
}

function falcone_display_twitter_element()
{
    ?>
    <input type="text" name="falcone_contact_twitter" id="falcone_contact_twitter" value="<?php echo get_option('falcone_contact_twitter'); ?>" />
    <?php
}

function falcone_display_youtube_element()
{
    ?>
    <input type="text" name="falcone_contact_youtube" id="falcone_contact_youtube" value="<?php echo get_option('falcone_contact_youtube'); ?>" />
    <?php
}

function falcone_display_linkedin_element()
{
    ?>
    <input type="text" name="falcone_contact_linkedin" id="falcone_contact_linkedin" value="<?php echo get_option('falcone_contact_linkedin'); ?>" />
    <?php
}

function falcone_display_hiring_element()
{
    ?>
    <input type="text" name="falcone_contact_hiring" id="falcone_contact_hiring" value="<?php echo get_option('falcone_contact_hiring'); ?>" />
    <?php
}

function falcone_display_emailform_element()
{
    ?>
    <input type="text" name="falcone_contact_emailform" id="falcone_contact_emailform" value="<?php echo get_option('falcone_contact_emailform'); ?>" />
    <?php
}

function display_theme_panel_fields()
{
	add_settings_section("falcone_contact", "All Settings", null, "theme-options");

	add_settings_field("falcone_contact_email", "Contact Email Address", "falcone_display_email_element", "theme-options", "falcone_contact");
	add_settings_field("falcone_contact_phone", "Contact Phone Number", "falcone_display_phone_element", "theme-options", "falcone_contact");
    add_settings_field("falcone_contact_facebook", "Facebook URL", "falcone_display_facebook_element", "theme-options", "falcone_contact");
    add_settings_field("falcone_contact_twitter", "Twitter URL", "falcone_display_twitter_element", "theme-options", "falcone_contact");
    add_settings_field("falcone_contact_youtube", "YouTube URL", "falcone_display_youtube_element", "theme-options", "falcone_contact");
    add_settings_field("falcone_contact_linkedin", "LinkedIn URL", "falcone_display_linkedin_element", "theme-options", "falcone_contact");
    add_settings_field("falcone_contact_hiring", "Now Hiring URL<br /><small>Icon only displays when there is a URL in this field</small>", "falcone_display_hiring_element", "theme-options", "falcone_contact");
    add_settings_field("falcone_contact_emailform", "Email Address for Form Submissions<br /><small>Form submissions will be emailed to this address or the site administrators address</small>", "falcone_display_emailform_element", "theme-options", "falcone_contact");

	register_setting("falcone_contact", "falcone_contact_email");
	register_setting("falcone_contact", "falcone_contact_phone");
    register_setting("falcone_contact", "falcone_contact_facebook");
    register_setting("falcone_contact", "falcone_contact_twitter");
    register_setting("falcone_contact", "falcone_contact_youtube");
    register_setting("falcone_contact", "falcone_contact_linkedin");
    register_setting("falcone_contact", "falcone_contact_hiring");
    register_setting("falcone_contact", "falcone_contact_emailform");
}

add_action("admin_init", "display_theme_panel_fields");
