<?php
/**
 * Script per creare automaticamente le categorie dal dashboard
 * Accedi a questo file via browser: http://localhost:10004/create-categories.php
 */

// Carica WordPress
require_once('wp-load.php');

// Categorie da creare basate sull'immagine del dashboard
$categories_to_create = array(
    array(
        'name' => 'Stato della democrazia e della società',
        'description' => 'Articoli riguardanti lo stato della democrazia, delle istituzioni democratiche e della società civile',
        'slug' => 'stato-democrazia-societa'
    ),
    array(
        'name' => 'Commercio Internazionale',
        'description' => 'Articoli su commercio internazionale, accordi commerciali e relazioni economiche globali',
        'slug' => 'commercio-internazionale'
    ),
    array(
        'name' => 'Economia e disuguaglianza',
        'description' => 'Articoli su temi economici, disuguaglianze sociali e distribuzione della ricchezza',
        'slug' => 'economia-disuguaglianza'
    ),
    array(
        'name' => 'Intelligenza artificiale',
        'description' => 'Articoli su intelligenza artificiale, tecnologie emergenti e loro impatto sulla società',
        'slug' => 'intelligenza-artificiale'
    ),
    array(
        'name' => 'Inclusione e diritti',
        'description' => 'Articoli su inclusione sociale, diritti umani e civili, diversità e pari opportunità',
        'slug' => 'inclusione-diritti'
    ),
    array(
        'name' => 'Percezione e andamento del paese',
        'description' => 'Articoli su sondaggi, opinioni pubbliche e percezioni riguardo l\'andamento del paese',
        'slug' => 'percezione-andamento-paese'
    ),
    array(
        'name' => 'Sicurezza ed ordine pubblico',
        'description' => 'Articoli su sicurezza pubblica, ordine pubblico e politiche di sicurezza',
        'slug' => 'sicurezza-ordine-pubblico'
    )
);

echo "<!DOCTYPE html><html><head><title>Creazione Categorie</title><style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;}</style></head><body>";
echo "<h1>🚀 Creazione Categorie per il Dashboard</h1>";
echo "<p>Creazione delle categorie basate sull'immagine del dashboard fornita...</p>";

$created_categories = array();
$existing_categories = array();
$errors = array();

foreach ($categories_to_create as $category_data) {
    // Controlla se la categoria esiste già
    $existing_term = term_exists($category_data['name'], 'category');
    
    if ($existing_term) {
        $existing_categories[] = $category_data['name'];
        continue;
    }
    
    // Crea la categoria
    $result = wp_insert_term(
        $category_data['name'],
        'category',
        array(
            'description' => $category_data['description'],
            'slug' => $category_data['slug']
        )
    );
    
    if (is_wp_error($result)) {
        $errors[] = 'Errore nella creazione di "' . $category_data['name'] . '": ' . $result->get_error_message();
    } else {
        $created_categories[] = $category_data['name'];
        echo "<p style='color: green; background: #f0f8f0; padding: 10px; border-left: 4px solid green;'>✓ Categoria creata: <strong>" . $category_data['name'] . "</strong></p>";
    }
}

// Mostra risultati
echo "<h2>📊 Risultati:</h2>";

if (!empty($created_categories)) {
    echo "<div style='background: #f0f8f0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: green; margin-top: 0;'>✅ Categorie create con successo (" . count($created_categories) . "):</h3>";
    echo "<ul>";
    foreach ($created_categories as $category) {
        echo "<li><strong>" . $category . "</strong></li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($existing_categories)) {
    echo "<div style='background: #fff8e1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: orange; margin-top: 0;'>⚠️ Categorie già esistenti (" . count($existing_categories) . "):</h3>";
    echo "<ul>";
    foreach ($existing_categories as $category) {
        echo "<li>" . $category . "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($errors)) {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: red; margin-top: 0;'>❌ Errori (" . count($errors) . "):</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . $error . "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<h2>🎯 Prossimi Passi:</h2>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px;'>";
echo "<ol>";
echo "<li><strong>Le pagine per ogni categoria sono state create automaticamente</strong> dal sistema</li>";
echo "<li>Vai su <a href='" . admin_url('edit.php?post_type=page&page=category-pages-manager') . "' target='_blank'>Pagine > Gestione Categorie</a> per verificare</li>";
echo "<li>Usa lo shortcode <code style='background: #f5f5f5; padding: 2px 5px;'>[category_cards]</code> nella tua homepage per mostrare le categorie</li>";
echo "<li>Puoi personalizzare ogni pagina categoria modificandola con Elementor</li>";
echo "</ol>";
echo "</div>";

echo "<div style='margin: 30px 0; text-align: center;'>";
echo "<a href='" . admin_url() . "' style='background: #0073aa; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;'>🏠 Vai alla Dashboard Admin</a>";
echo "<a href='" . admin_url('edit.php?post_type=page&page=category-pages-manager') . "' style='background: #00a32a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;'>📋 Gestione Categorie</a>";
echo "<a href='" . home_url() . "' style='background: #8bc34a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;'>🌐 Visualizza Sito</a>";
echo "</div>";

echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>💡 Come utilizzare le categorie:</h3>";
echo "<p><strong>1. Per mostrare tutte le categorie:</strong><br><code>[category_cards]</code></p>";
echo "<p><strong>2. Per mostrare 4 colonne:</strong><br><code>[category_cards columns=\"4\"]</code></p>";
echo "<p><strong>3. Per escludere una categoria:</strong><br><code>[category_cards exclude=\"1\"]</code></p>";
echo "</div>";

echo "<p style='text-align: center; color: #666; font-size: 14px;'><em>💡 Suggerimento: Questo file può essere eliminato dopo l'esecuzione.</em></p>";
echo "</body></html>";
?>