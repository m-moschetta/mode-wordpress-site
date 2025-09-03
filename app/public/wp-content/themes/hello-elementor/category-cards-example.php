<?php
/**
 * Template di esempio per le card delle categorie
 * 
 * Questo file mostra come utilizzare lo shortcode [category_cards]
 * per visualizzare le categorie nella homepage o in altre pagine.
 * 
 * Per utilizzare questo template:
 * 1. Copia il contenuto dello shortcode
 * 2. Incollalo in una pagina Elementor usando il widget "Shortcode"
 * 3. Oppure inseriscilo direttamente nel contenuto di una pagina
 */

// Esempio di utilizzo dello shortcode
?>

<!-- ESEMPIO 1: Card categorie base (3 colonne) -->
[category_cards]

<!-- ESEMPIO 2: Card categorie con 4 colonne -->
[category_cards columns="4"]

<!-- ESEMPIO 3: Card categorie senza conteggio articoli -->
[category_cards show_count="no"]

<!-- ESEMPIO 4: Solo categorie specifiche -->
[category_cards include="1,2,3"]

<!-- ESEMPIO 5: Escludi categorie specifiche -->
[category_cards exclude="1"]

<!-- ESEMPIO 6: Configurazione completa -->
[category_cards columns="3" show_count="yes" exclude="1"]

<?php
/**
 * ISTRUZIONI PER L'USO:
 * 
 * 1. CREAZIONE CATEGORIE:
 *    - Vai su "Articoli > Categorie" nell'admin di WordPress
 *    - Crea le tue categorie (es: "Tecnologia", "Sport", "Cultura")
 *    - Il sistema creerà automaticamente una pagina per ogni categoria
 * 
 * 2. VISUALIZZAZIONE CARD CATEGORIE:
 *    - Modifica la tua homepage con Elementor
 *    - Aggiungi il widget "Shortcode"
 *    - Inserisci: [category_cards]
 *    - Personalizza con i parametri sopra
 * 
 * 3. GESTIONE PAGINE CATEGORIA:
 *    - Vai su "Pagine > Gestione Categorie" nell'admin
 *    - Clicca "Crea Pagine per Categorie Esistenti" se necessario
 *    - Ogni pagina categoria mostrerà automaticamente gli articoli filtrati
 * 
 * 4. PERSONALIZZAZIONE:
 *    - Le pagine categoria usano il widget JKit Post Block
 *    - Puoi modificare ogni pagina categoria individualmente
 *    - Oppure modificare le impostazioni di default nel functions.php
 * 
 * PARAMETRI SHORTCODE:
 * - columns: numero di colonne (1-6, default: 3)
 * - show_count: mostra numero articoli (yes/no, default: yes)
 * - include: solo queste categorie (IDs separati da virgola)
 * - exclude: escludi queste categorie (IDs separati da virgola)
 */
?>

<!-- CSS PERSONALIZZATO (opzionale) -->
<style>
/* Personalizza l'aspetto delle card categoria */
.category-cards-grid {
    margin: 40px 0;
}

.category-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.category-card h3 a {
    color: white !important;
}

.category-card p {
    color: rgba(255,255,255,0.8) !important;
}

.category-link {
    background: rgba(255,255,255,0.2) !important;
    border: 1px solid rgba(255,255,255,0.3) !important;
}

.category-link:hover {
    background: rgba(255,255,255,0.3) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .category-cards-grid {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 1024px) {
    .category-cards-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}
</style>