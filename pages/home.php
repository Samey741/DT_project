<h2>Vitajte v distribuovanej databáze</h2>
<p>Vyberte možnosť z menu na ľavej strane.</p>

<button class="queue-btn" id="queueBtn" aria-live="polite">Spustiť replikácie</button>
<span id="queueStatus" class="queue-status" aria-hidden="true"></span>

<script>
    let isProcessing = false;
    let pendingManual = false;
    let lastResult = null;

    const BACKGROUND_INTERVAL_MS = 20000;
    const backgroundIntervalId = setInterval(() => runQueue(false), BACKGROUND_INTERVAL_MS);

    async function runQueue(isManual = true) {
        if (isProcessing) {
            if (isManual) {
                pendingManual = true;
                showStatus('Začlenené do fronty…', 'info');
                flashButton();
            }
            // If background triggered while running just skip silently
            return;
        }

        isProcessing = true;
        pendingManual = false; // reset pending because we're executing now
        const btn = document.getElementById('queueBtn');
        const status = document.getElementById('queueStatus');

        btn.disabled = true;
        showStatus('Spracováva sa…', 'working');

        try {
            const res = await fetch('/dt/pages/process-queue.php', { cache: 'no-store' });
            let data;
            try {
                data = await res.json();
            } catch (e) {
                const text = await res.text();
                console.warn('Non-JSON response from process-queue.php:', text);
                data = { processed: null, failed: null, errors: text };
            }

            lastResult = data;
            console.log('Processed:', data.processed);
            console.log('Failed:', data.failed);
            console.log('Errors:', data.errors);

            showStatus('✅ Dokončené', 'success');

        } catch (err) {
            console.error('Queue error:', err);
            showStatus('❌ Chyba', 'error');
        } finally {
            isProcessing = false;
            btn.disabled = false;

            // If user requested a manual run during processing, run once more immediately
            if (pendingManual) {
                pendingManual = false;
                setTimeout(() => runQueue(true), 50);
            } else {
                setTimeout(() => {
                    // only clear if there isn't a pending manual or currently processing
                    if (!isProcessing && !pendingManual) showStatus('', '');
                }, 2500);
            }
        }
    }

    document.getElementById('queueBtn').addEventListener('click', () => runQueue(true));

    function showStatus(text, type) {
        const status = document.getElementById('queueStatus');
        status.textContent = text;

        status.classList.remove('status-working','status-success','status-error','status-info');
        if (type === 'working') status.classList.add('status-working');
        if (type === 'success') status.classList.add('status-success');
        if (type === 'error') status.classList.add('status-error');
        if (type === 'info') status.classList.add('status-info');
    }

    function flashButton() {
        const btn = document.getElementById('queueBtn');
        btn.animate([
            { transform: 'translateY(0)' },
            { transform: 'translateY(-4px)' },
            { transform: 'translateY(0)' }
        ], {
            duration: 250,
            easing: 'ease-out'
        });
    }
</script>
