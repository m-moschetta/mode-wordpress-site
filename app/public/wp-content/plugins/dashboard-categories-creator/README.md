# Dashboard Categories Creator

## 📋 Descrizione

Plugin WordPress per creare automaticamente le categorie del dashboard con le relative pagine. Basato sull'immagine del dashboard fornita, questo plugin crea tutte le categorie necessarie e le relative pagine con configurazione automatica.

## 🚀 Funzionalità

### ✅ Categorie Create Automaticamente

1. **Stato della democrazia e della società**
   - Articoli riguardanti lo stato della democrazia, delle istituzioni democratiche e della società civile

2. **Commercio Internazionale**
   - Articoli su commercio internazionale, accordi commerciali e relazioni economiche globali

3. **Economia e disuguaglianza**
   - Articoli su temi economici, disuguaglianze sociali e distribuzione della ricchezza

4. **Intelligenza artificiale**
   - Articoli su intelligenza artificiale, tecnologie emergenti e loro impatto sulla società

5. **Inclusione e diritti**
   - Articoli su inclusione sociale, diritti umani e civili, diversità e pari opportunità

6. **Percezione e andamento del paese**
   - Articoli su sondaggi, opinioni pubbliche e percezioni riguardo all'andamento del paese

7. **Sicurezza ed ordine pubblico**
   - Articoli su sicurezza pubblica, ordine pubblico e politiche di sicurezza

### 🔧 Funzionalità Automatiche

- **Creazione Pagine**: Per ogni categoria viene creata automaticamente una pagina dedicata
- **Configurazione Elementor**: Ogni pagina è preconfigurata con il widget JKit Post Block
- **Filtro Automatico**: Gli articoli vengono automaticamente filtrati per categoria
- **Shortcode Integrato**: Disponibile `[category_cards]` per mostrare le categorie

## 📦 Installazione

1. Il plugin è già installato nella cartella `wp-content/plugins/dashboard-categories-creator/`
2. Vai su **Plugin > Plugin Installati** nell'admin di WordPress
3. Attiva il plugin "Dashboard Categories Creator"

## 🎯 Come Utilizzare

### Passo 1: Attivazione
1. Attiva il plugin dall'area admin
2. Vai su **Strumenti > Crea Categorie Dashboard**

### Passo 2: Creazione Categorie
1. Clicca su "🚀 Crea Tutte le Categorie"
2. Conferma l'operazione
3. Le categorie e le relative pagine verranno create automaticamente

### Passo 3: Visualizzazione
1. Usa lo shortcode `[category_cards]` nella tua homepage
2. Personalizza con i parametri disponibili
3. Ogni categoria avrà la sua pagina dedicata

## 🎨 Shortcode Disponibili

### Shortcode Base
```php
[category_cards]
```

### Parametri Disponibili

- **columns**: Numero di colonne (1-6, default: 3)
- **show_count**: Mostra numero articoli (yes/no, default: yes)
- **include**: Solo queste categorie (IDs separati da virgola)
- **exclude**: Escludi queste categorie (IDs separati da virgola)

### Esempi di Utilizzo

```php
// 4 colonne
[category_cards columns="4"]

// Senza conteggio articoli
[category_cards show_count="no"]

// Solo categorie specifiche
[category_cards include="1,2,3"]

// Escludi categorie specifiche
[category_cards exclude="1"]

// Configurazione completa
[category_cards columns="3" show_count="yes" exclude="1"]
```

## 🔗 Integrazione con il Sistema Esistente

Questo plugin si integra perfettamente con il sistema di gestione categorie già presente nel tema:

- **Compatibile** con il sistema automatico di creazione pagine
- **Utilizza** gli stessi hook e funzioni del tema
- **Estende** le funzionalità esistenti senza conflitti

## 📋 Gestione Post-Installazione

### Verifica Categorie Create
1. Vai su **Articoli > Categorie** per vedere le categorie
2. Vai su **Pagine > Gestione Categorie** per verificare le pagine associate

### Personalizzazione Pagine
1. Ogni pagina categoria può essere modificata con Elementor
2. Il widget JKit Post Block è preconfigurato ma personalizzabile
3. Layout e stile possono essere modificati individualmente

### Aggiunta Contenuti
1. Crea nuovi articoli e assegnali alle categorie appropriate
2. Gli articoli appariranno automaticamente nelle relative pagine categoria
3. Il conteggio nelle card si aggiornerà automaticamente

## 🛠️ Requisiti Tecnici

- **WordPress**: 5.0 o superiore
- **PHP**: 7.4 o superiore
- **Plugin richiesti**: 
  - Elementor (Free o Pro)
  - JEG Elementor Kit (per il widget Post Block)

## 🔧 Risoluzione Problemi

### Le categorie non vengono create
- Verifica di avere i permessi di amministratore
- Controlla che non esistano già categorie con lo stesso nome

### Le pagine non si visualizzano correttamente
- Verifica che Elementor sia attivo
- Controlla che JEG Elementor Kit sia installato e attivo

### Lo shortcode non funziona
- Verifica la sintassi: `[category_cards]`
- Controlla che ci siano categorie pubblicate
- Assicurati che il tema supporti gli shortcode

## 📞 Supporto

Per supporto tecnico o domande:
1. Controlla la documentazione del tema in `README-CATEGORIE.md`
2. Verifica la sezione "Gestione Categorie" nell'admin
3. Consulta i log di WordPress per eventuali errori

## 🔄 Aggiornamenti Futuri

Il plugin è progettato per essere:
- **Estendibile**: Facile aggiungere nuove categorie
- **Manutenibile**: Codice pulito e commentato
- **Compatibile**: Segue le best practices WordPress

---

**Versione**: 1.0.0  
**Compatibilità**: WordPress 5.0+, Elementor 3.0+  
**Licenza**: GPL v2 o successiva