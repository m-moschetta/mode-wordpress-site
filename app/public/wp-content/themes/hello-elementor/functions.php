<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Enqueue image quality fix styles
 */
function hello_elementor_image_quality_styles() {
	wp_enqueue_style(
		'hello-elementor-image-quality',
		get_template_directory_uri() . '/assets/css/image-quality-fix.css',
		array(),
		'1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_image_quality_styles' );

/**
 * Enqueue custom padding fix styles
 */
function hello_elementor_custom_padding_fix_styles() {
	wp_enqueue_style(
		'hello-elementor-padding-fix',
		get_template_directory_uri() . '/custom-padding-fix.css',
		array(),
		time() // Force cache refresh
	);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_custom_padding_fix_styles' );

/**
 * Enqueue accessibility improvements styles
 */
function hello_elementor_accessibility_styles() {
	wp_enqueue_style(
		'hello-elementor-accessibility',
		get_template_directory_uri() . '/accessibility-improvements.css',
		array(),
		'1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_accessibility_styles' );

/**
 * Disable comments completely
 */
function hello_elementor_disable_comments() {
	// Close comments on the front-end
	add_filter( 'comments_open', '__return_false', 20, 2 );
	add_filter( 'pings_open', '__return_false', 20, 2 );
	
	// Hide existing comments
	add_filter( 'comments_array', '__return_empty_array', 10, 2 );
	
	// Remove comments page in menu
	add_action( 'admin_menu', function() {
		remove_menu_page( 'edit-comments.php' );
	});
	
	// Remove comments links from admin bar
	add_action( 'init', function() {
		if ( is_admin_bar_showing() ) {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		}
	});
	
	// Remove comments metabox from dashboard
	add_action( 'admin_init', function() {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	});
	
	// Remove comments support from post types
	add_action( 'init', function() {
		remove_post_type_support( 'post', 'comments' );
		remove_post_type_support( 'page', 'comments' );
	}, 100 );
}
add_action( 'init', 'hello_elementor_disable_comments' );

define( 'HELLO_ELEMENTOR_VERSION', '3.4.4' );
define( 'EHP_THEME_SLUG', 'hello-elementor' );

define( 'HELLO_THEME_PATH', get_template_directory() );
define( 'HELLO_THEME_URL', get_template_directory_uri() );
define( 'HELLO_THEME_ASSETS_PATH', HELLO_THEME_PATH . '/assets/' );
define( 'HELLO_THEME_ASSETS_URL', HELLO_THEME_URL . '/assets/' );
define( 'HELLO_THEME_SCRIPTS_PATH', HELLO_THEME_ASSETS_PATH . 'js/' );
define( 'HELLO_THEME_SCRIPTS_URL', HELLO_THEME_ASSETS_URL . 'js/' );
define( 'HELLO_THEME_STYLE_PATH', HELLO_THEME_ASSETS_PATH . 'css/' );
define( 'HELLO_THEME_STYLE_URL', HELLO_THEME_ASSETS_URL . 'css/' );
define( 'HELLO_THEME_IMAGES_PATH', HELLO_THEME_ASSETS_PATH . 'images/' );
define( 'HELLO_THEME_IMAGES_URL', HELLO_THEME_ASSETS_URL . 'images/' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
					'navigation-widgets',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);
			add_theme_support( 'align-wide' );
			add_theme_support( 'responsive-embeds' );

			/*
			 * Editor Styles
			 */
			add_theme_support( 'editor-styles' );
			add_editor_style( 'editor-styles.css' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function hello_elementor_display_header_footer() {
		$hello_elementor_header_footer = true;

		return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				HELLO_THEME_STYLE_URL . 'reset.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				HELLO_THEME_STYLE_URL . 'theme.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( hello_elementor_display_header_footer() ) {
			wp_enqueue_style(
				'hello-elementor-header-footer',
				HELLO_THEME_STYLE_URL . 'header-footer.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	// Customizer controls
	function hello_elementor_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! hello_elementor_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}

require HELLO_THEME_PATH . '/theme.php';

HelloTheme\Theme::instance();

// ===== SISTEMA AUTOMATICO CATEGORIE E PAGINE =====

/**
 * Crea automaticamente una pagina per ogni categoria quando viene creata
 */
function auto_create_category_page($term_id, $tt_id, $taxonomy) {
    if ($taxonomy !== 'category') {
        return;
    }
    
    $term = get_term($term_id, 'category');
    if (is_wp_error($term)) {
        return;
    }
    
    // Controlla se esiste già una pagina per questa categoria
    $existing_page = get_posts(array(
        'post_type' => 'page',
        'meta_query' => array(
            array(
                'key' => '_category_page_id',
                'value' => $term_id,
                'compare' => '='
            )
        ),
        'post_status' => 'any',
        'numberposts' => 1
    ));
    
    if (!empty($existing_page)) {
        return; // Pagina già esistente
    }
    
    // Crea la nuova pagina con contenuto semplice
    $page_content = '<h1>Articoli della categoria: ' . esc_html($term->name) . '</h1>';
    if (!empty($term->description)) {
        $page_content .= '<p>' . esc_html($term->description) . '</p>';
    }
    
    // Usa JKit se disponibile, altrimenti usa query standard
    if (function_exists('jkit_post_block_shortcode') || shortcode_exists('jkit_post_block')) {
        $page_content .= '[jkit_post_block category="' . $term_id . '" number_post="9" column="3" post_type="post" enable_category="yes" enable_excerpt="yes"]';
    } else {
        // Fallback con query standard WordPress
        $page_content .= '<div class="category-posts-grid">';
        $page_content .= '[category_posts_query category_id="' . $term_id . '"]';
        $page_content .= '</div>';
    }
    
    $page_data = array(
        'post_title' => 'Categoria: ' . $term->name,
        'post_content' => $page_content,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 1
    );
    
    $page_id = wp_insert_post($page_data);
    
    if ($page_id && !is_wp_error($page_id)) {
        // Salva l'ID della categoria come meta della pagina
        update_post_meta($page_id, '_category_page_id', $term_id);
        
        // Non configurare Elementor per evitare conflitti
        // La pagina può essere modificata manualmente con Elementor se necessario
    }
}
add_action('created_category', 'auto_create_category_page', 10, 3);

/**
 * Elimina la pagina categoria quando viene eliminata la categoria
 */
function auto_delete_category_page($term_id, $tt_id, $taxonomy, $deleted_term) {
    if ($taxonomy !== 'category') {
        return;
    }
    
    $pages = get_posts(array(
        'post_type' => 'page',
        'meta_query' => array(
            array(
                'key' => '_category_page_id',
                'value' => $term_id,
                'compare' => '='
            )
        ),
        'post_status' => 'any',
        'numberposts' => -1
    ));
    
    foreach ($pages as $page) {
        wp_delete_post($page->ID, true);
    }
}
add_action('delete_category', 'auto_delete_category_page', 10, 4);

/**
 * Aggiorna il titolo della pagina quando viene modificata la categoria
 */
function auto_update_category_page($term_id, $tt_id, $taxonomy) {
    if ($taxonomy !== 'category') {
        return;
    }
    
    $term = get_term($term_id, 'category');
    if (is_wp_error($term)) {
        return;
    }
    
    $pages = get_posts(array(
        'post_type' => 'page',
        'meta_query' => array(
            array(
                'key' => '_category_page_id',
                'value' => $term_id,
                'compare' => '='
            )
        ),
        'post_status' => 'any',
        'numberposts' => -1
    ));
    
    foreach ($pages as $page) {
        wp_update_post(array(
            'ID' => $page->ID,
            'post_title' => 'Categoria: ' . $term->name
        ));
    }
}
add_action('edited_category', 'auto_update_category_page', 10, 3);

/**
 * Funzione helper per ottenere l'URL della pagina categoria
 */
function get_category_page_url($category_id) {
    $pages = get_posts(array(
        'post_type' => 'page',
        'meta_query' => array(
            array(
                'key' => '_category_page_id',
                'value' => $category_id,
                'compare' => '='
            )
        ),
        'post_status' => 'publish',
        'numberposts' => 1
    ));
    
    if (!empty($pages)) {
        return get_permalink($pages[0]->ID);
    }
    
    return get_category_link($category_id); // Fallback all'URL categoria standard
}

/**
 * Shortcode per visualizzare le card delle categorie
 */
function category_cards_shortcode($atts) {
    $atts = shortcode_atts(array(
        'exclude' => '', // ID categorie da escludere
        'include' => '', // Solo queste categorie
        'columns' => 3,
        'show_count' => 'yes'
    ), $atts);
    
    $args = array(
        'taxonomy' => 'category',
        'hide_empty' => true,
        'parent' => 0 // Solo categorie principali
    );
    
    if (!empty($atts['exclude'])) {
        $args['exclude'] = explode(',', $atts['exclude']);
    }
    
    if (!empty($atts['include'])) {
        $args['include'] = explode(',', $atts['include']);
    }
    
    $categories = get_terms($args);
    
    if (empty($categories) || is_wp_error($categories)) {
        return '<p>Nessuna categoria trovata.</p>';
    }
    
    $output = '<div class="category-cards-grid" style="display: grid; grid-template-columns: repeat(' . intval($atts['columns']) . ', 1fr); gap: 20px; margin: 20px 0;">';
    
    foreach ($categories as $category) {
        $category_url = get_category_page_url($category->term_id);
        $post_count = $atts['show_count'] === 'yes' ? ' (' . $category->count . ')' : '';
        
        $output .= '<div class="category-card" style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center; transition: transform 0.3s ease;">';
        $output .= '<h3 style="margin: 0 0 10px 0;"><a href="' . esc_url($category_url) . '" style="text-decoration: none; color: inherit;">' . esc_html($category->name) . $post_count . '</a></h3>';
        
        if (!empty($category->description)) {
            $output .= '<p style="margin: 0 0 15px 0; color: #666;">' . esc_html($category->description) . '</p>';
        }
        
        $output .= '<a href="' . esc_url($category_url) . '" class="category-link" style="display: inline-block; padding: 8px 16px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px;">Vedi Articoli</a>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    // Aggiungi CSS per hover effect
    $output .= '<style>
.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>';
    
    return $output;
}
add_shortcode('category_cards', 'category_cards_shortcode');

/**
 * Shortcode di fallback per visualizzare i post di una categoria
 */
function category_posts_query_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category_id' => '',
        'posts_per_page' => 9,
        'columns' => 3
    ), $atts);
    
    if (empty($atts['category_id'])) {
        return '<p>ID categoria non specificato.</p>';
    }
    
    $query = new WP_Query(array(
        'post_type' => 'post',
        'posts_per_page' => intval($atts['posts_per_page']),
        'cat' => intval($atts['category_id']),
        'post_status' => 'publish'
    ));
    
    if (!$query->have_posts()) {
        return '<p>Nessun articolo trovato in questa categoria.</p>';
    }
    
    $output = '<div class="category-posts-grid" style="display: grid; grid-template-columns: repeat(' . intval($atts['columns']) . ', 1fr); gap: 20px; margin: 20px 0;">';
    
    while ($query->have_posts()) {
        $query->the_post();
        $output .= '<article class="post-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">';
        
        if (has_post_thumbnail()) {
            $output .= '<div class="post-thumbnail">' . get_the_post_thumbnail(get_the_ID(), 'medium') . '</div>';
        }
        
        $output .= '<div class="post-content" style="padding: 15px;">';
        $output .= '<h3 style="margin: 0 0 10px 0;"><a href="' . get_permalink() . '" style="text-decoration: none; color: inherit;">' . get_the_title() . '</a></h3>';
        $output .= '<div class="post-meta" style="font-size: 0.9em; color: #666; margin-bottom: 10px;">' . get_the_date() . '</div>';
        $output .= '<div class="post-excerpt">' . get_the_excerpt() . '</div>';
        $output .= '<a href="' . get_permalink() . '" class="read-more" style="display: inline-block; margin-top: 10px; color: #0073aa;">Leggi di più</a>';
        $output .= '</div>';
        $output .= '</article>';
    }
    
    $output .= '</div>';
    
    wp_reset_postdata();
    
    return $output;
}
add_shortcode('category_posts_query', 'category_posts_query_shortcode');

/**
 * Funzione per pulire i metadati Elementor corrotti
 */
function clean_elementor_meta() {
    global $wpdb;
    
    // Trova tutti i post con metadati Elementor corrotti
    $corrupted_meta = $wpdb->get_results(
        "SELECT post_id, meta_key FROM {$wpdb->postmeta} 
         WHERE meta_key IN ('_elementor_data', '_elementor_page_settings') 
         AND meta_value = '[]' OR meta_value = '{}'"
    );
    
    foreach ($corrupted_meta as $meta) {
        delete_post_meta($meta->post_id, $meta->meta_key);
    }
    
    return count($corrupted_meta);
}

/**
 * Crea pagine per categorie esistenti (da eseguire una volta)
 */
function create_pages_for_existing_categories() {
    $categories = get_terms(array(
        'taxonomy' => 'category',
        'hide_empty' => false
    ));
    
    $created = 0;
    foreach ($categories as $category) {
        // Verifica se la pagina esiste già
        $existing_page = get_posts(array(
            'post_type' => 'page',
            'meta_query' => array(
                array(
                    'key' => '_category_page_id',
                    'value' => $category->term_id,
                    'compare' => '='
                )
            ),
            'post_status' => 'any',
            'numberposts' => 1
        ));
        
        if (empty($existing_page)) {
            auto_create_category_page($category->term_id, $category->term_taxonomy_id, 'category');
            $created++;
        }
    }
    
    return $created;
}

/**
 * Aggiorna le pagine categoria esistenti con contenuto migliorato
 */
function update_existing_category_pages() {
    $category_pages = get_posts(array(
        'post_type' => 'page',
        'meta_query' => array(
            array(
                'key' => '_category_page_id',
                'compare' => 'EXISTS'
            )
        ),
        'post_status' => 'any',
        'numberposts' => -1
    ));
    
    $updated_count = 0;
    
    foreach ($category_pages as $page) {
        $category_id = get_post_meta($page->ID, '_category_page_id', true);
        $category = get_category($category_id);
        
        if ($category && !is_wp_error($category)) {
            // Crea contenuto migliorato
            $new_content = '<div class="category-page-header">';
            $new_content .= '<h1 class="category-title">Articoli della categoria: ' . esc_html($category->name) . '</h1>';
            
            if (!empty($category->description)) {
                $new_content .= '<div class="category-description">';
                $new_content .= '<p>' . esc_html($category->description) . '</p>';
                $new_content .= '</div>';
            }
            $new_content .= '</div>';
            
            // Aggiungi sezione articoli
            $new_content .= '<div class="category-posts-section">';
            
            // Usa JKit se disponibile, altrimenti fallback
            if (function_exists('jkit_post_block_shortcode') || shortcode_exists('jkit_post_block')) {
                $new_content .= '[jkit_post_block category="' . $category_id . '" number_post="12" column="3" post_type="post" enable_category="yes" enable_excerpt="yes" enable_meta="yes" enable_readmore="yes"]';
            } else {
                $new_content .= '[category_posts_query category_id="' . $category_id . '" posts_per_page="12" columns="3"]';
            }
            
            $new_content .= '</div>';
            
            // Aggiungi CSS personalizzato inline
            $new_content .= '<style>';
            $new_content .= '.category-page-header { margin-bottom: 30px; text-align: center; }';
            $new_content .= '.category-title { font-size: 2.5em; margin-bottom: 15px; color: #333; }';
            $new_content .= '.category-description { font-size: 1.2em; color: #666; max-width: 800px; margin: 0 auto; }';
            $new_content .= '.category-posts-section { margin-top: 40px; }';
            $new_content .= '@media (max-width: 768px) {';
            $new_content .= '  .category-title { font-size: 2em; }';
            $new_content .= '  .category-description { font-size: 1.1em; padding: 0 20px; }';
            $new_content .= '}';
            $new_content .= '</style>';
            
            // Aggiorna la pagina
            $updated_page = array(
                'ID' => $page->ID,
                'post_title' => 'Categoria: ' . $category->name,
                'post_content' => $new_content,
                'post_status' => 'publish'
            );
            
            $result = wp_update_post($updated_page);
            
            if ($result && !is_wp_error($result)) {
                $updated_count++;
            }
        }
    }
    
    return $updated_count;
}

// Aggiungi azione admin per creare pagine per categorie esistenti
function add_category_pages_admin_action() {
    if (isset($_GET['create_category_pages']) && current_user_can('manage_options')) {
        create_pages_for_existing_categories();
        wp_redirect(admin_url('edit.php?post_type=page&category_pages_created=1'));
        exit;
    }
    
    if (isset($_GET['category_pages_created'])) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Pagine categoria create con successo!</p></div>';
        });
    }
}
add_action('admin_init', 'add_category_pages_admin_action');

// Aggiungi link nel menu admin
function add_category_pages_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=page',
        'Gestione Categorie',
        'Gestione Categorie',
        'manage_options',
        'category-pages-manager',
        'category_pages_admin_page'
    );
}
add_action('admin_menu', 'add_category_pages_admin_menu');

