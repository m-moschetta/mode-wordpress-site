<?php
// File per aggiornare le pagine categoria esistenti
require_once('wp-config.php');
require_once('wp-load.php');

if (isset($_POST['update_pages'])) {
    // Trova tutte le pagine categoria esistenti
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
            
            // Aggiungi CSS personalizzato
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
    
    echo '<div style="background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;">';
    echo '<strong>✅ Aggiornamento completato!</strong><br>';
    echo 'Sono state aggiornate ' . $updated_count . ' pagine categoria.';
    echo '</div>';
}

// Mostra form e pagine esistenti
echo '<h1>🔧 Aggiorna Pagine Categoria Esistenti</h1>';
echo '<p>Questo strumento aggiornerà tutte le pagine categoria esistenti con contenuto migliorato e styling.</p>';

// Form per aggiornare
echo '<form method="post" style="margin: 20px 0;">';
echo '<input type="submit" name="update_pages" value="🚀 Aggiorna Tutte le Pagine Categoria" style="background: #0073aa; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;" />';
echo '</form>';

// Trova e mostra le pagine esistenti
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

echo '<h2>📄 Pagine Categoria Esistenti (' . count($category_pages) . ')</h2>';

if (empty($category_pages)) {
    echo '<p style="color: #666;">Nessuna pagina categoria trovata. Vai su <a href="/wp-admin/edit.php?post_type=page&page=category-pages-manager">Pagine > Gestione Categorie</a> per crearle.</p>';
} else {
    echo '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
    echo '<thead style="background: #f1f1f1;">';
    echo '<tr><th style="padding: 10px; border: 1px solid #ddd;">Titolo Pagina</th><th style="padding: 10px; border: 1px solid #ddd;">Categoria</th><th style="padding: 10px; border: 1px solid #ddd;">Status</th><th style="padding: 10px; border: 1px solid #ddd;">Azioni</th></tr>';
    echo '</thead><tbody>';
    
    foreach ($category_pages as $page) {
        $category_id = get_post_meta($page->ID, '_category_page_id', true);
        $category = get_category($category_id);
        $category_name = $category ? $category->name : 'Categoria non trovata';
        
        echo '<tr>';
        echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($page->post_title) . '</td>';
        echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($category_name) . '</td>';
        echo '<td style="padding: 10px; border: 1px solid #ddd;">';
        if ($page->post_status == 'publish') {
            echo '<span style="color: green;">✅ Pubblicata</span>';
        } else {
            echo '<span style="color: orange;">⏳ ' . ucfirst($page->post_status) . '</span>';
        }
        echo '</td>';
        echo '<td style="padding: 10px; border: 1px solid #ddd;">';
        echo '<a href="' . get_edit_post_link($page->ID) . '" target="_blank" style="margin-right: 10px;">✏️ Modifica</a>';
        echo '<a href="' . get_permalink($page->ID) . '" target="_blank" style="margin-right: 10px;">👁️ Visualizza</a>';
        echo '<a href="' . get_permalink($page->ID) . '?elementor" target="_blank">🎨 Elementor</a>';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
}

echo '<hr style="margin: 40px 0;">';
echo '<p><strong>💡 Suggerimenti:</strong></p>';
echo '<ul>';
echo '<li>Dopo l\'aggiornamento, puoi personalizzare ulteriormente ogni pagina con Elementor</li>';
echo '<li>Le pagine aggiornate includeranno automaticamente gli articoli della categoria</li>';
echo '<li>Il layout è responsive e ottimizzato per tutti i dispositivi</li>';
echo '</ul>';

echo '<p><a href="/wp-admin/edit.php?post_type=page&page=category-pages-manager">← Torna alla Gestione Categorie</a></p>';
?>