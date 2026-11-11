{{-- window.gdSwap(url, id, { beforeSwap }): ambil halaman url, lalu tukar elemen #id dengan versi barunya tanpa
     reload. Selama memuat: bilah tipis di atas layar dan elemen lama diredupkan. Permintaan sebelumnya dibatalkan
     bila ada yang baru (error AbortError, abaikan saja). Gagal lainnya dilempar agar pemanggil bisa reload biasa. --}}
<script>
    window.gdSwap = window.gdSwap || (function () {
        var inflight = null;
        var bar = null;

        function progress(on) {
            if (!bar) {
                bar = document.createElement('div');
                bar.setAttribute('aria-hidden', 'true');
                bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0;background:#9BDB4D;z-index:9999;opacity:0;transition:width .4s ease,opacity .3s ease';
                document.body.appendChild(bar);
            }
            if (on) {
                bar.style.opacity = '1';
                bar.style.width = '70%';
            } else {
                bar.style.width = '100%';
                setTimeout(function () { bar.style.opacity = '0'; bar.style.width = '0'; }, 250);
            }
        }

        function dim(el, on) {
            el.style.transition = 'opacity .15s ease';
            el.style.opacity = on ? '0.55' : '';
            el.style.pointerEvents = on ? 'none' : '';
            el.setAttribute('aria-busy', on ? 'true' : 'false');
        }

        return async function (url, id, options) {
            options = options || {};
            if (inflight) inflight.abort();
            var controller = inflight = new AbortController();
            var current = document.getElementById(id);
            var dimTarget = (current && options.dim && options.dim(current)) || current;

            progress(true);
            if (dimTarget) dim(dimTarget, true);
            try {
                var res = await fetch(url, { signal: controller.signal, credentials: 'same-origin', headers: { Accept: 'text/html' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                var doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                var next = doc.getElementById(id);
                current = document.getElementById(id);
                if (!next || !current) throw new Error('#' + id + ' tidak ditemukan');

                if (options.beforeSwap) options.beforeSwap(current);
                current.replaceWith(next);
                document.title = doc.title;
                return next;
            } finally {
                if (inflight === controller) {
                    inflight = null;
                    progress(false);
                    var still = document.getElementById(id);
                    if (still && dimTarget && still.contains(dimTarget)) dim(dimTarget, false);
                }
            }
        };
    })();
</script>