function category_pages_admin_page() {
    if (isset($_POST['create_category_pages'])) {
        $created = create_pages_for_existing_categories();
        echo '<div class="notice notice-success"><p>Sono state create ' . $created . ' pagine per le categorie esistenti.</p></div>';
    }
    
    if (isset($_POST['clean_elementor_meta'])) {
        $cleaned = clean_elementor_meta();
        echo '<div class="notice notice-success"><p>Sono stati puliti ' . $cleaned . ' metadati Elementor corrotti.</p></div>';
    }
    
    if (isset($_POST['update_existing_pages'])) {
        $updated = update_existing_category_pages();
        echo '<div class="notice notice-success"><p>Sono state aggiornate ' . $updated . ' pagine categoria esistenti.</p></div>';
    }
    
    echo '<div class="wrap">';
    echo '<h1>Gestione Pagine Categoria</h1>';
    echo '<p>Questo strumento ti permette di gestire automaticamente le pagine per le categorie.</p>';
    echo '<h2>Azioni Disponibili</h2>';
    echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
    echo '<input type="submit" name="create_category_pages" value="Crea Pagine per Categorie Esistenti" class="button button-primary" />';
    echo '</form>';
    echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
    echo '<input type="submit" name="update_existing_pages" value="Aggiorna Pagine Categoria Esistenti" class="button button-primary" />';
    echo '</form>';
    echo '<form method="post" style="display: inline-block;">';
    echo '<input type="submit" name="clean_elementor_meta" value="Pulisci Metadati Elementor Corrotti" class="button button-secondary" />';
    echo '</form>';
    echo '<p><em>Nota: Le pagine per nuove categorie vengono create automaticamente.</em></p>';
    
    // Mostra tabella delle categorie e pagine
    echo '<h2>Categorie e Pagine Associate</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Categoria</th><th>Slug</th><th>Pagina Associata</th><th>Azioni</th></tr></thead>';
    echo '<tbody>';
    
    $categories = get_categories(array('hide_empty' => false));
    foreach ($categories as $category) {
        $page_url = get_category_page_url($category->term_id);
        echo '<tr>';
        echo '<td>' . esc_html($category->name) . '</td>';
        echo '<td>' . esc_html($category->slug) . '</td>';
        if ($page_url) {
            echo '<td><a href="' . esc_url($page_url) . '" target="_blank">Visualizza Pagina</a></td>';
            echo '<td><a href="' . esc_url($page_url) . '?elementor" target="_blank" class="button button-small">Modifica con Elementor</a></td>';
        } else {
            echo '<td><em>Nessuna pagina</em></td>';
            echo '<td>-</td>';
        }
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
