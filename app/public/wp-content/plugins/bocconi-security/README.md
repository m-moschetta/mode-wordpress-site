# Bocconi Security Plugin

Un plugin di sicurezza completo per WordPress basato sugli standard OWASP ASVS 4.0, sviluppato specificamente per il sito web dell'Università Bocconi.

## Caratteristiche Principali

### 🛡️ Sicurezza Completa OWASP ASVS 4.0
- **V1 - Architettura**: Rimozione informazioni sensibili, disabilitazione XML-RPC
- **V2 - Autenticazione**: Protezione contro brute force, blocco IP temporaneo
- **V3 - Gestione Sessioni**: Cookie sicuri, rigenerazione ID sessione
- **V4 - Controllo Accessi**: Protezione file sensibili, limitazione accesso admin
- **V5 - Validazione Input**: Sanitizzazione input, validazione upload file
- **V7 - Gestione Errori**: Nascondere informazioni sensibili negli errori
- **V9 - Comunicazioni**: Forzatura HTTPS, header di sicurezza
- **V10 - Protezione Codice Malevolo**: Scansione file upload, blocco estensioni pericolose
- **V11 - Logica Business**: Rate limiting, protezione contro spam
- **V12 - Sicurezza File**: Protezione directory upload, controllo permessi
- **V13 - Sicurezza API**: Limitazione accesso REST API, autenticazione endpoint
- **V14 - Configurazione**: Monitoraggio configurazioni non sicure 

### 📊 Dashboard di Sicurezza
- Punteggio di sicurezza in tempo reale
- Monitoraggio minacce attive
- Report OWASP dettagliati
- Log di sicurezza completi
- Stato del sistema in tempo reale

### 🔧 Configurazione Avanzata
- Preset di sicurezza (Base, Intermedio, Avanzato)
- Configurazione granulare per ogni categoria OWASP
- Esportazione/Importazione configurazioni
- Raccomandazioni automatiche

## Installazione

1. Carica la cartella `bocconi-security` nella directory `/wp-content/plugins/`
2. Attiva il plugin dal menu 'Plugin' di WordPress
3. Vai su 'Bocconi Security' nel menu di amministrazione
4. Configura le impostazioni di sicurezza secondo le tue necessità

## Struttura del Plugin

```
bocconi-security/
├── bocconi-security.php          # File principale del plugin
├── includes/
│   ├── class-security-config.php      # Gestione configurazioni
│   ├── class-security-implementation.php # Implementazione misure di sicurezza
│   └── class-admin-dashboard.php       # Dashboard amministrativa
├── assets/
│   ├── admin-style.css               # Stili interfaccia admin
│   └── admin-script.js               # Script interfaccia admin
└── README.md                         # Documentazione
```

## Configurazioni di Sicurezza Applicate

### wp-config.php
Il plugin modifica automaticamente le seguenti configurazioni:

```php
// Sicurezza file
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);

// Ottimizzazioni database
define('WP_POST_REVISIONS', 3);
define('AUTOSAVE_INTERVAL', 300);
define('EMPTY_TRASH_DAYS', 7);

// Sicurezza SSL
define('FORCE_SSL_ADMIN', true);
define('FORCE_SSL_LOGIN', true);

// Cookie sicuri
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Header di sicurezza
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
```

## Funzionalità di Sicurezza

### Protezione Brute Force
- Tracciamento tentativi di login falliti
- Blocco temporaneo IP dopo 5 tentativi
- Reset automatico dopo login riuscito
- Log dettagliato degli attacchi

### Validazione File Upload
- Controllo estensioni file pericolose
- Scansione contenuto per codice malevolo
- Limitazione dimensioni file
- Protezione directory upload

### Monitoraggio Sicurezza
- Controllo integrità file critici
- Rilevamento pattern SQL injection
- Monitoraggio modifiche configurazioni
- Alert email per eventi critici

### Header di Sicurezza
- X-Content-Type-Options: nosniff
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection: 1; mode=block
- Content-Security-Policy
- Strict-Transport-Security
- Referrer-Policy
- Permissions-Policy

## Dashboard Amministrativa

### Pagine Disponibili
1. **Dashboard**: Panoramica sicurezza, punteggio, minacce recenti
2. **Configurazione**: Impostazioni dettagliate per ogni categoria OWASP
3. **Log di Sicurezza**: Cronologia eventi e minacce
4. **Report OWASP**: Valutazione compliance ASVS 4.0

### Azioni Rapide
- Scansione sicurezza completa
- Pulizia log di sicurezza
- Reset tentativi login falliti
- Aggiornamento configurazione
- Esportazione report

## Requisiti di Sistema

- WordPress 5.0 o superiore
- PHP 7.4 o superiore
- MySQL 5.6 o superiore
- HTTPS abilitato (raccomandato)
- Permessi di scrittura su wp-config.php

## Configurazione Raccomandata

### Per Siti in Produzione
```php
// Disabilita debug
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// Forza HTTPS
define('FORCE_SSL_ADMIN', true);

// Cambia prefisso database
$table_prefix = 'boc_';

// Limita revisioni
define('WP_POST_REVISIONS', 3);
```

### Per Siti in Sviluppo
```php
// Abilita debug sicuro
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Log errori
define('SCRIPT_DEBUG', true);
```

## Monitoraggio e Manutenzione

### Log di Sicurezza
Il plugin mantiene log dettagliati di:
- Tentativi di login falliti
- Upload file sospetti
- Accessi non autorizzati
- Modifiche configurazioni
- Errori di sicurezza

### Alert Email
Notifiche automatiche per:
- Attacchi brute force
- Upload file malevoli
- Modifiche file critici
- Configurazioni non sicure

### Backup Configurazioni
- Esportazione configurazioni in JSON
- Importazione configurazioni da backup
- Preset predefiniti per diversi livelli di sicurezza

## Risoluzione Problemi

### Plugin non si attiva
1. Verifica compatibilità PHP (7.4+)
2. Controlla permessi file
3. Verifica log errori WordPress

### Errori dopo attivazione
1. Disabilita temporaneamente il plugin
2. Controlla conflitti con altri plugin
3. Verifica configurazioni wp-config.php

### Problemi di performance
1. Ottimizza configurazioni database
2. Abilita cache se disponibile
3. Monitora log di sicurezza

## Supporto e Contributi

Questo plugin è stato sviluppato specificamente per l'Università Bocconi seguendo le migliori pratiche di sicurezza OWASP.

Per supporto tecnico o segnalazione bug, contattare il team di sviluppo interno.

## Licenza

Questo plugin è proprietario dell'Università Bocconi e non può essere distribuito o modificato senza autorizzazione.

## Changelog

### Versione 1.0.0
- Implementazione completa OWASP ASVS 4.0
- Dashboard amministrativa
- Sistema di logging avanzato
- Configurazioni di sicurezza automatiche
- Monitoraggio minacce in tempo reale

---

**Sviluppato per Università Bocconi**  
*Sicurezza WordPress di livello enterprise*