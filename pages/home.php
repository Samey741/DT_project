<h2>Vitajte v distribuovanej databáze</h2>
<p>Vyberte možnosť z menu na ľavej strane.</p>
<script>
    function checkServerStatus() {
        var xhttp = new XMLHttpRequest();
        console.log("KOKOT")
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                console.log(this.responseText);
            }
        };
        xhttp.open("GET", "check_and_sync.php", true);
        xhttp.send();
    }

    // Spustenie každých 20 sekúnd
    setInterval(checkServerStatus, 20000);
</script>

<script>
    function runQueue() {
        fetch('/dt/pages/process-queue.php')
            .then(response => response.text())
            .then(data => console.log('Queue processed:', data))
            .catch(err => console.error('Queue error:', err));
    }

    // Run every 20 seconds
    setInterval(runQueue, 5000);
</script>
