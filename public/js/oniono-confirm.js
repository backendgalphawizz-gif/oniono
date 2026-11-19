(function () {
    'use strict';

    var modalEl, modal, titleEl, messageEl, iconEl, okBtn, cancelBtn, pendingResolve;

    var VARIANTS = {
        danger: {
            icon: 'bi-exclamation-triangle-fill',
            title: 'Confirm deletion',
            btnClass: 'btn-danger',
            btnLabel: 'Yes, proceed',
            iconClass: 'is-danger',
        },
        warning: {
            icon: 'bi-exclamation-circle-fill',
            title: 'Please confirm',
            btnClass: 'btn-warning text-dark',
            btnLabel: 'Yes, continue',
            iconClass: 'is-warning',
        },
        primary: {
            icon: 'bi-question-circle-fill',
            title: 'Are you sure?',
            btnClass: 'btn-brand',
            btnLabel: 'Confirm',
            iconClass: 'is-primary',
        },
    };

    function inferVariant(message) {
        var text = (message || '').toLowerCase();
        if (/delete|remove|reject|cancel/.test(text)) {
            return 'danger';
        }
        if (/migration|run all|cannot be undone/.test(text)) {
            return 'warning';
        }

        return 'primary';
    }

    function parseConfirmFromHandler(handler) {
        if (!handler || typeof handler !== 'string') {
            return null;
        }
        var match = handler.match(/confirm\s*\(\s*(['"`])([\s\S]*?)\1\s*\)/);

        return match ? match[2] : null;
    }

    function migrateLegacyConfirms() {
        document.querySelectorAll('form[onsubmit]').forEach(function (form) {
            var message = parseConfirmFromHandler(form.getAttribute('onsubmit'));
            if (!message) {
                return;
            }
            form.removeAttribute('onsubmit');
            if (!form.dataset.confirm) {
                form.dataset.confirm = message;
            }
            if (!form.dataset.confirmVariant) {
                form.dataset.confirmVariant = inferVariant(message);
            }
        });

        document.querySelectorAll('[onclick]').forEach(function (el) {
            var message = parseConfirmFromHandler(el.getAttribute('onclick'));
            if (!message) {
                return;
            }
            el.removeAttribute('onclick');
            if (!el.dataset.confirm) {
                el.dataset.confirm = message;
            }
            if (!el.dataset.confirmVariant) {
                el.dataset.confirmVariant = inferVariant(message);
            }
        });
    }

    function applyVariant(variantKey, options) {
        var variant = VARIANTS[variantKey] || VARIANTS.primary;
        titleEl.textContent = options.title || variant.title;
        messageEl.textContent = options.message || '';
        okBtn.textContent = options.confirmLabel || variant.btnLabel;
        okBtn.className = 'btn px-4 ' + (options.confirmClass || variant.btnClass);
        iconEl.className = 'oniono-confirm-icon mx-auto mb-3 ' + variant.iconClass;
        iconEl.innerHTML = '<i class="bi ' + (options.icon || variant.icon) + '"></i>';
    }

    function ask(message, options) {
        options = options || {};
        if (!modal) {
            return Promise.resolve(window.confirm(message));
        }

        var variantKey = options.variant || inferVariant(message);

        return new Promise(function (resolve) {
            pendingResolve = resolve;
            applyVariant(variantKey, {
                title: options.title,
                message: message,
                confirmLabel: options.confirmLabel,
                confirmClass: options.confirmClass,
                icon: options.icon,
            });
            modal.show();
        });
    }

    function resolvePending(result) {
        if (typeof pendingResolve === 'function') {
            var resolve = pendingResolve;
            pendingResolve = null;
            resolve(result);
        }
    }

    function initDom() {
        modalEl = document.getElementById('onionoConfirmModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        modal = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: 'static', keyboard: true });
        titleEl = document.getElementById('onionoConfirmTitle');
        messageEl = document.getElementById('onionoConfirmMessage');
        iconEl = document.getElementById('onionoConfirmIcon');
        okBtn = document.getElementById('onionoConfirmOk');
        cancelBtn = document.getElementById('onionoConfirmCancel');

        okBtn.addEventListener('click', function () {
            modal.hide();
            resolvePending(true);
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            if (pendingResolve) {
                resolvePending(false);
            }
        });

        document.addEventListener(
            'submit',
            function (event) {
                var form = event.target;
                if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
                    return;
                }
                if (form.dataset.onionoConfirmApproved === '1') {
                    delete form.dataset.onionoConfirmApproved;

                    return;
                }
                event.preventDefault();
                event.stopPropagation();

                var variant = form.dataset.confirmVariant || inferVariant(form.dataset.confirm);
                ask(form.dataset.confirm, {
                    variant: variant,
                    title: form.dataset.confirmTitle,
                    confirmLabel: form.dataset.confirmLabel,
                }).then(function (ok) {
                    if (ok) {
                        form.dataset.onionoConfirmApproved = '1';
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        } else {
                            form.submit();
                        }
                    }
                });
            },
            true
        );

        document.addEventListener(
            'click',
            function (event) {
                var trigger = event.target.closest('[data-confirm]');
                if (!trigger || trigger.tagName === 'FORM') {
                    return;
                }
                var form = trigger.closest('form');
                if (form && form.dataset.confirm) {
                    return;
                }

                if (trigger.tagName !== 'BUTTON' && trigger.tagName !== 'INPUT') {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                var variant = trigger.dataset.confirmVariant || inferVariant(trigger.dataset.confirm);
                ask(trigger.dataset.confirm, {
                    variant: variant,
                    title: trigger.dataset.confirmTitle,
                    confirmLabel: trigger.dataset.confirmLabel,
                }).then(function (ok) {
                    if (!ok || !form) {
                        return;
                    }
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit(trigger);
                    } else {
                        form.submit();
                    }
                });
            },
            true
        );
    }

    window.OnionoConfirm = { ask: ask };

    document.addEventListener('DOMContentLoaded', function () {
        initDom();
        migrateLegacyConfirms();
    });
})();
