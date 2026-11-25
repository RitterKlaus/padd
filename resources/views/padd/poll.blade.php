<script>
    (function () {
        const url = @json($versionUrl);
        const version = @json($version);

        setInterval(async function () {
            try {
                const response = await fetch(url, {
                    cache: 'no-store',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok && (await response.json()).version !== version) {
                    window.location.reload();
                }
            } catch {
                // The PADD keeps its content while the server cannot be reached.
            }
        }, 5000);
    })();
</script>
