(function () {
    const root = document.getElementById('conteo-root');
    if (!root) return;

    // ================= Helpers =================
    function getCsrf() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const param = document.querySelector('meta[name="csrf-param"]')?.getAttribute('content') || '_csrf';
        return {
            token,
            param
        };
    }

    function buildBody(paramsObj) {
        const {
            token,
            param
        } = getCsrf();
        const body = new URLSearchParams();
        if (token) body.append(param, token);
        Object.entries(paramsObj || {}).forEach(([k, v]) => body.append(k, v));
        return body.toString();
    }

    function recargarTabla() {
        if (window.$ && $.pjax) {
            $.pjax.reload({
                container: '#pjax-container',
                async: false
            });
        }
    }

    function toggleResumenVisible(forceShow = false) {
        const box = document.getElementById('ocultarInput');
        if (!box) return;

        if (forceShow) {
            box.classList.remove('d-none');
            return;
        }

        const tu = document.getElementById('total_unidades')?.value ?? '';
        const ti = document.getElementById('total_items')?.value ?? '';
        const ue = document.getElementById('ultimo_ean')?.value ?? '';

        const nTu = parseInt(String(tu).replace(/[^\d-]/g, ''), 10) || 0;
        const nTi = parseInt(String(ti).replace(/[^\d-]/g, ''), 10) || 0;
        const hasUE = String(ue).trim() !== '';

        if (nTu > 0 || nTi > 0 || hasUE) box.classList.remove('d-none');
        else box.classList.add('d-none');
    }

    function actualizarContadores(data) {
        const tu = document.getElementById('total_unidades');
        const ti = document.getElementById('total_items');
        const ue = document.getElementById('ultimo_ean');

        if (tu && data.totalUnidades != null) tu.value = data.totalUnidades;
        if (ti && data.totalItems != null) ti.value = data.totalItems;
        if (ue && data.ultimo_ean != null) ue.value = data.ultimo_ean;

        toggleResumenVisible();
    }

    // ================= Inputs principales =================
    const inputEan = document.getElementById('codigo_barras');
    const inputCant = document.getElementById('cantidad_entrada');
    const btnCambiar = document.getElementById('btn-cambiar-cantidad');

    let cantidadHabilitadaUnaVez = false;

    function bloquearCantidad() {
        if (!inputCant) return;
        inputCant.value = 1;
        inputCant.disabled = true;
        cantidadHabilitadaUnaVez = false;
    }

    function habilitarCantidadUnaVez() {
        if (!inputCant) return;
        inputCant.disabled = false;
        inputCant.focus();
        inputCant.select();
        cantidadHabilitadaUnaVez = true;
    }

    // ================= Modal Admin =================
    (function initModalAdmin() {
        if (!btnCambiar) return;

        const modalEl = document.getElementById('modalClaveAdmin');
        const claveInput = document.getElementById('clave_admin_input');
        const claveErr = document.getElementById('clave_admin_error');
        const btnValidar = document.getElementById('btn-validar-clave');

        if (!modalEl || !btnValidar) return;

        const modal = new bootstrap.Modal(modalEl);

        function clearErr() {
            if (!claveErr) return;
            claveErr.textContent = '';
            claveErr.classList.add('d-none');
        }

        function showErr(msg) {
            if (claveErr) {
                claveErr.textContent = msg || 'Clave incorrecta';
                claveErr.classList.remove('d-none');
            } else {
                alert(msg || 'Clave incorrecta');
            }
        }

        btnCambiar.addEventListener('click', function () {
            clearErr();
            if (claveInput) claveInput.value = '';
            modal.show();
            setTimeout(() => claveInput?.focus(), 150);
        });

        btnValidar.addEventListener('click', function () {
            clearErr();
            const url = btnCambiar.dataset.validarUrl;
            const clave = (claveInput?.value || '').trim();

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: buildBody({
                        clave
                    }),
                })
                .then(async (r) => {
                    const data = await r.json().catch(() => null);
                    if (!r.ok) {
                        showErr((data && data.message) ? data.message : `Error HTTP ${r.status}`);
                        return null;
                    }
                    return data;
                })
                .then((data) => {
                    if (!data) return;
                    if (!data.success) {
                        showErr(data.message || 'Clave incorrecta');
                        return;
                    }
                    modal.hide();
                    habilitarCantidadUnaVez();
                })
                .catch(() => showErr('Error de comunicación'));
        });

        claveInput?.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter') {
                ev.preventDefault();
                btnValidar.click();
            }
        });
    })();

    // ================= Escaneo =================
    function postScan() {
        if (!inputEan) return;

        const url = inputEan.dataset.procesarUrl;
        const ean = inputEan.value.trim();
        const cantidad = inputCant ? parseInt(inputCant.value || '1', 10) : 1;

        if (!ean) return;

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: buildBody({
                    codigoBarras: ean,
                    cantidadEntrada: (Number.isFinite(cantidad) && cantidad > 0) ? cantidad : 1,
                }),
            })
            .then(async (r) => {
                const data = await r.json().catch(() => null);
                if (!r.ok) {
                    alert((data && data.message) ? data.message : `Error HTTP ${r.status}`);
                    return null;
                }
                return data;
            })
            .then((data) => {
                if (!data) return;
                if (!data.success) {
                    alert(data.message || 'Error');
                    return;
                }

                actualizarContadores(data);
                recargarTabla();

                if (cantidadHabilitadaUnaVez) bloquearCantidad();

                inputEan.value = '';
                inputEan.focus();
            })
            .catch(() => alert('Error de comunicación'));
    }

    inputEan?.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter') {
            ev.preventDefault();
            postScan();
        }
    });

    // ================= Estado inicial =================
    bloquearCantidad();
    toggleResumenVisible();

    // ================= Modal Eliminar SKU =================
    (function initEliminarSku() {
        const modalEl = document.getElementById('modalEliminarSku');
        if (!modalEl) return;

        const modal = new bootstrap.Modal(modalEl);

        const titulo = document.getElementById('eliminar_sku_titulo');
        const info = document.getElementById('eliminar_sku_info');
        const inputDel = document.getElementById('eliminar_sku_input');
        const maxSpan = document.getElementById('eliminar_sku_max');
        const err = document.getElementById('eliminar_sku_error');
        const btnConfirm = document.getElementById('btn-confirmar-eliminar-sku');

        let current = null;

        function clearErr() {
            if (!err) return;
            err.textContent = '';
            err.classList.add('d-none');
        }

        function showErr(msg) {
            if (err) {
                err.textContent = msg || 'No se pudo eliminar.';
                err.classList.remove('d-none');
            } else {
                alert(msg || 'No se pudo eliminar.');
            }
        }

        if (!window.$) {
            console.warn('jQuery no disponible: para PJAX se recomienda jQuery');
            return;
        }

        $(document).off('click.conteoTrash');
        $(document).on('click.conteoTrash', '.btn-eliminar-sku', function (e) {
            e.preventDefault();
            clearErr();

            const $btn = $(this);

            current = {
                url: $btn.data('url'),
                item: ($btn.data('item') || '').toString(),
                idColor: parseInt($btn.data('idcolor') || 0, 10) || 0,
                idTalla: parseInt($btn.data('idtalla') || 0, 10) || 0,
                color: ($btn.data('color') || 'NA').toString(),
                talla: ($btn.data('talla') || 'NA').toString(),
                max: parseInt($btn.data('max') || 0, 10) || 0,
            };

            if (titulo) titulo.textContent = `SKU: ${current.item}`;
            if (info) info.textContent = `Color: ${current.color} | Talla: ${current.talla}`;
            if (maxSpan) maxSpan.textContent = String(current.max);

            if (inputDel) {
                inputDel.value = 1;
                inputDel.max = String(current.max);
            }

            modal.show();
            setTimeout(() => inputDel?.focus(), 150);
        });

        btnConfirm?.addEventListener('click', function () {
            if (!current) return;
            clearErr();

            const cant = parseInt(inputDel?.value || '0', 10);
            if (!Number.isFinite(cant) || cant < 1) {
                showErr('Ingresa una cantidad válida.');
                return;
            }
            if (cant > current.max) {
                showErr(`No puedes eliminar ${cant}. Este SKU solo tiene ${current.max}.`);
                return;
            }

            fetch(current.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: buildBody({
                        item: current.item,
                        idColor: current.idColor,
                        idTalla: current.idTalla,
                        cantidadEliminar: cant,
                    }),
                })
                .then(async (r) => {
                    const data = await r.json().catch(() => null);
                    if (!r.ok) {
                        showErr((data && data.message) ? data.message : `Error HTTP ${r.status}`);
                        return null;
                    }
                    return data;
                })
                .then((data) => {
                    if (!data) return;
                    if (!data.success) {
                        showErr(data.message || 'No se pudo eliminar.');
                        return;
                    }

                    modal.hide();
                    actualizarContadores(data);
                    recargarTabla();
                })
                .catch(() => showErr('Error de comunicación'));
        });

        inputDel?.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter') {
                ev.preventDefault();
                btnConfirm?.click();
            }
        });
    })();

    // ================= Finalizar conteo =================
    const btnFinalizar = document.getElementById('btn-finalizar-conteo');
    if (btnFinalizar) {
        const modalEl = document.getElementById('modalFinalizarConteo');
        const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

        const btnConfirmar = document.getElementById('btn-confirmar-finalizar');
        const err = document.getElementById('finalizar_conteo_error');

        let finalizando = false; // ✅ anti doble click

        function clearErr() {
            if (!err) return;
            err.classList.add('d-none');
            err.textContent = '';
        }

        function showErr(msg) {
            if (err) {
                err.textContent = msg || 'No se pudo finalizar';
                err.classList.remove('d-none');
            } else {
                alert(msg || 'No se pudo finalizar');
            }
        }

        btnFinalizar.addEventListener('click', () => {
            if (!modal) return alert('Bootstrap modal no disponible');
            clearErr();
            finalizando = false;
            if (btnConfirmar) btnConfirmar.disabled = false;
            modal.show();
        });

        btnConfirmar?.addEventListener('click', () => {
            if (finalizando) return; // ✅ evita doble POST
            finalizando = true;
            btnConfirmar.disabled = true;

            clearErr();
            const url = btnFinalizar.dataset.url;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: buildBody({}),
                })
                .then(async (r) => {
                    const data = await r.json().catch(() => null);
                    if (!r.ok) {
                        finalizando = false;
                        btnConfirmar.disabled = false;
                        showErr((data && data.message) ? data.message : `Error HTTP ${r.status}`);
                        return null;
                    }
                    return data;
                })
                .then((data) => {
                    if (!data) return;

                    if (!data.success) {
                        finalizando = false;
                        btnConfirmar.disabled = false;
                        showErr(data.message || 'No se pudo finalizar');
                        return;
                    }

                    modal?.hide();

                    // ✅ Redirección sin volver atrás
                    const redirectUrl = btnFinalizar.dataset.redirect;
                    window.location.replace(
                        redirectUrl || '/grumalog-scan/web/index.php?r=grumascanmarcacion%2Fgrumascanconteo%2Fcreate'
                    );
                })
                .catch(() => {
                    finalizando = false;
                    btnConfirmar.disabled = false;
                    showErr('Error de comunicación');
                });
        });
    }
})();