# Sistema Automatico Gestione Categorie

Questo sistema implementa la gestione automatica delle categorie e delle relative pagine per WordPress con Elementor.

## 🚀 Funzionalità Implementate

### 1. Creazione Automatica Pagine Categoria
- **Automatico**: Ogni volta che crei una nuova categoria, viene automaticamente creata una pagina dedicata
- **Configurazione**: Ogni pagina usa il widget JKit Post Block preconfigurato
- **Filtro**: Gli articoli vengono automaticamente filtrati per categoria

### 2. Gestione Completa del Ciclo di Vita
- **Creazione**: Nuova categoria → Nuova pagina automatica
- **Modifica**: Categoria rinominata → Titolo pagina aggiornato
- **Eliminazione**: Categoria eliminata → Pagina eliminata

### 3. Shortcode per Card Categorie
- **Visualizzazione**: `[category_cards]` per mostrare tutte le categorie
- **Personalizzazione**: Parametri per colonne, conteggio, inclusioni/esclusioni
- **Responsive**: Design adattivo per mobile e tablet

### 4. Pannello di Controllo Admin
- **Menu**: "Pagine > Gestione Categorie"
- **Azioni**: Creazione batch per categorie esistenti
- **Monitoraggio**: Tabella con categorie e pagine associate

## 📋 Come Utilizzare

### Passo 1: Creare le Categorie
1. Vai su **Articoli > Categorie**
2. Crea le tue categorie (es: "Tecnologia", "Sport", "Cultura")
3. Le pagine verranno create automaticamente

### Passo 2: Configurare la Homepage
1. Modifica la homepage con **Elementor**
2. Aggiungi il widget **"Shortcode"**
3. Inserisci: `[category_cards]`
4. Personalizza con i parametri disponibili

### Passo 3: Gestire Categorie Esistenti (se necessario)
1. Vai su **Pagine > Gestione Categorie**
2. Clicca **"Crea Pagine per Categorie Esistenti"**
3. Verifica la tabella delle associazioni

## 🛠️ Parametri Shortcode

```php
// Esempi di utilizzo
[category_cards]                                    // Base (3 colonne)
[category_cards columns="4"]                        // 4 colonne
[category_cards show_count="no"]                    // Senza conteggio
[category_cards include="1,2,3"]                    // Solo categorie 1,2,3
[category_cards exclude="1"]                        // Escludi categoria 1
[category_cards columns="3" show_count="yes" exclude="1"] // Completo
```

### Parametri Disponibili:
- **columns**: Numero di colonne (1-6, default: 3)
- **show_count**: Mostra numero articoli (yes/no, default: yes)
- **include**: Solo queste categorie (IDs separati da virgola)
- **exclude**: Escludi queste categorie (IDs separati da virgola)

## ⚙️ Configurazione Avanzata

### Personalizzare le Pagine Categoria
Le pagine categoria sono preconfigurate con:
- **Widget**: JKit Post Block
- **Articoli**: 9 per pagina
- **Layout**: Griglia 3 colonne
- **Tipo**: Type-1
- **Categoria**: Abilitata
- **Excerpt**: Abilitato (20 parole)

### Modificare le Impostazioni Default
Nel file `functions.php`, cerca la sezione:
```php
'settings' => array(
    'sg_content_filter_category' => array($term_id),
    'sg_content_number_post' => 9,        // Cambia qui
    'sg_setting_column' => 3,             // Cambia qui
    'sg_setting_post_block_type' => 'type-1', // Cambia qui
    // ...
)
```

## 🎨 Personalizzazione CSS

Per personalizzare l'aspetto delle card categoria, aggiungi CSS personalizzato:

```css
.category-cards-grid {
    margin: 40px 0;
}

.category-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none !important;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
```

## 🔧 Funzioni Helper Disponibili

### `get_category_page_url($category_id)`
Ottiene l'URL della pagina categoria:
```php
$url = get_category_page_url(5); // URL della pagina per categoria ID 5
```

### `create_pages_for_existing_categories()`
Crea pagine per tutte le categorie esistenti:
```php
create_pages_for_existing_categories();
```

## 📱 Responsive Design

Il sistema è completamente responsive:
- **Desktop**: Numero di colonne configurabile
- **Tablet**: Massimo 2 colonne
- **Mobile**: 1 colonna

## 🔍 Troubleshooting

### Problema: Le pagine non vengono create
**Soluzione**: Vai su "Pagine > Gestione Categorie" e clicca "Crea Pagine per Categorie Esistenti"

### Problema: Le card non si visualizzano
**Soluzione**: Verifica che lo shortcode sia scritto correttamente: `[category_cards]`

### Problema: Gli articoli non vengono filtrati
**Soluzione**: Verifica che il plugin JEG Elementor Kit sia attivo e aggiornato

## 📝 Note Tecniche

- **Hook utilizzati**: `created_category`, `edited_category`, `delete_category`
- **Meta fields**: `_category_page_id` per collegare pagine e categorie
- **Elementor**: Compatibile con Elementor Pro e Free
- **Plugin richiesto**: JEG Elementor Kit per il widget Post Block

## 🔄 Aggiornamenti Futuri

Il sistema è progettato per essere:
- **Estendibile**: Facile aggiungere nuove funzionalità
- **Manutenibile**: Codice pulito e commentato
- **Compatibile**: Segue le best practices WordPress

---

**Sviluppato per**: WordPress + Elementor + JEG Elementor Kit  
**Compatibilità**: WordPress 5.0+, Elementor 3.0+  
**Licenza**: GPL v2 o successiva