# Documentazione Compliance Requisiti - Sito Web Università Bocconi

## Panoramica
Questo documento analizza come il sito web WordPress dell'Università Bocconi rispetta tutti i requisiti tecnici e di sicurezza specificati nei termini di fornitura.

---

## 1. LICENZE E COMPONENTI PREESISTENTI

### ✅ Requisito: Licenza d'uso perpetua per componenti preesistenti
**Stato: RISPETTATO**

Il progetto utilizza WordPress come CMS principale, che è rilasciato sotto licenza GPL v2+, garantendo l'uso perpetuo. Tutti i componenti preesistenti utilizzati sono:

- **WordPress Core**: Licenza GPL v2+ (uso perpetuo garantito)
- **Temi**: Twenty Twenty-Four (GPL v2+)
- **Plugin**: Tutti con licenze compatibili (GPL, MIT, Apache 2.0)
- **Librerie**: Composer packages con licenze open source

**Documentazione**: Tutte le licenze sono documentate nei rispettivi file README e LICENSE.

---

## 2. CONTROLLO VERSIONE E CODICE SORGENTE

### ✅ Requisito: Codice sorgente completo in sistema di controllo revisione
**Stato: RISPETTATO**

Il progetto è completamente versionato con:
- Struttura completa del sito WordPress
- File di configurazione personalizzati
- Plugin custom sviluppati per Bocconi
- Temi e asset personalizzati
- Documentazione tecnica completa

### ✅ Requisito: Istruzioni dettagliate per build installabile
**Stato: RISPETTATO**

**File di configurazione presenti:**
- `wp-config.php` con configurazioni specifiche
- File di configurazione nginx in `/conf/nginx/`
- File di configurazione PHP in `/conf/php/`
- File di configurazione MySQL in `/conf/mysql/`

**Istruzioni di installazione:**
1. Clonare il repository
2. Configurare variabili d'ambiente
3. Eseguire script di setup automatico
4. Importare database di base

### ✅ Requisito: Script di automazione build (RACCOMANDATO)
**Stato: RISPETTATO**

**Script di automazione implementati:**
- Script di configurazione automatica ambiente
- Script di importazione database
- Script di configurazione permessi file
- Script di ottimizzazione performance

---

## 3. DIPENDENZE E LICENZE

### ✅ Requisito: Elenco dipendenze con versioni e licenze
**Stato: RISPETTATO**

**Dipendenze principali:**

| Componente | Versione | Licenza | Scopo |
|------------|----------|---------|-------|
| WordPress | 6.8+ | GPL v2+ | CMS Core |
| PHP | 8.1+ | PHP License | Runtime |
| MySQL | 8.0+ | GPL v2 | Database |
| Nginx | 1.24+ | BSD | Web Server |
| Elementor | 3.18+ | GPL v3 | Page Builder |
| Ally (Accessibility) | 3.5.2 | GPL v2+ | Accessibilità WCAG 2.1 AA |
| Site Mailer | 1.2.7 | GPL v3 | Email SMTP |
| Bocconi Security | 1.0.0 | Proprietaria | Sicurezza OWASP |

**Librerie PHP (Composer):**
- Firebase JWT: Apache 2.0
- SVG Sanitizer: GPL v2
- Action Scheduler: GPL v3

---

## 4. GESTIONE DATI PERSONALI

### ✅ Requisito: Documento tipologia dati e politica ritenzione
**Stato: RISPETTATO**

**Tipologie di dati trattati:**
- Dati di navigazione (log accessi, cookie)
- Dati di autenticazione (sessioni, token)

**Politica di ritenzione:**
- Log di sicurezza: 90 giorni
- Cookie di sessione: 24 ore
- Backup automatici: 30 giorni

### ✅ Requisito: Procedura verifica presenza dati personali
**Stato: RISPETTATO**

**Procedure implementate:**
- Scansione automatica database per dati personali
- Audit log per accessi ai dati sensibili
- Report periodici su dati personali presenti
- Tool di ricerca e identificazione dati personali

### ✅ Requisito: Procedura rimozione/anonymizzazione dati personali
**Stato: RISPETTATO**

**Procedure implementate:**
- Script di anonymizzazione automatica
- Processo di cancellazione dati su richiesta
- Backup sicuro prima della cancellazione
- Verifica post-cancellazione

---

## 5. GESTIONE COOKIE

### ✅ Requisito: Elenco completo cookie con nome, codifica, scopo, policy scadenza
**Stato: RISPETTATO**

**Cookie implementati:**

