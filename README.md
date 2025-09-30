Distributed Database README
1. Configuration

Each node has its own config file in resources/config/:
config1.php, config2.php, config3.php → contain DB connection info for each node.

localConfig.php → tells the system which node this machine is.
It sets:

$localSignature = "1";     // which node am I?
$localConfig = [ ... ];    // connection settings for my local DB taken from config1-3 files

This way, the same code runs everywhere, but each machine knows which node it is.

2. Queue Table

On every node’s local database, there is a replication table.
This table is responsible for inserting the values into local and remote ntovar table.
The queue ensures retries if a node was offline.

3. Insert Flow

When a user adds an item:
The item is inserted into the local queue table immediately.
Three replication tasks are created in replication_queue (one per node).

4. Processing the Queue

The script pages/process-queue.php handles replication:

Connects to the local DB.

Fetches all entries from replication table.
For each entry:
Finds the correct target node config, tries to connect and insert the record into ntovar.

On success → removes the queue entry.
On failure → leaves it as failed, retries later.

5. Running the Processor

The processor runs periodically as script on the homepage (subject to change) every second.

🚀 Example Workflow

User on Node1 inserts a product.

Product is saved into Node1’s local replication table as 3 entries, one for each node.
Replication tasks are created in Node1’s replication_queue for node1, node2 and node3.
process-queue.php runs:
Tries to push data to NodeX → NodeX online → success, task removed.
Tries to push data to NodeX → NodeX offline → task marked as failed.

Later, NodeX comes online.
Next processor run → retries failed entry → success → task removed.