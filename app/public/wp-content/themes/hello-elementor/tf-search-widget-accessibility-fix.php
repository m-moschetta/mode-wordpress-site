<?php
/**
 * Correzioni di accessibilità per il widget di ricerca ThemesFlat
 * 
 * Questo file aggiunge gli attributi di accessibilità mancanti al widget di ricerca
 * e assicura che sia racchiuso in landmark semantici appropriati.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Filtra l'output del widget di ricerca ThemesFlat per migliorare l'accessibilità
 */
function fix_tf_search_widget_accessibility( $content ) {
	// Verifica se il contenuto contiene il widget di ricerca ThemesFlat
	if ( strpos( $content, 'tf-widget-search' ) !== false ) {
		
		// Aggiungi aria-label al pulsante di ricerca principale
		$content = str_replace(
			'<button class="tf-icon-search">',
			'<button class="tf-icon-search" aria-label="Apri ricerca" type="button">',
			$content
		);
		
		// Aggiungi aria-label al pulsante di chiusura del modal
		$content = str_replace(
			'<button class="tf-close-modal">',
			'<button class="tf-close-modal" aria-label="Chiudi ricerca" type="button">',
			$content
		);
		
		// Aggiungi aria-label al pulsante di submit
		$content = str_replace(
			'<button type="submit" class="search-submit">',
			'<button type="submit" class="search-submit" aria-label="Invia ricerca">',
			$content
		);
		
		// Aggiungi attributi di accessibilità al modal
		$content = str_replace(
			'<div class="tf-modal-search-panel">',
			'<div class="tf-modal-search-panel" role="dialog" aria-modal="true" aria-labelledby="search-modal-title">',
			$content
		);
		
		// Aggiungi un titolo nascosto per il modal
		$content = str_replace(
			'<div class="search-panel">',
			'<div class="search-panel"><h2 id="search-modal-title" class="sr-only">Ricerca nel sito</h2>',
			$content
		);
		
		// Racchiudi il widget in un elemento nav con aria-label
		$content = str_replace(
			'<div class="tf-widget-search',
			'<nav aria-label="Ricerca sito" role="search"><div class="tf-widget-search',
			$content
		);
		
		// Chiudi l'elemento nav
		$content = str_replace(
			'</div>\t\t\t\t</div>',
			'</div>\t\t\t\t</div></nav>',
			$content
		);
		
		// Aggiungi attributi di accessibilità al campo di input
		$content = str_replace(
			'<input type="search" class="search-field"',
			'<input type="search" class="search-field" aria-label="Campo di ricerca"',
			$content
		);
	}
	
	return $content;
}

/**
 * Applica le correzioni di accessibilità all'output del widget
 */
function apply_tf_search_accessibility_fixes() {
	// Filtra l'output finale della pagina per correggere il widget di ricerca
	add_filter( 'the_content', 'fix_tf_search_widget_accessibility' );
	add_filter( 'widget_text', 'fix_tf_search_widget_accessibility' );
	
	// Filtra anche l'output di Elementor se presente
	if ( function_exists( 'elementor_theme_do_location' ) ) {
		add_filter( 'elementor/frontend/the_content', 'fix_tf_search_widget_accessibility' );
	}
}

/**
 * Inizializza le correzioni di accessibilità
 */
function init_tf_search_accessibility_fixes() {
	// Verifica se il plugin ThemesFlat è attivo
	if ( class_exists( 'TFSearch_Widget_Free' ) ) {
		apply_tf_search_accessibility_fixes();
	}
}

// Inizializza le correzioni dopo che i plugin sono caricati
add_action( 'plugins_loaded', 'init_tf_search_accessibility_fixes' );

/**
 * Aggiungi JavaScript per migliorare l'accessibilità del widget di ricerca
 */
function tf_search_accessibility_script() {
	?>
	<script>
	(function() {
		// Gestione dell'accessibilità da tastiera per il modal di ricerca
		document.addEventListener('DOMContentLoaded', function() {
			var searchButtons = document.querySelectorAll('.tf-icon-search');
			var closeButtons = document.querySelectorAll('.tf-close-modal');
			var modals = document.querySelectorAll('.tf-modal-search-panel');
			
			// Gestione apertura modal con Enter/Space
			searchButtons.forEach(function(button) {
				button.addEventListener('keydown', function(e) {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						button.click();
					}
				});
			});
			
			// Gestione chiusura modal con Escape
			modals.forEach(function(modal) {
				modal.addEventListener('keydown', function(e) {
					if (e.key === 'Escape') {
						var closeButton = modal.querySelector('.tf-close-modal');
						if (closeButton) {
							closeButton.click();
						}
					}
				});
				
				// Trap focus nel modal quando è aperto
				var observer = new MutationObserver(function(mutations) {
					mutations.forEach(function(mutation) {
						if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
							if (modal.classList.contains('show')) {
								// Modal aperto - sposta focus al campo di ricerca
								var searchField = modal.querySelector('.search-field');
								if (searchField) {
									searchField.focus();
								}
							}
						}
					});
				});
				
				observer.observe(modal, {
					attributes: true,
					attributeFilter: ['class']
				});
			});
			
			// Gestione chiusura modal con Enter/Space sui pulsanti di chiusura
			closeButtons.forEach(function(button) {
				button.addEventListener('keydown', function(e) {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						button.click();
					}
				});
			});
		});
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'tf_search_accessibility_script' );

/**
 * Aggiungi stili CSS inline per correzioni immediate
 */
function tf_search_accessibility_inline_styles() {
	?>
	<style>
	/* Assicura che il testo per screen reader sia nascosto visivamente */
	.tf-widget-search .sr-only {
		position: absolute !important;
		width: 1px !important;
		height: 1px !important;
		padding: 0 !important;
		margin: -1px !important;
		overflow: hidden !important;
		clip: rect(0, 0, 0, 0) !important;
		white-space: nowrap !important;
		border: 0 !important;
	}
	
	/* Assicura che i pulsanti abbiano un focus visibile */
	.tf-widget-search button:focus {
		outline: 2px solid #0073aa !important;
		outline-offset: 2px !important;
	}
	</style>
	<?php
}
add_action( 'wp_head', 'tf_search_accessibility_inline_styles' );