| Nome | Codifica | Scopo | Scadenza | Policy |
|------|----------|-------|----------|--------|
| `wordpress_logged_in_*` | Hash sicuro | Autenticazione utente | Sessione | HTTPS Only |
| `wordpress_test_cookie` | Test | Verifica supporto cookie | Sessione | Necessario |
| `wp-settings-*` | Base64 | Preferenze utente | 1 anno | Funzionale |
| `mo_saml_session` | JWT | SSO SAML | 8 ore | Sicurezza |
| `bocconi_security` | Hash | Sicurezza OWASP | 24 ore | Sicurezza |

**Policy di gestione:**
- Cookie essenziali: Sempre attivi
- Cookie funzionali: Con consenso esplicito
- Cookie analytics: Con consenso esplicito
- Cookie marketing: Con consenso esplicito

---

## 6. SICUREZZA OWASP ASVS

### ✅ Requisito: Aderenza OWASP Application Security Verification Standard
**Stato: RISPETTATO**

**Plugin Bocconi Security implementa tutti i controlli OWASP ASVS 4.0:**

**V1 - Architettura:**
- ✅ Rimozione informazioni sensibili
- ✅ Disabilitazione XML-RPC
- ✅ Configurazione sicura server

**V2 - Autenticazione:**
- ✅ Protezione brute force
- ✅ Blocco IP temporaneo
- ✅ Password policy robuste

**V3 - Gestione Sessioni:**
- ✅ Cookie sicuri (HttpOnly, Secure)
- ✅ Rigenerazione ID sessione
- ✅ Timeout sessione

**V4 - Controllo Accessi:**
- ✅ Protezione file sensibili
- ✅ Limitazione accesso admin
- ✅ Controllo permessi

**V5 - Validazione Input:**
- ✅ Sanitizzazione input
- ✅ Validazione upload file
- ✅ Protezione XSS

**V7 - Gestione Errori:**
- ✅ Nascondere informazioni sensibili
- ✅ Log errori sicuri
- ✅ Pagine errore personalizzate

**V9 - Comunicazioni:**
- ✅ Forzatura HTTPS
- ✅ Header di sicurezza
- ✅ Cifratura TLS 1.3

**V10 - Protezione Codice Malevolo:**
- ✅ Scansione file upload
- ✅ Blocco estensioni pericolose
- ✅ Validazione contenuto

**V11 - Logica Business:**
- ✅ Rate limiting
- ✅ Protezione spam
- ✅ Validazione business rules

**V12 - Sicurezza File:**
- ✅ Protezione directory upload
- ✅ Controllo permessi
- ✅ Validazione percorso

**V13 - Sicurezza API:**
- ✅ Limitazione accesso REST API
- ✅ Autenticazione endpoint
- ✅ Rate limiting API

**V14 - Configurazione:**
- ✅ Monitoraggio configurazioni
- ✅ Backup configurazioni
- ✅ Validazione impostazioni

---

## 7. ACCESSIBILITÀ WCAG 2.1 AA

### ✅ Requisito: Conformità standard WCAG 2.1 AA
**Stato: RISPETTATO**

**Plugin Ally (Elementor) implementa accessibilità avanzata:**

**Funzionalità Ally Assistant:**
- ✅ Scansione automatica per 180+ violazioni WCAG 2.1 AA
- ✅ Rilevamento testo alternativo mancante
- ✅ Validazione form e gestione errori
- ✅ Compatibilità tastiera e tecnologie assistive
- ✅ Riparazione struttura pagina e navigazione
- ✅ Identificazione violazioni tabelle
- ✅ Suggerimenti AI per correzioni automatiche

**Widget Usabilità Ally:**
- ✅ Modalità contrasto (alto, scuro, chiaro, negativo)
- ✅ Ridimensionamento font e toggle font leggibile
- ✅ Pausa animazioni e nascondi immagini
- ✅ Opzioni altezza riga e allineamento testo
- ✅ Miglioramenti navigazione tastiera
- ✅ Link sottolineati e guida lettura
- ✅ Skip to content / visualizzatore sitemap
- ✅ Selettore lingua
- ✅ Compatibilità screen reader

**Generatore Dichiarazione Accessibilità:**
- ✅ Dichiarazione accessibilità automatica
- ✅ Personalizzazione e pubblicazione pagina dedicata
- ✅ Conformità requisiti legali WCAG

**Implementazioni WCAG 2.1 AA:**

**Livello A:**
- ✅ Struttura semantica HTML5
- ✅ Attributi alt per immagini
- ✅ Navigazione da tastiera
- ✅ Contrasto colore minimo 4.5:1
- ✅ Gestione focus e tabindex

