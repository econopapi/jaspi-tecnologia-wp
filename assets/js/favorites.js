(function($){
    'use strict';

    function setCookieFavorites(arr) {
        try {
            var v = JSON.stringify(arr || []);
            var days = 365;
            var d = new Date();
            d.setTime(d.getTime() + (days*24*60*60*1000));
            document.cookie = 'jaspi_favs=' + encodeURIComponent(v) + '; path=/; expires=' + d.toUTCString();
        } catch(e) { /* ignore */ }
    }

    function getCookieFavorites(){
        var match = document.cookie.match(new RegExp('(^| )jaspi_favs=([^;]+)'));
        if (!match) return [];
        try { return JSON.parse(decodeURIComponent(match[2])); } catch(e) { return []; }
    }

    function updateHeaderCount(count){
        var $c = $('#jaspi-fav-count');
        if (!$c.length) return;
        if (count && parseInt(count,10) > 0) {
            $c.text(count).show();
        } else {
            $c.text('').hide();
        }
    }

    $(document).ready(function(){
        // initialize count and mark initial favorite buttons
        var initialFavs = [];
        if (window.jaspi_favs && typeof window.jaspi_favs.count !== 'undefined') {
            updateHeaderCount(window.jaspi_favs.count);
        }
        if (window.jaspi_favs && Array.isArray(window.jaspi_favs.favorites) && window.jaspi_favs.favorites.length) {
            initialFavs = window.jaspi_favs.favorites.map(function(v){ return parseInt(v,10); });
        } else {
            initialFavs = getCookieFavorites();
        }

        // mark buttons on page based on favorites
        $('.jaspi-fav-btn').each(function(){
            var pid = parseInt($(this).data('product-id'),10) || 0;
            if (pid && initialFavs.indexOf(pid) !== -1) {
                $(this).addClass('is-fav').attr('aria-pressed','true');
            }
        });

        // toast helper
        function showToast(message, isLink) {
            var $t = $('<div class="jaspi-fav-toast" role="status" aria-live="polite"></div>');
            // Build content: message + ' - ' + link (if requested)
            if (isLink && window.jaspi_favs && window.jaspi_favs.favorites_page) {
                var $a = $('<a class="jaspi-fav-toast-link"></a>').attr('href', window.jaspi_favs.favorites_page).text('Ver lista');
                // use a separator hyphen with spaces
                $t.append(document.createTextNode(message + ' - '));
                $t.append($a);
            } else {
                $t.text(message);
            }
            $('body').append($t);
            setTimeout(function(){ $t.addClass('visible'); }, 20);
            setTimeout(function(){ $t.removeClass('visible'); $t.fadeOut(200, function(){ $t.remove(); }); }, 3000);
        }

        function updateClearButtonVisibility() {
            var $tbody = $('.jaspi-favorites-table tbody');
            var $clear = $('.jaspi-favorites-actions');
            if (!$clear.length) return;
            if ($tbody.length && $tbody.find('tr').length > 0) {
                $clear.show();
            } else {
                $clear.hide();
            }
        }

        // initial visibility
        updateClearButtonVisibility();

        // Toggle favorite button (product loop and single)
        $(document).on('click', '.jaspi-fav-btn', function(e){
            e.preventDefault();
            var $btn = $(this);
            var productId = $btn.data('product-id');
            if (!productId) return;

            // show loading state
            $btn.addClass('is-loading');

            $.post(window.jaspi_favs.ajax_url, {
                action: 'jaspi_toggle_favorite',
                product_id: productId,
                nonce: window.jaspi_favs.nonce
            }, function(resp){
                if (!resp || !resp.success) return;
                var data = resp.data;
                var count = data.count || 0;
                updateHeaderCount(count);
                // apply state
                var added = data.action === 'added';
                if (added) {
                    $btn.addClass('is-fav').attr('aria-pressed','true');
                    showToast('Añadido a favoritos', true);
                } else {
                    $btn.removeClass('is-fav').attr('aria-pressed','false');
                    showToast('Eliminado de favoritos', false);
                }
                // update cookie for guests
                setCookieFavorites(data.favorites);
                // remove loading
                $btn.removeClass('is-loading');
            }, 'json');
        });

        // Remove button inside favorites shortcode table
        $(document).on('click', '.jaspi-remove-fav', function(e){
            e.preventDefault();
            var $b = $(this);
            var pid = $b.data('product-id');
            if (!pid) return;
            $.post(window.jaspi_favs.ajax_url, {
                action: 'jaspi_toggle_favorite',
                product_id: pid,
                nonce: window.jaspi_favs.nonce
            }, function(resp){
                if (!resp || !resp.success) return;
                var data = resp.data;
                setCookieFavorites(data.favorites);
                updateHeaderCount(data.count || 0);
                // remove row
                $b.closest('tr').remove();
                updateClearButtonVisibility();
                showToast('Eliminado de favoritos', false);
            }, 'json');
        });

        // Clear all favorites button in shortcode view
        $(document).on('click', '.jaspi-clear-favs', function(e){
            e.preventDefault();
            if (!window.confirm('¿Vaciar toda la lista de favoritos?')) {
                return;
            }
            var $btn = $(this);
            $btn.prop('disabled', true).addClass('is-loading');
            $.post(window.jaspi_favs.ajax_url, {
                action: 'jaspi_clear_favorites',
                nonce: window.jaspi_favs.nonce
            }, function(resp){
                $btn.prop('disabled', false).removeClass('is-loading');
                if (!resp || !resp.success) return;
                // clear cookie, update header and remove table rows
                setCookieFavorites([]);
                updateHeaderCount(0);
                $('.jaspi-favorites-table tbody').empty();
                $('.jaspi-favorites-list').append('<p class="jaspi-favorites-empty">' + 'No tienes favoritos aún.' + '</p>');
                updateClearButtonVisibility();
                showToast('Lista de favoritos vaciada', false);
            }, 'json');
        });

        // Ensure cookie present if not logged
        if (!getCookieFavorites().length && !(window.jaspi_favs && window.jaspi_favs.count>0)) {
            // nothing
        }
    });

})(jQuery);
