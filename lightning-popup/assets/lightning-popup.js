/**
 * Lightning Popup — central reusable component
 * Open / close + click-outside + Escape handling, extracted from hurricane.js.
 *
 * Public globals (kept for backward compatibility with existing callers):
 *   window.snefuruOpenLightningPopup()
 *   window.snefuruCloseLightningPopup()
 *
 * Localized data (set via wp_localize_script):
 *   window.snefuruLightningPopup = { ajaxurl, nonce, post_id }
 */

function snefuruOpenLightningPopup() {
    var popup = jQuery('#snefuru-lightning-popup');
    if (popup.length) {
        popup.show().fadeIn(300);
        jQuery('body').addClass('snefuru-popup-open');
    }
}

function snefuruCloseLightningPopup() {
    jQuery('#snefuru-lightning-popup').fadeOut(300);
    jQuery('body').removeClass('snefuru-popup-open');
}

window.snefuruOpenLightningPopup = snefuruOpenLightningPopup;
window.snefuruCloseLightningPopup = snefuruCloseLightningPopup;

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Open on button click (delegated so dynamically-added buttons work).
        $(document).off('click.lightningpopup-open')
                   .on('click.lightningpopup-open', '.snefuru-lightning-popup-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            snefuruOpenLightningPopup();
        });

        // Close button.
        $(document).off('click.lightningpopup-close')
                   .on('click.lightningpopup-close', '#snefuru-lightning-popup .snefuru-popup-close', function (e) {
            e.preventDefault();
            snefuruCloseLightningPopup();
        });

        // Close on overlay backdrop click — but ignore if drag started inside the container.
        var isInteractingWithPopup = false;

        $(document).off('mousedown.lightningpopup-container')
                   .on('mousedown.lightningpopup-container', '#snefuru-lightning-popup .snefuru-popup-container', function (e) {
            isInteractingWithPopup = true;
            e.stopPropagation();
        });

        $(document).off('mouseup.lightningpopup-container')
                   .on('mouseup.lightningpopup-container', '#snefuru-lightning-popup .snefuru-popup-container', function (e) {
            e.stopPropagation();
        });

        $(document).off('mousedown.lightningpopup-overlay')
                   .on('mousedown.lightningpopup-overlay', '#snefuru-lightning-popup.snefuru-popup-overlay', function (e) {
            if (e.target === this) {
                isInteractingWithPopup = false;
            }
        });

        $(document).off('mouseup.lightningpopup-overlay')
                   .on('mouseup.lightningpopup-overlay', '#snefuru-lightning-popup.snefuru-popup-overlay', function (e) {
            if (!isInteractingWithPopup && e.target === this) {
                snefuruCloseLightningPopup();
            }
            setTimeout(function () { isInteractingWithPopup = false; }, 100);
        });

        // Escape closes only the Lightning popup (other popups handle their own Esc).
        $(document).off('keydown.lightningpopup')
                   .on('keydown.lightningpopup', function (e) {
            if (e.keyCode === 27 && $('#snefuru-lightning-popup').is(':visible')) {
                snefuruCloseLightningPopup();
            }
        });
    });
}(jQuery));
