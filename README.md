# Queue Manager for LaveroAdmin

Queue and background tasks management extension for LaveroAdmin.

## Installation

```bash
composer require laveroadmin/queue-manager
```

Import menu and permissions:

```bash
php artisan admin:import queue-manager
```

## Features

- **Queue Status & Overview**: Monitor driver, pending jobs, failed jobs, and batch statuses.
- **Failed Jobs Management**: Inspect job payloads, full exception stack-traces, retry failed jobs individually or in bulk, and purge unrecoverable tasks.
- **Worker Control**: Send restart signals to background worker processes (`queue:restart`).
- **Batch Processing**: Track execution progress and error states for job batches.

## License

The MIT License (MIT).