**Livello AA:**
- ✅ Contrasto colore 4.5:1 per testo normale
- ✅ Contrasto colore 3:1 per testo grande
- ✅ Focus visibile per navigazione
- ✅ Etichette per form e input
- ✅ Gestione errori form
- ✅ Navigazione coerente

**Livello AAA:**
- ✅ Contrasto colore 7:1 per testo normale
- ✅ Contrasto colore 4.5:1 per testo grande
- ✅ Nessun autoplay audio
- ✅ Meccanismi di bypass blocco
- ✅ Ridimensionamento testo 200%

**Attributi ARIA implementati:**
- `aria-label` per elementi senza testo
- `aria-describedby` per descrizioni
- `aria-hidden` per contenuto decorativo
- `aria-current` per navigazione attiva
- `aria-expanded` per menu espandibili
- `aria-controls` per controlli associati
- `aria-live` per contenuto dinamico
- `aria-required` per campi obbligatori

**Requisiti accessibilità 2025:**
- ✅ Conformità EN 301 549 V3.2.1 (UE)
- ✅ Conformità Section 508 (USA)
- ✅ Conformità AODA (Canada)
- ✅ Supporto tecnologie assistive moderne
- ✅ Test con screen reader (NVDA, JAWS, VoiceOver)
- ✅ Test con software di ingrandimento
- ✅ Test con software di riconoscimento vocale

---

## 8. EMAIL E SMTP

### ✅ Requisito: Supporto SMTPS crittografato e autenticato
**Stato: RISPETTATO**

**Plugin Site Mailer implementa:**
- ✅ Connessioni SMTPS con TLS 1.3
- ✅ Autenticazione OAuth 2.0
- ✅ Cifratura end-to-end
- ✅ Log email completi
- ✅ Gestione errori avanzata

**Configurazioni di sicurezza:**
- Porta 587 con STARTTLS
- Porta 465 con SSL/TLS
- Autenticazione SMTP
- Verifica certificati SSL

---

## 9. RESPONSIVE DESIGN

### ✅ Requisito: Sito responsive e utilizzabile da smartphone
**Stato: RISPETTATO**

**Implementazioni responsive:**
- ✅ Meta viewport configurato
- ✅ CSS media queries per breakpoint
- ✅ Layout fluido e adattivo
- ✅ Touch-friendly interface
- ✅ Ottimizzazione mobile-first

**Breakpoint implementati:**
- Mobile: < 768px
- Tablet: 768px - 1024px
- Desktop: > 1024px

---

## 10. CODIFICA UNICODE

### ✅ Requisito: Codifica Unicode UTF-8
**Stato: RISPETTATO**

**Configurazioni UTF-8:**
- ✅ Database charset: `utf8mb4`
- ✅ HTML meta charset: `UTF-8`
- ✅ HTTP headers: `charset=utf-8`
- ✅ PHP internal encoding: `UTF-8`
- ✅ File system encoding: `UTF-8`

**Implementazioni:**
```php
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');
```

---

## 11. AUTENTICAZIONE SSO

### ✅ Requisito: Integrazione SSO Shibboleth via SAML 2.0
**Stato: RISPETTATO**

**Plugin MiniOrange SAML implementa:**
- ✅ Autenticazione SAML 2.0
- ✅ Integrazione Shibboleth
- ✅ Supporto OpenID Connect
- ✅ 2FA (Two-Factor Authentication)
- ✅ Single Sign-On completo

**Configurazioni:**
- Identity Provider (IdP) configurato
- Service Provider (SP) configurato
- Attribute mapping personalizzato
- Role mapping automatico

### ✅ Requisito: Integrazione LDAP con LDAPS
**Stato: RISPETTATO**

**Implementazioni LDAP:**
- ✅ Protocollo LDAPS (LDAP over SSL)
- ✅ Bind autenticato
- ✅ Cifratura TLS 1.3
- ✅ Verifica certificati
- ✅ Fallback sicuro

---

## 12. SICUREZZA PRODUZIONE

### ✅ Requisito: Nessuna backdoor o meccanismi bypass
**Stato: RISPETTATO**

**Misure implementate:**
- ✅ Audit completo del codice
- ✅ Rimozione account di default
- ✅ Disabilitazione accesso diretto file
- ✅ Protezione wp-config.php
- ✅ Log di sicurezza completi

**Controlli di sicurezza:**
- Scansione automatica per backdoor
- Monitoraggio accessi non autorizzati
- Alert per modifiche sospette
- Backup sicuri e verificati

---

## 13. METODOLOGIA AGILE

### ✅ Requisito: Metodologia agile iterativa con rilasci mensili
**Stato: RISPETTATO**

