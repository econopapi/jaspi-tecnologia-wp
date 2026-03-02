(function () {
    'use strict';

    function el(selector) {
        return document.querySelector(selector);
    }

    var form = el('.jaspi-footer-subscribe-form');
    var messageEl = el('#jaspi-footer-subscribe-message');

    if (!form) {
        return;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var input = form.querySelector('input[type="email"]');
        if (!input) return;

        var email = input.value.trim();
        if (!email) {
            showMessage('Por favor ingresa tu correo.', 'error');
            return;
        }

        if (!validateEmail(email)) {
            showMessage('Correo inválido', 'error');
            return;
        }

        // disable
        var submit = form.querySelector('button[type="submit"]');
        if (submit) submit.disabled = true;

        var payload = new FormData();
        payload.append('action', 'jaspi_footer_subscribe');
        payload.append('nonce', ( window.jaspiFooterSubscribe && jaspiFooterSubscribe.nonce ) ? jaspiFooterSubscribe.nonce : '');
        payload.append('email', email);

        fetch( (window.jaspiFooterSubscribe && jaspiFooterSubscribe.ajax_url) ? jaspiFooterSubscribe.ajax_url : '/wp-admin/admin-ajax.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: payload
        }).then(function (res) {
            return res.json();
        }).then(function (json) {
            if (submit) submit.disabled = false;
            if (json && json.success) {
                showMessage((json.data && json.data.message) ? json.data.message : 'OK', 'success');
                input.value = '';
            } else {
                var msg = (json && json.data && json.data.message) ? json.data.message : 'Error';
                showMessage(msg, 'error');
            }
        }).catch(function (err) {
            if (submit) submit.disabled = false;
            showMessage('Error de red', 'error');
            // eslint-disable-next-line no-console
            console.error(err);
        });
    });

    function showMessage(text, type) {
        if (!messageEl) return;
        messageEl.textContent = text;
        messageEl.classList.remove('success', 'error');
        if (type === 'success') messageEl.classList.add('success');
        if (type === 'error') messageEl.classList.add('error');
    }

    function validateEmail(email) {
        // simple validation
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\\.,;:\s@\"]+\.)+[^<>()[\]\\.,;:\s@\"]{2,})$/i;
        return re.test(email);
    }
})();
