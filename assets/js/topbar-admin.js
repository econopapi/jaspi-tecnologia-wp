(function () {
    'use strict';

    function byId(id) {
        return document.getElementById(id);
    }

    function buildLink(label, url) {
        if (!label || !url) {
            return null;
        }

        var anchor = document.createElement('a');
        anchor.href = url;
        anchor.textContent = label;
        return anchor;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var welcomeField = byId('jaspi_topbar_welcome_text');
        var enabledField = byId('jaspi_topbar_highlight_enabled');
        var highlightTextField = byId('jaspi_topbar_highlight_text');
        var highlightUrlField = byId('jaspi_topbar_highlight_url');
        var linkOneLabelField = byId('jaspi_topbar_link_1_label');
        var linkOneUrlField = byId('jaspi_topbar_link_1_url');
        var linkTwoLabelField = byId('jaspi_topbar_link_2_label');
        var linkTwoUrlField = byId('jaspi_topbar_link_2_url');
        var previewMessage = byId('jaspi-topbar-preview-message');
        var previewHighlight = byId('jaspi-topbar-preview-highlight');
        var previewHighlightLink = byId('jaspi-topbar-preview-highlight-link');
        var previewLinks = byId('jaspi-topbar-preview-links');
        var previewNote = byId('jaspi-topbar-preview-note');

        if (!welcomeField ||
            !enabledField ||
            !highlightTextField ||
            !highlightUrlField ||
            !previewMessage ||
            !previewHighlight ||
            !previewHighlightLink ||
            !previewLinks ||
            !previewNote) {
            return;
        }

        function updatePreview() {
            var isEnabled = enabledField.checked;
            var welcomeMessage = welcomeField.value.trim();
            var highlightMessage = highlightTextField.value.trim();
            var highlightUrl = highlightUrlField.value.trim();
            var links = [
                buildLink(linkOneLabelField.value.trim(), linkOneUrlField.value.trim()),
                buildLink(linkTwoLabelField.value.trim(), linkTwoUrlField.value.trim())
            ].filter(Boolean);

            previewMessage.textContent = welcomeMessage;

            previewHighlightLink.textContent = highlightMessage;
            previewHighlight.hidden = !isEnabled || !highlightMessage;

            if (highlightUrl) {
                previewHighlightLink.href = highlightUrl;
            } else {
                previewHighlightLink.removeAttribute('href');
            }

            previewLinks.innerHTML = '';
            links.forEach(function (link) {
                previewLinks.appendChild(link);
            });

            previewNote.hidden = links.length > 0;
            if (!links.length && window.jaspiTopbarAdmin && jaspiTopbarAdmin.noLinksText) {
                previewNote.textContent = jaspiTopbarAdmin.noLinksText;
            }
        }

        [
            welcomeField,
            enabledField,
            highlightTextField,
            highlightUrlField,
            linkOneLabelField,
            linkOneUrlField,
            linkTwoLabelField,
            linkTwoUrlField
        ].forEach(function (field) {
            field.addEventListener('input', updatePreview);
            field.addEventListener('change', updatePreview);
        });

        updatePreview();
    });
})();