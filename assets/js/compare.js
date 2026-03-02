(function($){
    'use strict';

    function setCookieCompare(arr) {
        try {
            var v = JSON.stringify(arr || []);
            var days = 365;
            var d = new Date();
            d.setTime(d.getTime() + (days*24*60*60*1000));
            document.cookie = 'jaspi_compare=' + encodeURIComponent(v) + '; path=/; expires=' + d.toUTCString();
        } catch(e) { }
    }

    function getCookieCompare(){
        var match = document.cookie.match(new RegExp('(^| )jaspi_compare=([^;]+)'));
        if (!match) return [];
        try { return JSON.parse(decodeURIComponent(match[2])); } catch(e) { return []; }
    }

    function updateCompareCount(count){
        var $c = $('#jaspi-compare-count');
        if (!$c.length) return;
        if (count && parseInt(count,10) > 0) {
            $c.text(count).show();
        } else {
            $c.text('').hide();
        }
    }

    $(document).ready(function(){
        var initial = [];
        if (window.jaspi_compare && Array.isArray(window.jaspi_compare.compare) && window.jaspi_compare.compare.length) {
            initial = window.jaspi_compare.compare.map(function(v){ return parseInt(v,10); });
        } else {
            initial = getCookieCompare();
        }
        if (window.jaspi_compare && typeof window.jaspi_compare.count !== 'undefined') {
            updateCompareCount(window.jaspi_compare.count);
        }

        // mark existing buttons
        $('.jaspi-compare-btn').each(function(){
            var pid = parseInt($(this).data('product-id'),10) || 0;
            if (pid && initial.indexOf(pid)!==-1) {
                $(this).addClass('is-compare').attr('aria-pressed','true');
            }
        });

        // toggle compare
        $(document).on('click', '.jaspi-compare-btn', function(e){
            e.preventDefault();
            var $btn = $(this);
            var pid = $btn.data('product-id');
            if (!pid) return;
            $btn.addClass('is-loading');
            $.post(window.jaspi_compare.ajax_url, {
                action: 'jaspi_toggle_compare',
                product_id: pid,
                nonce: window.jaspi_compare.nonce
            }, function(resp){
                $btn.removeClass('is-loading');
                if (!resp) return;
                if (!resp.success) {
                    if (resp.data && resp.data.message) {
                        alert(resp.data.message + (resp.data.limit ? (' (Límite: ' + resp.data.limit + ')') : ''));
                    }
                    return;
                }
                var data = resp.data;
                var added = data.action === 'added';
                if (added) {
                    $btn.addClass('is-compare').attr('aria-pressed','true');
                } else {
                    $btn.removeClass('is-compare').attr('aria-pressed','false');
                }
                setCookieCompare(data.compare);
                updateCompareCount(data.count || 0);
                // tiny toast
                var msg = added ? 'Añadido a comparar' : 'Eliminado de comparación';
                var $t = $('<div class="jaspi-fav-toast" role="status" aria-live="polite"></div>').text(msg + (added ? ' - ' : ''));
                if (added && window.jaspi_compare && window.jaspi_compare.compare_page) {
                    var $a = $('<a class="jaspi-fav-toast-link"></a>').attr('href', window.jaspi_compare.compare_page).text('Ver comparación');
                    $t.append($a);
                }
                $('body').append($t);
                setTimeout(function(){ $t.addClass('visible'); },20);
                setTimeout(function(){ $t.removeClass('visible'); $t.fadeOut(200,function(){ $t.remove(); }); },3000);
            }, 'json');
        });

        // remove from compare in compare view
        $(document).on('click', '.jaspi-remove-compare', function(e){
            e.preventDefault();
            var pid = $(this).data('product-id');
            if (!pid) return;
            $.post(window.jaspi_compare.ajax_url, {
                action: 'jaspi_toggle_compare',
                product_id: pid,
                nonce: window.jaspi_compare.nonce
            }, function(resp){
                if (!resp || !resp.success) return;
                setCookieCompare(resp.data.compare);
                updateCompareCount(resp.data.count || 0);
                // remove column - simple approach: reload page for robust layout
                location.reload();
            }, 'json');
        });

        // clear all compare
        $(document).on('click', '.jaspi-clear-compare', function(e){
            e.preventDefault();
            if (!confirm('¿Vaciar comparación?')) return;
            var $btn = $(this);
            $btn.prop('disabled', true).addClass('is-loading');
            $.post(window.jaspi_compare.ajax_url, { action: 'jaspi_clear_compare', nonce: window.jaspi_compare.nonce }, function(resp){
                $btn.prop('disabled', false).removeClass('is-loading');
                if (!resp || !resp.success) return;
                setCookieCompare([]);
                updateCompareCount(0);
                location.reload();
            }, 'json');
        });

    });

})(jQuery);
