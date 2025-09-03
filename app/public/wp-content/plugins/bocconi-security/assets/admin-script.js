/**
 * Bocconi Security Plugin - Admin JavaScript
 * Script avanzato per l'interfaccia amministrativa del plugin di sicurezza
 */

(function($) {
    'use strict';

    // Variabili globali per il tracking dello stato
    let securityData = {
        score: 0,
        threats: [],
        stats: {},
        isScanning: false,
        lastUpdate: null
    };

    let updateInterval;
    let notificationTimeout;
    let chartInstances = {};

    // Oggetto principale del plugin con funzionalità avanzate
    const BocconiSecurity = {
        init: function() {
            this.bindEvents();
            this.initTabs();
            this.initDashboard();
            this.initPresets();
            this.initOWASP();
            this.initCharts();
            this.initRealTimeUpdates();
            this.initAdvancedFeatures();
            this.initKeyboardShortcuts();
            this.initProgressTracking();
        },

        initAdvancedFeatures: function() {
            this.initNotificationSystem();
            this.initDataExport();
            this.initBulkActions();
            this.initSearchFilters();
            this.initTooltips();
        },

        initKeyboardShortcuts: function() {
            $(document).on('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    switch(e.key) {
                        case 'r':
                            e.preventDefault();
                            BocconiSecurity.refreshDashboard();
                            break;
                        case 's':
                            e.preventDefault();
                            BocconiSecurity.quickScan();
                            break;
                    }
                }
            });
        },

        initProgressTracking: function() {
            // Sistema di tracking per operazioni lunghe
            this.progressQueue = [];
            this.activeOperations = new Set();
        },

        bindEvents: function() {
            // Gestione form
            $(document).on('submit', '.bocconi-security-form', this.handleFormSubmit);
            
            // Gestione preset di sicurezza
            $(document).on('click', '.security-preset', this.handlePresetClick);
            
            // Gestione scansioni
            $(document).on('click', '.scan-button', this.handleScanClick);
            
            // Gestione pulizia logs
            $(document).on('click', '.clear-logs-button', this.handleClearLogs);
            
            // Gestione esportazione configurazione
            $(document).on('click', '.export-config-button', this.handleExportConfig);
            
            // Gestione importazione configurazione
            $(document).on('change', '.import-config-input', this.handleImportConfig);
            
            // Gestione test email
            $(document).on('click', '.test-email-button', this.handleTestEmail);
            
            // Gestione aggiornamento automatico
            $(document).on('change', '.auto-update-checkbox', this.handleAutoUpdate);
        },

        initTabs: function() {
            // Gestione tab
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                const targetTab = $(this).attr('href');
                
                // Rimuovi classe active da tutti i tab
                $('.nav-tab').removeClass('nav-tab-active');
                $('.tab-content').removeClass('active').hide();
                
                // Aggiungi classe active al tab corrente
                $(this).addClass('nav-tab-active');
                $(targetTab).addClass('active fade-in').show();
                
                // Salva tab attivo in localStorage
                localStorage.setItem('bocconi_security_active_tab', targetTab);
            });
            
            // Ripristina tab attivo
            const activeTab = localStorage.getItem('bocconi_security_active_tab') || '#dashboard';
            $('.nav-tab[href="' + activeTab + '"]').trigger('click');
        },

        initDashboard: function() {
            this.updateSecurityScore();
            this.loadRecentThreats();
            this.updateSystemStatus();
            this.loadSecurityStats();
        },

        updateSecurityScore: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_get_score',
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const score = response.data.score;
                        const level = response.data.level;
                        
                        $('.security-score').text(score + '%');
                        $('.security-score-container')
                            .removeClass('score-high score-medium score-low')
                            .addClass('score-' + level);
                        
                        // Anima la barra di progresso
                        setTimeout(function() {
                            $('.security-score-progress::after').css('width', score + '%');
                        }, 500);
                    }
                }
            });
        },

        loadRecentThreats: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_get_recent_threats',
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const threats = response.data;
                        let html = '';
                        
                        if (threats.length === 0) {
                            html = '<div class="no-threats">Nessuna minaccia rilevata di recente</div>';
                        } else {
                            threats.forEach(function(threat) {
                                html += `
                                    <div class="threat-item">
                                        <span class="threat-type ${threat.level}">${threat.level}</span>
                                        <div class="threat-details">
                                            <strong>${threat.type}</strong><br>
                                            <small>${threat.description} - ${threat.time}</small>
                                        </div>
                                    </div>
                                `;
                            });
                        }
                        
                        $('.recent-threats').html(html);
                    }
                }
            });
        },

        updateSystemStatus: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_get_system_status',
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const status = response.data;
                        let html = '';
                        
                        Object.keys(status).forEach(function(key) {
                            const item = status[key];
                            html += `
                                <div class="status-indicator status-${item.status}">
                                    <span class="status-dot"></span>
                                    <div class="status-texts">
                                        <span class="status-label">${item.label}</span>
                                        ${item.message ? `<small class="status-message">${item.message}</small>` : ''}
                                    </div>
                                </div>
                            `;
                        });
                        
                        $('.system-status').html(html);
                    }
                }
            });
        },

        loadSecurityStats: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_get_stats',
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const stats = response.data;
                        
                        $('.stat-item.threats .stat-number').text(stats.threats || 0);
                        $('.stat-item.blocked .stat-number').text(stats.blocked || 0);
                        $('.stat-item.scans .stat-number').text(stats.scans || 0);
                        
                        // Anima i numeri
                        $('.stat-number').each(function() {
                            const $this = $(this);
                            const target = parseInt($this.text());
                            let current = 0;
                            
                            const increment = target / 50;
                            const timer = setInterval(function() {
                                current += increment;
                                if (current >= target) {
                                    current = target;
                                    clearInterval(timer);
                                }
                                $this.text(Math.floor(current));
                            }, 20);
                        });
                    }
                }
            });
        },

        initPresets: function() {
            // Carica preset attivo
            this.loadActivePreset();
        },

        loadActivePreset: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_get_active_preset',
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.security-preset').removeClass('active');
                        $('.security-preset[data-preset="' + response.data + '"]').addClass('active');
                    }
                }
            });
        },

        handlePresetClick: function(e) {
            e.preventDefault();
            
            const $preset = $(this);
            const presetName = $preset.data('preset');
            
            if ($preset.hasClass('active') || $preset.hasClass('loading')) {
                return;
            }
            
            $preset.addClass('loading');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_apply_preset',
                    preset: presetName,
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    $preset.removeClass('loading');
                    
                    if (response.success) {
                        $('.security-preset').removeClass('active');
                        $preset.addClass('active');
                        
                        BocconiSecurity.showNotice('Preset di sicurezza applicato con successo!', 'success');
                        BocconiSecurity.updateSecurityScore();
                    } else {
                        BocconiSecurity.showNotice('Errore nell\'applicazione del preset: ' + response.data, 'error');
                    }
                },
                error: function() {
                    $preset.removeClass('loading');
                    BocconiSecurity.showNotice('Errore di connessione', 'error');
                }
            });
        },

        initOWASP: function() {
            // Gestione accordion OWASP
            $('.owasp-category-header').on('click', function() {
                const $header = $(this);
                const $content = $header.next('.owasp-category-content');
                const $icon = $header.find('.toggle-icon');
                
                $content.slideToggle(300);
                $icon.toggleClass('rotated');
            });
            
            // Gestione toggle regole OWASP
            $('.owasp-rule input[type="checkbox"]').on('change', function() {
                const $checkbox = $(this);
                const ruleId = $checkbox.data('rule-id');
                const enabled = $checkbox.is(':checked');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bocconi_security_toggle_owasp_rule',
                        rule_id: ruleId,
                        enabled: enabled,
                        nonce: bocconiSecurityAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            BocconiSecurity.updateSecurityScore();
                        } else {
                            $checkbox.prop('checked', !enabled);
                            BocconiSecurity.showNotice('Errore nell\'aggiornamento della regola', 'error');
                        }
                    }
                });
            });
        },

        initCharts: function() {
            // Inizializza grafici se Chart.js è disponibile
            if (typeof Chart !== 'undefined') {
                this.initThreatChart();
                this.initActivityChart();
            }
        },

        initThreatChart: function() {
            const ctx = document.getElementById('threatChart');
            if (!ctx) return;
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_get_threat_chart_data',
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: response.data,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }
                        });
                    }
                }
            });
        },

        initActivityChart: function() {
            const ctx = document.getElementById('activityChart');
            if (!ctx) return;
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_get_activity_chart_data',
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        new Chart(ctx, {
                            type: 'line',
                            data: response.data,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                }
            });
        },

        initRealTimeUpdates: function() {
            // Aggiorna dati ogni 30 secondi
            setInterval(function() {
                if ($('#dashboard').hasClass('active')) {
                    BocconiSecurity.updateSecurityScore();
                    BocconiSecurity.loadRecentThreats();
                    BocconiSecurity.updateSystemStatus();
                }
            }, 30000);
        },

        handleFormSubmit: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitButton = $form.find('input[type="submit"], button[type="submit"]');
            const originalText = $submitButton.val() || $submitButton.text();
            
            $submitButton.prop('disabled', true)
                        .val('Salvataggio...')
                        .text('Salvataggio...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&nonce=' + bocconiSecurityAdmin.nonce,
                success: function(response) {
                    if (response.success) {
                        BocconiSecurity.showNotice('Configurazione salvata con successo!', 'success');
                        BocconiSecurity.updateSecurityScore();
                    } else {
                        BocconiSecurity.showNotice('Errore nel salvataggio: ' + response.data, 'error');
                    }
                },
                error: function() {
                    BocconiSecurity.showNotice('Errore di connessione', 'error');
                },
                complete: function() {
                    $submitButton.prop('disabled', false)
                                .val(originalText)
                                .text(originalText);
                }
            });
        },

        handleScanClick: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const scanType = $button.data('scan-type') || 'full';
            
            $button.prop('disabled', true)
                   .html('<span class="loading-spinner"></span>Scansione in corso...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_run_scan',
                    scan_type: scanType,
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        BocconiSecurity.showNotice('Scansione completata!', 'success');
                        BocconiSecurity.updateSecurityScore();
                        BocconiSecurity.loadRecentThreats();
                    } else {
                        BocconiSecurity.showNotice('Errore durante la scansione: ' + response.data, 'error');
                    }
                },
                error: function() {
                    BocconiSecurity.showNotice('Errore di connessione', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false)
                           .html('Avvia Scansione');
                }
            });
        },

        handleClearLogs: function(e) {
            e.preventDefault();
            
            if (!confirm('Sei sicuro di voler cancellare tutti i log di sicurezza?')) {
                return;
            }
            
            const $button = $(this);
            
            $button.prop('disabled', true)
                   .text('Cancellazione...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_clear_logs',
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        BocconiSecurity.showNotice('Log cancellati con successo!', 'success');
                        location.reload();
                    } else {
                        BocconiSecurity.showNotice('Errore nella cancellazione: ' + response.data, 'error');
                    }
                },
                error: function() {
                    BocconiSecurity.showNotice('Errore di connessione', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false)
                           .text('Cancella Log');
                }
            });
        },

        handleExportConfig: function(e) {
            e.preventDefault();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_export_config',
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const dataStr = JSON.stringify(response.data, null, 2);
                        const dataBlob = new Blob([dataStr], {type: 'application/json'});
                        
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(dataBlob);
                        link.download = 'bocconi-security-config-' + new Date().toISOString().split('T')[0] + '.json';
                        link.click();
                        
                        BocconiSecurity.showNotice('Configurazione esportata con successo!', 'success');
                    } else {
                        BocconiSecurity.showNotice('Errore nell\'esportazione: ' + response.data, 'error');
                    }
                },
                error: function() {
                    BocconiSecurity.showNotice('Errore di connessione', 'error');
                }
            });
        },

        handleImportConfig: function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const config = JSON.parse(e.target.result);
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'bocconi_security_import_config',
                            config: JSON.stringify(config),
                            nonce: bocconiSecurityAdmin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                BocconiSecurity.showNotice('Configurazione importata con successo!', 'success');
                                location.reload();
                            } else {
                                BocconiSecurity.showNotice('Errore nell\'importazione: ' + response.data, 'error');
                            }
                        },
                        error: function() {
                            BocconiSecurity.showNotice('Errore di connessione', 'error');
                        }
                    });
                } catch (error) {
                    BocconiSecurity.showNotice('File di configurazione non valido', 'error');
                }
            };
            reader.readAsText(file);
        },

        handleTestEmail: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const email = $('#notification_email').val();
            
            if (!email) {
                BocconiSecurity.showNotice('Inserisci un indirizzo email valido', 'error');
                return;
            }
            
            $button.prop('disabled', true)
                   .text('Invio in corso...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_test_email',
                    email: email,
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        BocconiSecurity.showNotice('Email di test inviata con successo!', 'success');
                    } else {
                        BocconiSecurity.showNotice('Errore nell\'invio: ' + response.data, 'error');
                    }
                },
                error: function() {
                    BocconiSecurity.showNotice('Errore di connessione', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false)
                           .text('Invia Test');
                }
            });
        },

        handleAutoUpdate: function() {
            const enabled = $(this).is(':checked');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bocconi_security_toggle_auto_update',
                    enabled: enabled,
                    nonce: bocconiSecurityAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const message = enabled ? 'Aggiornamenti automatici attivati' : 'Aggiornamenti automatici disattivati';
                        BocconiSecurity.showNotice(message, 'success');
                    } else {
                        BocconiSecurity.showNotice('Errore nell\'aggiornamento delle impostazioni', 'error');
                    }
                }
            });
        },

        showNotice: function(message, type) {
            type = type || 'info';
            
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('.bocconi-security-admin').prepend($notice);
            
            // Auto-dismiss dopo 5 secondi
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Gestione dismiss manuale
            $notice.on('click', '.notice-dismiss', function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            });
        },

        // Utility functions
        formatBytes: function(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        },

        formatDate: function(date) {
            return new Date(date).toLocaleString('it-IT');
        },

        debounce: function(func, wait, immediate) {
            let timeout;
            return function() {
                const context = this, args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    };

    // Inizializza quando il DOM è pronto
    $(document).ready(function() {
        BocconiSecurity.init();
    });

    // Esponi l'oggetto globalmente per debug
    window.BocconiSecurity = BocconiSecurity;

})(jQuery);

// Aggiungi animazioni CSS personalizzate
const style = document.createElement('style');
style.textContent = `
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .loading-spinner {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #0073aa;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 5px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .security-preset.loading {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .toggle-icon {
        transition: transform 0.3s ease;
    }
    
    .toggle-icon.rotated {
        transform: rotate(180deg);
    }
`;
document.head.appendChild(style);