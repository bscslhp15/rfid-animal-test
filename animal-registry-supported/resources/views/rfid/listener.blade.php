<div
    id="rfid-global-status"
    data-lookup-url="{{ $lookupUrl }}"
    class="fixed bottom-4 right-4 z-50 max-w-xs rounded-lg bg-slate-900 px-4 py-3 text-sm text-white shadow-lg"
    role="status"
    aria-live="polite"
>
    RFID ready · scan a tag
</div>
<script>
    (() => {
        const status = document.getElementById('rfid-global-status');
        const lookupUrl = status.dataset.lookupUrl;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let buffer = '';
        let scanTarget = null;
        let scanStartedAt = 0;
        let lastKeyAt = 0;
        let submitTimer;
        let scanning = false;

        const showStatus = (message) => {
            status.textContent = message;
        };

        const lookupTag = async () => {
            const rfidTagId = buffer;
            const target = scanTarget;
            buffer = '';
            scanTarget = null;

            if (scanning || !/^[A-Za-z0-9._:-]{8,64}$/.test(rfidTagId)) {
                return;
            }

            if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement) {
                if (target.value.endsWith(rfidTagId)) {
                    target.setRangeText('', target.value.length - rfidTagId.length, target.value.length, 'end');
                    target.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }

            scanning = true;
            showStatus('RFID detected · looking up animal…');

            try {
                const response = await fetch(lookupUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: new URLSearchParams({ rfid_tag_id: rfidTagId }),
                });
                const result = await response.json();

                if (response.ok && result.redirect) {
                    showStatus('Animal found · opening record…');
                    window.location.assign(result.redirect);
                    return;
                }

                showStatus(result.message || 'RFID lookup failed. Please try again.');
            } catch {
                showStatus('Could not contact the system. Check your connection and try again.');
            } finally {
                scanning = false;
            }
        };

        document.addEventListener('keydown', (event) => {
            if (event.ctrlKey || event.altKey || event.metaKey) {
                return;
            }

            const target = event.target;
            if (
                target instanceof Element
                && target.closest('select, [contenteditable="true"], [data-rfid-assignment]')
            ) {
                buffer = '';
                scanTarget = null;
                window.clearTimeout(submitTimer);
                return;
            }

            const passwordField = target instanceof HTMLInputElement && target.type === 'password';
            if (passwordField) {
                buffer = '';
                scanTarget = null;
                window.clearTimeout(submitTimer);
                return;
            }

            if (event.key === 'Tab' && buffer.length >= 8) {
                event.preventDefault();
                window.clearTimeout(submitTimer);
                lookupTag();

                return;
            }

            if (event.key === 'Enter') {
                if (buffer.length >= 8 && performance.now() - scanStartedAt <= 10000) {
                    event.preventDefault();
                    window.clearTimeout(submitTimer);
                    lookupTag();
                }

                return;
            }

            if (event.key.length !== 1 || !/^[A-Za-z0-9._:-]$/.test(event.key)) {
                return;
            }

            const now = performance.now();
            if (now - lastKeyAt > 1000) {
                buffer = '';
                scanTarget = event.target;
                scanStartedAt = now;
            } else if (buffer === '') {
                scanTarget = event.target;
                scanStartedAt = now;
            }

            lastKeyAt = now;
            buffer += event.key;
            window.clearTimeout(submitTimer);

            if (buffer.length > 64) {
                buffer = '';
                return;
            }

            if (buffer.length >= 8) {
                submitTimer = window.setTimeout(() => {
                    if (performance.now() - scanStartedAt <= 10000) {
                        lookupTag();
                    } else {
                        buffer = '';
                        scanTarget = null;
                    }
                }, 1200);
            }
        }, true);
    })();
</script>
