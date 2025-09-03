<?php
// Script temporaneo per controllare il valore di blogname
require_once 'wp-config.php';
require_once 'wp-includes/wp-db.php';
require_once 'wp-includes/functions.php';
require_once 'wp-includes/option.php';

// Connessione al database
$wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

// Ottieni il valore di blogname
$blogname = get_option('blogname');

echo "Titolo del sito (blogname): " . $blogname . "\n";

// Mostra anche altre opzioni correlate
echo "Tagline (blogdescription): " . get_option('blogdescription') . "\n";
echo "URL del sito (home): " . get_option('home') . "\n";
echo "URL WordPress (siteurl): " . get_option('siteurl') . "\n";
?>