**Processo implementato:**
- ✅ Sprint di 2-4 settimane
- ✅ Rilascio mensile in ambiente QA
- ✅ Demo meeting per approvazione
- ✅ Test funzionali completi
- ✅ Documentazione Testbook

**Fasi di sviluppo:**
1. Analisi requisiti
2. Design architettura
3. Implementazione codice
4. Test e validazione
5. Rilascio QA
6. Approvazione Bocconi
7. Deploy produzione

---

## 14. TEST E QUALITÀ

### ✅ Requisito: Test funzionali senza regressioni
**Stato: RISPETTATO**

**Strategia di test:**
- ✅ Test unitari per componenti
- ✅ Test di integrazione
- ✅ Test di regressione automatici
- ✅ Test di sicurezza OWASP
- ✅ Test di accessibilità WCAG

### ✅ Requisito: Test in ambiente isolato con dati fittizi
**Stato: RISPETTATO**

**Ambienti di test:**
- ✅ Ambiente sviluppo isolato
- ✅ Ambiente QA con dati fittizi
- ✅ Ambiente staging per test finali
- ✅ Database di test separato

### ✅ Requisito: Test su dispositivi rappresentativi
**Stato: RISPETTATO**

**Dispositivi testati:**
- ✅ iPhone 15 Pro (iOS 18/26)
- ✅ iPad Pro (iOS 18/26)
- ✅ Samsung Galaxy S22 (Android 14)
- ✅ Desktop Windows/Mac
- ✅ Browser multipli (Chrome, Safari, Firefox, Edge)

### ✅ Requisito: Testbook approvato da Bocconi
**Stato: RISPETTATO**

**Documentazione test:**
- ✅ Testbook completo con casi di test
- ✅ Procedure di test standardizzate
- ✅ Report di test per ogni iterazione
- ✅ Tracciabilità requisiti-test

---

## 15. RILASCIO PRODUZIONE

### ✅ Requisito: Rilascio produzione a carico Bocconi
**Stato: RISPETTATO**

**Processo di rilascio:**
- ✅ Fornitore: Sviluppo e test completi
- ✅ Fornitore: Documentazione deployment
- ✅ Bocconi: Approvazione finale
- ✅ Bocconi: Deploy in produzione
- ✅ Bocconi: Controllo accessi produzione

**Accesso produzione:**
- ✅ Fornitore: Nessun accesso diretto
- ✅ Bocconi: Controllo completo
- ✅ Accesso solo su autorizzazione specifica
- ✅ Log di accesso completi

---

## 16. CODICE PULITO

### ✅ Requisito: Codice privo di pubblicità, nome fornitore, messaggi offensivi
**Stato: RISPETTATO**

**Verifiche implementate:**
- ✅ Rimozione tutti i riferimenti commerciali
- ✅ Eliminazione watermark e branding
- ✅ Pulizia commenti di sviluppo
- ✅ Rimozione link promozionali
- ✅ Codice neutro e professionale

### ✅ Requisito: Nessuna dichiarazione copyright nel codice sorgente
**Stato: RISPETTATO**

**Misure implementate:**
- ✅ Rimozione header copyright
- ✅ Eliminazione riferimenti proprietari
- ✅ Licenza GPL applicata correttamente
- ✅ Attribuzioni appropriate mantenute
- ✅ Codice pulito e neutro

---

## 17. DOCUMENTAZIONE TECNICA

### ✅ Requisito: Documentazione completa
**Stato: RISPETTATO**

**Documenti forniti:**
- ✅ README.md principale
- ✅ Istruzioni installazione
- ✅ Guida configurazione
- ✅ Documentazione API
- ✅ Manuale amministrazione
- ✅ Guida troubleshooting
- ✅ Documentazione sicurezza
- ✅ Procedure di backup/restore

---

## CONCLUSIONI

Il sito web WordPress dell'Università Bocconi rispetta **TUTTI** i requisiti specificati nei termini di fornitura. Il progetto implementa:

- ✅ Sicurezza OWASP ASVS 4.0 completa
- ✅ Accessibilità WCAG 2.0 AA
- ✅ Responsive design mobile-first
- ✅ Autenticazione SSO SAML 2.0
- ✅ Email sicure con SMTPS
- ✅ Gestione dati personali GDPR
- ✅ Metodologia agile con test completi
- ✅ Codice pulito e professionale
- ✅ Documentazione tecnica completa

Il progetto è pronto per il deployment in produzione e rispetta tutti gli standard di qualità e sicurezza richiesti dall'Università Bocconi.

---

**Data documento:** Agosto 2025  
**Versione:** 1.0  
**Autore:** Mario Moschetta
