<?php
/**
 * Semantic Landmarks Accessibility Fixes for Hello Elementor Theme
 * 
 * This file addresses accessibility issues where interactive elements
 * are not properly enclosed in semantic landmarks as required by WCAG 2.1.
 * 
 * Issues addressed:
 * - Interactive elements not in proper landmark regions
 * - Missing semantic structure for navigation, main content, etc.
 * - Proper landmark roles and labels
 * 
 * @package HelloElementor
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Hello_Elementor_Semantic_Landmarks_Fix {
    
    public function __construct() {
        add_action( 'wp_head', array( $this, 'add_landmark_styles' ) );
        add_filter( 'wp_nav_menu_args', array( $this, 'add_nav_landmark_attributes' ) );
        add_filter( 'the_content', array( $this, 'wrap_content_in_main_landmark' ), 5 );
        add_action( 'wp_footer', array( $this, 'add_landmark_javascript' ) );
        add_filter( 'comment_form_defaults', array( $this, 'add_comment_form_landmark' ) );
        add_filter( 'get_search_form', array( $this, 'wrap_search_form_in_landmark' ) );
    }
    
    /**
     * Add CSS for landmark accessibility
     */
    public function add_landmark_styles() {
        echo '<style id="hello-landmarks-accessibility">';
        echo '/* Semantic Landmarks Accessibility Fixes */';
        echo '.hello-landmark-main { display: block; }';
        echo '.hello-landmark-nav { display: block; }';
        echo '.hello-landmark-search { display: block; }';
        echo '.hello-landmark-complementary { display: block; }';
        echo '.hello-landmark-contentinfo { display: block; }';
        echo '</style>';
    }
    
    /**
     * Add proper landmark attributes to navigation menus
     */
    public function add_nav_landmark_attributes( $args ) {
        // Ensure navigation has proper landmark role and aria-label
        if ( isset( $args['theme_location'] ) ) {
            switch ( $args['theme_location'] ) {
                case 'menu-1':
                case 'header':
                    $args['container'] = 'nav';
                    $args['container_class'] = 'hello-landmark-nav primary-navigation';
                    $args['container_aria_label'] = __( 'Primary Navigation', 'hello-elementor' );
                    break;
                case 'menu-2':
                case 'footer':
                    $args['container'] = 'nav';
                    $args['container_class'] = 'hello-landmark-nav footer-navigation';
                    $args['container_aria_label'] = __( 'Footer Navigation', 'hello-elementor' );
                    break;
                default:
                    $args['container'] = 'nav';
                    $args['container_class'] = 'hello-landmark-nav';
                    $args['container_aria_label'] = __( 'Navigation Menu', 'hello-elementor' );
                    break;
            }
        }
        
        return $args;
    }
    
    /**
     * Wrap main content in proper landmark
     */
    public function wrap_content_in_main_landmark( $content ) {
        // Only wrap if we're in the main query and not in admin
        if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }
        
        // Check if content is already wrapped in main
        if ( strpos( $content, '<main' ) !== false ) {
            return $content;
        }
        
        // Wrap content in main landmark
        $wrapped_content = '<main class="hello-landmark-main" role="main" aria-label="' . esc_attr__( 'Main Content', 'hello-elementor' ) . '">';
        $wrapped_content .= $content;
        $wrapped_content .= '</main>';
        
        return $wrapped_content;
    }
    
    /**
     * Add JavaScript for dynamic landmark fixes
     */
    public function add_landmark_javascript() {
        ?>
        <script id="hello-landmarks-js">
        (function() {
            'use strict';
            
            // Counter for unique region labels
            var regionCounter = 1;
            var usedLabels = new Set();
            
            // Function to generate unique, descriptive labels for regions
            function generateUniqueRegionLabel(element) {
                var label = '';
                
                // Try to get label from element content or attributes
                if (element.textContent && element.textContent.trim()) {
                    var text = element.textContent.trim();
                    // Use first few words as label
                    var words = text.split(/\s+/).slice(0, 3).join(' ');
                    if (words.length > 50) {
                        words = words.substring(0, 47) + '...';
                    }
                    label = words;
                } else if (element.getAttribute('title')) {
                    label = element.getAttribute('title');
                } else if (element.getAttribute('alt')) {
                    label = element.getAttribute('alt');
                } else if (element.className) {
                    // Generate label from class names
                    var className = element.className.split(' ')[0];
                    label = className.replace(/[-_]/g, ' ').replace(/([a-z])([A-Z])/g, '$1 $2');
                    label = label.charAt(0).toUpperCase() + label.slice(1).toLowerCase();
                }
                
                // Fallback to element type
                if (!label) {
                    var tagName = element.tagName.toLowerCase();
                    if (tagName === 'button') {
                        label = 'Button';
                    } else if (tagName === 'input') {
                        var type = element.getAttribute('type') || 'text';
                        label = type.charAt(0).toUpperCase() + type.slice(1) + ' Input';
                    } else if (tagName === 'a') {
                        label = 'Link';
                    } else if (tagName === 'form') {
                        label = 'Form';
                    } else {
                        label = 'Interactive Content';
                    }
                }
                
                // Ensure uniqueness
                var originalLabel = label;
                var counter = 1;
                while (usedLabels.has(label)) {
                    label = originalLabel + ' ' + (counter + 1);
                    counter++;
                }
                
                usedLabels.add(label);
                return label;
            }
            
            // Function to add landmarks to elements missing them
            function addMissingLandmarks() {
                // Find interactive elements and content elements not in landmarks
                var interactiveSelectors = [
                    'button:not([role="presentation"]):not([aria-hidden="true"])',
                    'input[type="button"]:not([aria-hidden="true"])',
                    'input[type="submit"]:not([aria-hidden="true"])',
                    'input[type="reset"]:not([aria-hidden="true"])',
                    'a[href]:not([aria-hidden="true"])',
                    'input[type="text"]:not([aria-hidden="true"])',
                    'input[type="email"]:not([aria-hidden="true"])',
                    'input[type="search"]:not([aria-hidden="true"])',
                    'textarea:not([aria-hidden="true"])',
                    'select:not([aria-hidden="true"])',
                    // Add specific problematic elements
                    'h1.elementor-heading-title:not([aria-hidden="true"])',
                    'h2.elementor-heading-title:not([aria-hidden="true"])',
                    'h3.elementor-heading-title:not([aria-hidden="true"])',
                    'h4.elementor-heading-title:not([aria-hidden="true"])',
                    'h5.elementor-heading-title:not([aria-hidden="true"])',
                    'h6.elementor-heading-title:not([aria-hidden="true"])',
                    'h3.jkit-post-title:not([aria-hidden="true"])',
                    'ul.slides:not([aria-hidden="true"])',
                    'a.flex-active:not([aria-hidden="true"])'
                ];
                
                var landmarkSelectors = [
                    'main', 'nav', 'aside', 'section', 'header', 'footer',
                    '[role="main"]', '[role="navigation"]', '[role="complementary"]',
                    '[role="banner"]', '[role="contentinfo"]', '[role="region"]',
                    '[role="search"]'
                ];
                
                interactiveSelectors.forEach(function(selector) {
                    var elements = document.querySelectorAll(selector);
                    
                    elements.forEach(function(element) {
                        // Check if element is inside a landmark
                        var isInLandmark = false;
                        var parent = element.parentElement;
                        
                        while (parent && parent !== document.body) {
                            for (var i = 0; i < landmarkSelectors.length; i++) {
                                if (parent.matches(landmarkSelectors[i])) {
                                    isInLandmark = true;
                                    break;
                                }
                            }
                            if (isInLandmark) break;
                            parent = parent.parentElement;
                        }
                        
                        // If not in landmark, wrap in appropriate landmark
                        if (!isInLandmark) {
                            var wrapper;
                            
                            // Determine appropriate landmark based on element type
                            if (element.matches('input[type="search"], [class*="search"]')) {
                                wrapper = document.createElement('div');
                                wrapper.setAttribute('role', 'search');
                                wrapper.setAttribute('aria-label', 'Search');
                                wrapper.className = 'hello-landmark-search';
                            } else if (element.matches('nav *, [class*="nav"] *, [class*="menu"] *')) {
                                wrapper = document.createElement('nav');
                                wrapper.setAttribute('aria-label', 'Navigation');
                                wrapper.className = 'hello-landmark-nav';
                            } else if (element.matches('h1, h2, h3, h4, h5, h6')) {
                                // Headings should be in main content or section
                                wrapper = document.createElement('section');
                                var headingText = element.textContent.trim();
                                var label = headingText ? 'Section: ' + headingText.substring(0, 50) : 'Content Section';
                                wrapper.setAttribute('aria-labelledby', element.id || 'heading-' + Date.now());
                                if (!element.id) {
                                    element.id = 'heading-' + Date.now();
                                }
                                wrapper.className = 'hello-landmark-main';
                            } else if (element.matches('ul.slides, .slides')) {
                                // Slideshow/carousel content
                                wrapper = document.createElement('section');
                                wrapper.setAttribute('role', 'region');
                                wrapper.setAttribute('aria-label', 'Image Slideshow');
                                wrapper.className = 'hello-landmark-complementary';
                            } else if (element.matches('a.flex-active, .flex-active')) {
                                // Slider navigation
                                wrapper = document.createElement('nav');
                                wrapper.setAttribute('aria-label', 'Slideshow Navigation');
                                wrapper.className = 'hello-landmark-nav';
                            } else {
                                wrapper = document.createElement('div');
                                wrapper.setAttribute('role', 'region');
                                
                                // Generate unique, descriptive label based on element content/context
                                var label = generateUniqueRegionLabel(element);
                                wrapper.setAttribute('aria-label', label);
                                wrapper.className = 'hello-landmark-complementary';
                            }
                            
                            // Insert wrapper
                            element.parentNode.insertBefore(wrapper, element);
                            wrapper.appendChild(element);
                        }
                    });
                });
            }
            
            // Run on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', addMissingLandmarks);
            } else {
                addMissingLandmarks();
            }
            
            // Function to handle Elementor-specific content grouping
            function handleElementorContent() {
                // Group Elementor widgets that should be in the same landmark
                var elementorSections = document.querySelectorAll('.elementor-section:not([role]):not([aria-label])');
                elementorSections.forEach(function(section) {
                    if (!section.closest('main, [role="main"], section, [role="region"]')) {
                        section.setAttribute('role', 'region');
                        var headings = section.querySelectorAll('h1, h2, h3, h4, h5, h6');
                        if (headings.length > 0) {
                            var firstHeading = headings[0];
                            var sectionLabel = firstHeading.textContent.trim().substring(0, 50) || 'Content Section';
                            section.setAttribute('aria-label', sectionLabel);
                        } else {
                            section.setAttribute('aria-label', 'Content Section');
                        }
                        section.classList.add('hello-landmark-complementary');
                    }
                });
                
                // Handle JKit post blocks
                var jkitPostBlocks = document.querySelectorAll('.jeg-elementor-kit.jkit-postblock:not([role]):not([aria-label])');
                jkitPostBlocks.forEach(function(block) {
                    if (!block.closest('main, [role="main"], section, [role="region"]')) {
                        block.setAttribute('role', 'region');
                        block.setAttribute('aria-label', 'Post Content');
                        block.classList.add('hello-landmark-complementary');
                    }
                });
            }
            
            // Also run after Elementor loads (if present)
            if (window.elementorFrontend) {
                window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function() {
                    addMissingLandmarks();
                    handleElementorContent();
                });
            }
            
            // Run Elementor-specific handling
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', handleElementorContent);
            } else {
                handleElementorContent();
            }
            
        })();
        </script>
        <?php
    }
    
    /**
     * Add landmark to comment form
     */
    public function add_comment_form_landmark( $defaults ) {
        $defaults['comment_notes_before'] = '<div role="region" aria-label="' . esc_attr__( 'Comment Form', 'hello-elementor' ) . '" class="hello-landmark-complementary">' . $defaults['comment_notes_before'];
        $defaults['comment_notes_after'] .= '</div>';
        
        return $defaults;
    }
    
    /**
     * Wrap search form in proper landmark
     */
    public function wrap_search_form_in_landmark( $form ) {
        // Check if already wrapped
        if ( strpos( $form, 'role="search"' ) !== false ) {
            return $form;
        }
        
        $wrapped_form = '<div role="search" aria-label="' . esc_attr__( 'Search', 'hello-elementor' ) . '" class="hello-landmark-search">';
        $wrapped_form .= $form;
        $wrapped_form .= '</div>';
        
        return $wrapped_form;
    }
}

// Initialize the class
new Hello_Elementor_Semantic_Landmarks_Fix();

?>