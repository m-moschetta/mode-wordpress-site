<?php
/**
 * Plugin Name: Dashboard Categories Creator
 * Description: Crea automaticamente le categorie del dashboard con le relative pagine
 * Version: 1.0.0
 * Author: Dashboard Team
 */

// Previeni accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale del plugin
 */
class DashboardCategoriesCreator {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
    }
    
    /**
     * Aggiunge il menu admin
     */
    public function add_admin_menu() {
        add_management_page(
            'Crea Categorie Dashboard',
            'Crea Categorie Dashboard',
            'manage_options',
            'dashboard-categories-creator',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Gestisce le azioni admin
     */
    public function handle_admin_actions() {
        if (isset($_GET['action']) && $_GET['action'] === 'create_dashboard_categories' && current_user_can('manage_options')) {
            check_admin_referer('create_dashboard_categories');
            $this->create_dashboard_categories();
            wp_redirect(admin_url('tools.php?page=dashboard-categories-creator&created=1'));
            exit;
        }
    }
    
    /**
     * Pagina admin del plugin
     */
    public function admin_page() {
        $created = isset($_GET['created']) ? true : false;
        ?>
        <div class="wrap">
            <h1>🚀 Crea Categorie Dashboard</h1>
            
            <?php if ($created): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>✅ Categorie create con successo!</strong> Le pagine sono state generate automaticamente.</p>
                </div>
            <?php endif; ?>
            
            <div class="card" style="max-width: 800px;">
                <h2>📊 Categorie da Creare</h2>
                <p>Questo strumento creerà automaticamente le seguenti categorie basate sull'immagine del dashboard fornita:</p>
                
                <ul style="list-style-type: none; padding: 0;">
                    <?php foreach ($this->get_categories_data() as $category): ?>
                        <li style="padding: 10px; margin: 5px 0; background: #f9f9f9; border-left: 4px solid #0073aa;">
                            <strong><?php echo esc_html($category['name']); ?></strong><br>
                            <small style="color: #666;"><?php echo esc_html($category['description']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <h3>🎯 Cosa Succederà:</h3>
                <ol>
                    <li><strong>Creazione Categorie:</strong> Verranno create tutte le categorie elencate sopra</li>
                    <li><strong>Pagine Automatiche:</strong> Per ogni categoria verrà creata automaticamente una pagina dedicata</li>
                    <li><strong>Configurazione Elementor:</strong> Ogni pagina sarà preconfigurata con il widget JKit Post Block</li>
                    <li><strong>Shortcode Disponibile:</strong> Potrai usare <code>[category_cards]</code> per mostrare le categorie</li>
                </ol>
                
                <p style="margin-top: 30px;">
                    <a href="<?php echo wp_nonce_url(admin_url('tools.php?page=dashboard-categories-creator&action=create_dashboard_categories'), 'create_dashboard_categories'); ?>" 
                       class="button button-primary button-large"
                       onclick="return confirm('Sei sicuro di voler creare tutte le categorie? Questa operazione non può essere annullata.');">🚀 Crea Tutte le Categorie</a>
                </p>
                
                <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin-top: 20px;">
                    <h4>💡 Dopo la Creazione:</h4>
                    <ul>
                        <li>Vai su <a href="<?php echo admin_url('edit.php?post_type=page&page=category-pages-manager'); ?>">Pagine > Gestione Categorie</a> per verificare</li>
                        <li>Usa lo shortcode <code>[category_cards]</code> nella tua homepage</li>
                        <li>Personalizza ogni pagina categoria con Elementor</li>
                    </ul>
                </div>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>📋 Categorie Esistenti</h2>
                <?php $this->show_existing_categories(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostra le categorie esistenti
     */
    private function show_existing_categories() {
        $categories = get_terms(array(
            'taxonomy' => 'category',
            'hide_empty' => false
        ));
        
        if (empty($categories)) {
            echo '<p>Nessuna categoria esistente.</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome Categoria</th><th>Slug</th><th>Articoli</th><th>Pagina Associata</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($categories as $category) {
            // Controlla se esiste una pagina associata
            $pages = get_posts(array(
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
            
            echo '<tr>';
            echo '<td><strong>' . esc_html($category->name) . '</strong></td>';
            echo '<td>' . esc_html($category->slug) . '</td>';
            echo '<td>' . $category->count . '</td>';
            
            if (!empty($pages)) {
                $page = $pages[0];
                echo '<td><a href="' . get_edit_post_link($page->ID) . '">✅ ' . esc_html($page->post_title) . '</a></td>';
            } else {
                echo '<td><span style="color: #999;">❌ Nessuna pagina</span></td>';
            }
            
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Ottiene i dati delle categorie da creare
     */
    private function get_categories_data() {
        return array(
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
                'description' => 'Articoli su sondaggi, opinioni pubbliche e percezioni riguardo all\'andamento del paese',
                'slug' => 'percezione-andamento-paese'
            ),
            array(
                'name' => 'Sicurezza ed ordine pubblico',
                'description' => 'Articoli su sicurezza pubblica, ordine pubblico e politiche di sicurezza',
                'slug' => 'sicurezza-ordine-pubblico'
            )
        );
    }
    
    /**
     * Crea tutte le categorie del dashboard
     */
    private function create_dashboard_categories() {
        $categories_data = $this->get_categories_data();
        $created_count = 0;
        
        foreach ($categories_data as $category_data) {
            // Controlla se la categoria esiste già
            $existing_term = term_exists($category_data['name'], 'category');
            
            if ($existing_term) {
                continue; // Salta se esiste già
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
            
            if (!is_wp_error($result)) {
                $created_count++;
            }
        }
        
        // Salva il numero di categorie create per mostrarlo nella notifica
        set_transient('dashboard_categories_created_count', $created_count, 60);
    }
}

// Inizializza il plugin
new DashboardCategoriesCreator();

/**
 * Attivazione del plugin
 */
register_activation_hook(__FILE__, function() {
    // Nessuna azione specifica necessaria all'attivazione
});

/**
 * Disattivazione del plugin
 */
register_deactivation_hook(__FILE__, function() {
    // Pulisci i transient
    delete_transient('dashboard_categories_created_count');
});
?>