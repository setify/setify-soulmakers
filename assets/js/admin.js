/**
 * Soulmakers Admin JavaScript
 *
 * @package Soulmakers
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * Soulmakers Admin Modul
     */
    const SoulmakersAdmin = {

        /**
         * Konfiguration
         */
        config: {
            ajaxUrl: soulmakersAdmin.ajaxUrl || '',
            nonce: soulmakersAdmin.nonce || '',
            i18n: soulmakersAdmin.i18n || {}
        },

        /**
         * Initialisierung
         */
        init: function() {
            console.log('Soulmakers Admin initialisiert');
            this.bindEvents();
            this.initSelect2();
        },

        /**
         * Event-Handler binden
         */
        bindEvents: function() {
            // Hier können Admin-Event-Handler hinzugefügt werden
        },

        /**
         * Select2 initialisieren
         */
        initSelect2: function() {
            if (typeof $.fn.select2 !== 'undefined') {
                $('.soulmakers-admin .soulmakers-select2').select2({
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
                    confirmButtonColor: '#2271b1'
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
                    confirmButtonColor: '#d63638'
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
                    confirmButtonColor: '#2271b1',
                    cancelButtonColor: '#787c82'
                });
            }
            return Promise.resolve({ isConfirmed: confirm(text) });
        }
    };

    // Bei DOM Ready initialisieren
    $(document).ready(function() {
        SoulmakersAdmin.init();
    });

    // Global verfügbar machen
    window.SoulmakersAdmin = SoulmakersAdmin;

})(jQuery);
