<?php
/**
 * Template Name: Dashboard Categorie
 * 
 * Template personalizzato per mostrare le categorie del dashboard
 * in stile card responsive
 */

get_header();
?>

<div class="dashboard-categorie-container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
    
    <header class="dashboard-header" style="text-align: center; margin-bottom: 50px;">
        <h1 style="font-size: 2.5em; color: #333; margin-bottom: 20px;">Dashboard delle Categorie</h1>
        <p style="font-size: 1.2em; color: #666; max-width: 600px; margin: 0 auto;">Esplora i nostri articoli organizzati per tematiche principali. Ogni categoria contiene approfondimenti e analisi specifiche.</p>
    </header>
    
    <!-- Sezione Categorie Principali -->
    <section class="categorie-principali" style="margin-bottom: 60px;">
        <h2 style="text-align: center; margin-bottom: 40px; color: #333;">📊 Categorie Principali</h2>
        
        <!-- Shortcode per mostrare tutte le categorie in 3 colonne -->
        <?php echo do_shortcode('[category_cards columns="3" show_count="yes"]'); ?>
    </section>
    
    <!-- Sezione Categorie in Evidenza (4 colonne) -->
    <section class="categorie-evidenza" style="margin-bottom: 60px;">
        <h2 style="text-align: center; margin-bottom: 40px; color: #333;">⭐ Layout Alternativo (4 Colonne)</h2>
        
        <!-- Shortcode per mostrare le categorie in 4 colonne -->
        <?php echo do_shortcode('[category_cards columns="4" show_count="no"]'); ?>
    </section>
    
    <!-- Sezione Istruzioni -->
    <section class="istruzioni" style="background: #f8f9fa; padding: 40px; border-radius: 10px; margin-bottom: 40px;">
        <h2 style="color: #333; margin-bottom: 30px;">🛠️ Come Personalizzare</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            
            <div class="istruzione-card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3 style="color: #0073aa; margin-bottom: 15px;">📝 Shortcode Base</h3>
                <code style="background: #f1f1f1; padding: 10px; display: block; border-radius: 4px; margin-bottom: 10px;">[category_cards]</code>
                <p style="color: #666; margin: 0;">Mostra tutte le categorie in 3 colonne con conteggio articoli</p>
            </div>
            
            <div class="istruzione-card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3 style="color: #0073aa; margin-bottom: 15px;">🎛️ Parametri Disponibili</h3>
                <ul style="margin: 0; padding-left: 20px; color: #666;">
                    <li><strong>columns</strong>: 1-6 colonne</li>
                    <li><strong>show_count</strong>: yes/no</li>
                    <li><strong>include</strong>: IDs specifici</li>
                    <li><strong>exclude</strong>: Escludi IDs</li>
                </ul>
            </div>
            
            <div class="istruzione-card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3 style="color: #0073aa; margin-bottom: 15px;">🎨 Esempi Avanzati</h3>
                <code style="background: #f1f1f1; padding: 5px; display: block; border-radius: 4px; margin-bottom: 5px; font-size: 12px;">[category_cards columns="4"]</code>
                <code style="background: #f1f1f1; padding: 5px; display: block; border-radius: 4px; margin-bottom: 5px; font-size: 12px;">[category_cards exclude="1,2"]</code>
                <code style="background: #f1f1f1; padding: 5px; display: block; border-radius: 4px; font-size: 12px;">[category_cards include="3,4,5"]</code>
            </div>
            
        </div>
    </section>
    
    <!-- Sezione Link Utili -->
    <section class="link-utili" style="text-align: center; margin-bottom: 40px;">
        <h2 style="color: #333; margin-bottom: 30px;">🔗 Link Utili</h2>
        
        <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
            <a href="<?php echo admin_url('edit.php?post_type=page&page=category-pages-manager'); ?>" 
               style="background: #0073aa; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; display: inline-block;"
               target="_blank">📋 Gestione Categorie</a>
            
            <a href="<?php echo admin_url('edit-tags.php?taxonomy=category'); ?>" 
               style="background: #00a32a; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; display: inline-block;"
               target="_blank">🏷️ Modifica Categorie</a>
            
            <a href="<?php echo admin_url('post-new.php'); ?>" 
               style="background: #8bc34a; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; display: inline-block;"
               target="_blank">✍️ Nuovo Articolo</a>
        </div>
    </section>
    
</div>

<!-- CSS Personalizzato per questa pagina -->
<style>
/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-categorie-container {
        padding: 20px 15px !important;
    }
    
    .dashboard-header h1 {
        font-size: 2em !important;
    }
    
    .dashboard-header p {
        font-size: 1em !important;
    }
    
    .istruzioni {
        padding: 20px !important;
    }
    
    .link-utili div {
        flex-direction: column !important;
        align-items: center;
    }
    
    .link-utili a {
        width: 200px;
        text-align: center;
    }
}

/* Hover Effects */
.istruzione-card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease;
}

.link-utili a:hover {
    transform: translateY(-2px);
    transition: transform 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Miglioramenti tipografici */
.dashboard-categorie-container h2 {
    font-weight: 600;
    letter-spacing: -0.5px;
}

.dashboard-categorie-container h3 {
    font-weight: 600;
}

code {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 13px;
}
</style>

<?php
get_footer();
?>