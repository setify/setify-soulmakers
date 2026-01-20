/**
 * Soulmakers Frontend JavaScript
 *
 * @package Soulmakers
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * Soulmakers Frontend Modul
     */
    const SoulmakersFrontend = {

        /**
         * Konfiguration
         */
        config: {
            ajaxUrl: soulmakersFrontend.ajaxUrl || '',
            nonce: soulmakersFrontend.nonce || '',
            i18n: soulmakersFrontend.i18n || {}
        },

        /**
         * Initialisierung
         */
        init: function() {
            this.logSuccess();
            this.bindEvents();
            this.initSelect2();
        },

        /**
         * Erfolgsmeldung in Konsole ausgeben
         */
        logSuccess: function() {
            console.log(
                '%c Soulmakers Frontend erfolgreich geladen! ',
                'background: #2c3e50; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold;'
            );
            console.log('Version: ' + '1.0.0');
        },

        /**
         * Event-Handler binden
         */
        bindEvents: function() {
            // Hier können Event-Handler hinzugefügt werden
        },

        /**
         * Select2 initialisieren
         */
        initSelect2: function() {
            if (typeof $.fn.select2 !== 'undefined') {
                $('.soulmakers-select2').select2({
                    width: '100%',
                    placeholder: 'Bitte auswählen...'
                });
            }
        },

        /**
         * AJAX-Request Helper
         *
         * @param {string} action - WordPress AJAX Action
         * @param {object} data - Zusätzliche Daten
         * @returns {Promise}
         */
        ajaxRequest: function(action, data = {}) {
            return $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: action,
                    nonce: this.config.nonce,
                    ...data
                }
            });
        },

        /**
         * SweetAlert2 Erfolgsmeldung
         *
         * @param {string} title - Titel
         * @param {string} text - Nachricht
         */
        showSuccess: function(title, text) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: title || this.config.i18n.success,
                    text: text,
                    confirmButtonColor: '#2c3e50'
                });
            }
        },

        /**
         * SweetAlert2 Fehlermeldung
         *
         * @param {string} title - Titel
         * @param {string} text - Nachricht
         */
        showError: function(title, text) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: title || this.config.i18n.error,
                    text: text,
                    confirmButtonColor: '#e74c3c'
                });
            }
        },

        /**
         * SweetAlert2 Bestätigungsdialog
         *
         * @param {string} title - Titel
         * @param {string} text - Nachricht
         * @returns {Promise}
         */
        showConfirm: function(title, text) {
            if (typeof Swal !== 'undefined') {
                return Swal.fire({
                    icon: 'question',
                    title: title,
                    text: text,
                    showCancelButton: true,
                    confirmButtonText: this.config.i18n.confirm,
                    cancelButtonText: this.config.i18n.cancel,
                    confirmButtonColor: '#2c3e50',
                    cancelButtonColor: '#95a5a6'
                });
            }
            return Promise.resolve({ isConfirmed: confirm(text) });
        }
    };

    // Bei DOM Ready initialisieren
    $(document).ready(function() {
        SoulmakersFrontend.init();
    });

    // Global verfügbar machen
    window.SoulmakersFrontend = SoulmakersFrontend;

})(jQuery);
