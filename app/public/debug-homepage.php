<?php
// Debug script per identificare il problema di padding "Monitor Democracy"
require_once('wp-config.php');

// Connessione al database
global $wpdb;

// Trova la homepage
$homepage_id = get_option('page_on_front');
echo "<h2>Homepage ID: $homepage_id</h2>";

// Ottieni i metadati Elementor della homepage
$elementor_data = get_post_meta($homepage_id, '_elementor_data', true);

if ($elementor_data) {
    $data = json_decode($elementor_data, true);
    
    echo "<h3>Ricerca 'Monitor Democracy' nel contenuto Elementor:</h3>";
    
    function search_in_array($array, $search_term) {
        $results = [];
        
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_string($value) && stripos($value, $search_term) !== false) {
                    $results[] = [
                        'key' => $key,
                        'value' => $value,
                        'context' => 'Found in: ' . $key
                    ];
                }
                
                if (is_array($value)) {
                    $nested_results = search_in_array($value, $search_term);
                    $results = array_merge($results, $nested_results);
                }
            }
        }
        
        return $results;
    }
    
    // Cerca "Monitor Democracy"
    $monitor_results = search_in_array($data, 'Monitor Democracy');
    $monitor_results2 = search_in_array($data, 'monitor');
    $monitor_results3 = search_in_array($data, 'democracy');
    
    if (!empty($monitor_results)) {
        echo "<h4>Trovato 'Monitor Democracy':</h4>";
        foreach ($monitor_results as $result) {
            echo "<p><strong>" . htmlspecialchars($result['context']) . ":</strong><br>";
            echo htmlspecialchars($result['value']) . "</p>";
        }
    }
    
    if (!empty($monitor_results2)) {
        echo "<h4>Trovato 'monitor':</h4>";
        foreach ($monitor_results2 as $result) {
            echo "<p><strong>" . htmlspecialchars($result['context']) . ":</strong><br>";
            echo htmlspecialchars($result['value']) . "</p>";
        }
    }
    
    if (!empty($monitor_results3)) {
        echo "<h4>Trovato 'democracy':</h4>";
        foreach ($monitor_results3 as $result) {
            echo "<p><strong>" . htmlspecialchars($result['context']) . ":</strong><br>";
            echo htmlspecialchars($result['value']) . "</p>";
        }
    }
    
    if (empty($monitor_results) && empty($monitor_results2) && empty($monitor_results3)) {
        echo "<p>Nessun risultato trovato per 'Monitor Democracy', 'monitor' o 'democracy'.</p>";
        
        // Cerca tutti i titoli/testi nella homepage
        echo "<h4>Tutti i testi trovati nella homepage:</h4>";
        $text_results = search_in_array($data, '');
        
        $text_fields = ['title', 'text', 'content', 'heading', 'description'];
        
        foreach ($text_results as $result) {
            $key_lower = strtolower($result['key']);
            foreach ($text_fields as $field) {
                if (strpos($key_lower, $field) !== false && !empty(trim($result['value']))) {
                    echo "<p><strong>" . htmlspecialchars($result['key']) . ":</strong><br>";
                    echo htmlspecialchars(substr($result['value'], 0, 200)) . "...</p>";
                    break;
                }
            }
        }
    }
    
    // Cerca problemi di padding responsivo
    echo "<h3>Analisi padding responsivo:</h3>";
    $padding_results = search_in_array($data, 'padding');
    $margin_results = search_in_array($data, 'margin');
    
    echo "<h4>Impostazioni di padding trovate:</h4>";
    foreach ($padding_results as $result) {
        if (strpos($result['key'], 'responsive') !== false || strpos($result['key'], 'tablet') !== false || strpos($result['key'], 'mobile') !== false) {
            echo "<p><strong>" . htmlspecialchars($result['key']) . ":</strong><br>";
            echo htmlspecialchars($result['value']) . "</p>";
        }
    }
    
    echo "<h4>Impostazioni di margin trovate:</h4>";
    foreach ($margin_results as $result) {
        if (strpos($result['key'], 'responsive') !== false || strpos($result['key'], 'tablet') !== false || strpos($result['key'], 'mobile') !== false) {
            echo "<p><strong>" . htmlspecialchars($result['key']) . ":</strong><br>";
            echo htmlspecialchars($result['value']) . "</p>";
        }
    }
    
} else {
    echo "<p>Nessun dato Elementor trovato per la homepage.</p>";
}

// Cerca anche nei post/pagine
echo "<h3>Ricerca in tutti i post/pagine:</h3>";
$posts = $wpdb->get_results("
    SELECT p.ID, p.post_title, p.post_content 
    FROM {$wpdb->posts} p 
    WHERE p.post_status = 'publish' 
    AND (p.post_content LIKE '%Monitor Democracy%' 
         OR p.post_content LIKE '%monitor%' 
         OR p.post_content LIKE '%democracy%')
    ORDER BY p.post_date DESC
");

if ($posts) {
    foreach ($posts as $post) {
        echo "<h4>Post: " . htmlspecialchars($post->post_title) . " (ID: $post->ID)</h4>";
        echo "<p>" . htmlspecialchars(substr($post->post_content, 0, 300)) . "...</p>";
    }
} else {
    echo "<p>Nessun post trovato con 'Monitor Democracy', 'monitor' o 'democracy'.</p>";
}
?>