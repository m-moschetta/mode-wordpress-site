<?php
// File temporaneo per verificare le pagine categoria esistenti
require_once('wp-config.php');
require_once('wp-load.php');

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

echo "<h2>Pagine Categoria Esistenti:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID Pagina</th><th>Titolo</th><th>Status</th><th>ID Categoria</th><th>Nome Categoria</th><th>Azioni</th></tr>";

if (empty($category_pages)) {
    echo "<tr><td colspan='6'>Nessuna pagina categoria trovata</td></tr>";
} else {
    foreach ($category_pages as $page) {
        $category_id = get_post_meta($page->ID, '_category_page_id', true);
        $category = get_category($category_id);
        $category_name = $category ? $category->name : 'Categoria non trovata';
        
        echo "<tr>";
        echo "<td>" . $page->ID . "</td>";
        echo "<td>" . esc_html($page->post_title) . "</td>";
        echo "<td>" . $page->post_status . "</td>";
        echo "<td>" . $category_id . "</td>";
        echo "<td>" . esc_html($category_name) . "</td>";
        echo "<td><a href='" . get_edit_post_link($page->ID) . "' target='_blank'>Modifica</a> | <a href='" . get_permalink($page->ID) . "' target='_blank'>Visualizza</a></td>";
        echo "</tr>";
    }
}

echo "</table>";

// Mostra anche le categorie senza pagine
echo "<h2>Categorie Senza Pagine:</h2>";
$all_categories = get_categories(array('hide_empty' => false));
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID Categoria</th><th>Nome</th><th>Slug</th><th>Descrizione</th></tr>";

foreach ($all_categories as $category) {
    // Verifica se esiste una pagina per questa categoria
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
        echo "<tr>";
        echo "<td>" . $category->term_id . "</td>";
        echo "<td>" . esc_html($category->name) . "</td>";
        echo "<td>" . esc_html($category->slug) . "</td>";
        echo "<td>" . esc_html($category->description) . "</td>";
        echo "</tr>";
    }
}

echo "</table>";
?>