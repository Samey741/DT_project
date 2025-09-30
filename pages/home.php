<h2>Vitajte v distribuovanej databáze</h2>
<p>Vyberte možnosť z menu na ľavej strane.</p>
<!--TODO   create a worker on server start up which will call this logic,
    TODO   currently user has to have the home page open for this script to run (it doesnt run in the background properly)-->
<script>
//  TODO logging show the ID of the entry in replication_queue table, maybe adjust it so its easier to see what went wrong
    function runQueue() {
        fetch('/dt/pages/process-queue.php')
            .then(res => res.json())
            .then(data => {
                console.log("Processed:", data.processed);
                console.log("Failed:", data.failed);
                console.log("Errors:", data.errors);
            })
            .catch(async err => {
                const raw = await fetch('/dt/pages/process-queue.php').then(r => r.text());
                console.error("Queue error:", err, "Raw output:", raw);
            });
    }
    //TODO better interval
    setInterval(runQueue, 1000); //1000 = 1 second
</script>
