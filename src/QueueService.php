<?php

namespace LaveroAdmin\Admin\QueueManager;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueueService
{
    /**
     * Get default queue driver.
     *
     * @return string
     */
    public function getDriver(): string
    {
        return config('queue.default', 'sync');
    }

    /**
     * Check if a table exists.
     *
     * @param string $table
     * @param string|null $connection
     * @return bool
     */
    protected function tableExists(string $table, ?string $connection = null): bool
    {
        try {
            return Schema::connection($connection)->hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get overall statistics about queues.
     *
     * @return array
     */
    public function getStats(): array
    {
        $failedTable = config('queue.failed.table', 'failed_jobs');
        $failedConn = config('queue.failed.database', config('database.default'));

        $failedCount = 0;
        if ($this->tableExists($failedTable, $failedConn)) {
            $failedCount = DB::connection($failedConn)->table($failedTable)->count();
        }

        $pendingTable = config('queue.connections.database.table', 'jobs');
        $pendingCount = 0;
        if ($this->tableExists($pendingTable)) {
            $pendingCount = DB::table($pendingTable)->count();
        }

        $batchesTable = config('queue.batching.table', 'job_batches');
        $batchesCount = 0;
        if ($this->tableExists($batchesTable)) {
            $batchesCount = DB::table($batchesTable)->count();
        }

        return [
            'driver'       => $this->getDriver(),
            'pending'      => $pendingCount,
            'failed'       => $failedCount,
            'batches'      => $batchesCount,
            'database_has' => $this->tableExists($pendingTable),
            'failed_has'   => $this->tableExists($failedTable, $failedConn),
        ];
    }

    /**
     * Get list of failed jobs.
     *
     * @param int $limit
     * @return array
     */
    public function getFailedJobs(int $limit = 50): array
    {
        $table = config('queue.failed.table', 'failed_jobs');
        $conn = config('queue.failed.database', config('database.default'));

        if (!$this->tableExists($table, $conn)) {
            return [];
        }

        $rows = DB::connection($conn)->table($table)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $list = [];
        foreach ($rows as $row) {
            $payload = json_decode($row->payload, true) ?: [];
            $displayName = $payload['displayName'] ?? ($payload['job'] ?? 'Unknown Job');

            $list[] = [
                'id'          => $row->id,
                'uuid'        => $row->uuid ?? (string)$row->id,
                'connection'  => $row->connection,
                'queue'       => $row->queue,
                'name'        => $displayName,
                'failed_at'   => $row->failed_at,
                'age'         => Carbon::parse($row->failed_at)->diffForHumans(),
                'exception'   => mb_substr($row->exception, 0, 200).'...',
            ];
        }

        return $list;
    }

    /**
     * Get single failed job detail.
     *
     * @param int|string $id
     * @return array|null
     */
    public function getFailedJobDetail($id): ?array
    {
        $table = config('queue.failed.table', 'failed_jobs');
        $conn = config('queue.failed.database', config('database.default'));

        if (!$this->tableExists($table, $conn)) {
            return null;
        }

        $row = DB::connection($conn)->table($table)->where('id', $id)->first();
        if (!$row) {
            return null;
        }

        $payload = json_decode($row->payload, true) ?: [];

        return [
            'id'         => $row->id,
            'uuid'       => $row->uuid ?? (string)$row->id,
            'connection' => $row->connection,
            'queue'      => $row->queue,
            'name'       => $payload['displayName'] ?? ($payload['job'] ?? 'Unknown Job'),
            'payload'    => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'exception'  => $row->exception,
            'failed_at'  => $row->failed_at,
        ];
    }

    /**
     * Get list of pending database queue jobs.
     *
     * @param int $limit
     * @return array
     */
    public function getPendingJobs(int $limit = 50): array
    {
        $table = config('queue.connections.database.table', 'jobs');

        if (!$this->tableExists($table)) {
            return [];
        }

        $rows = DB::table($table)->orderBy('id')->limit($limit)->get();

        $list = [];
        foreach ($rows as $row) {
            $payload = json_decode($row->payload, true) ?: [];

            $list[] = [
                'id'           => $row->id,
                'queue'        => $row->queue,
                'name'         => $payload['displayName'] ?? ($payload['job'] ?? 'Unknown Job'),
                'attempts'     => $row->attempts,
                'reserved_at'  => $row->reserved_at ? Carbon::createFromTimestamp($row->reserved_at)->format('Y-m-d H:i:s') : null,
                'available_at' => Carbon::createFromTimestamp($row->available_at)->format('Y-m-d H:i:s'),
                'created_at'   => Carbon::createFromTimestamp($row->created_at)->format('Y-m-d H:i:s'),
            ];
        }

        return $list;
    }

    /**
     * Get job batches.
     *
     * @param int $limit
     * @return array
     */
    public function getBatches(int $limit = 20): array
    {
        $table = config('queue.batching.table', 'job_batches');

        if (!$this->tableExists($table)) {
            return [];
        }

        $rows = DB::table($table)->orderByDesc('created_at')->limit($limit)->get();

        $list = [];
        foreach ($rows as $row) {
            $progress = $row->total_jobs > 0 ? round((($row->total_jobs - $row->pending_jobs) / $row->total_jobs) * 100) : 100;

            $list[] = [
                'id'           => $row->id,
                'name'         => $row->name,
                'total_jobs'   => $row->total_jobs,
                'pending_jobs' => $row->pending_jobs,
                'failed_jobs'  => $row->failed_jobs,
                'progress'     => $progress,
                'created_at'   => Carbon::createFromTimestamp($row->created_at)->format('Y-m-d H:i:s'),
                'finished_at'  => $row->finished_at ? Carbon::createFromTimestamp($row->finished_at)->format('Y-m-d H:i:s') : null,
                'cancelled_at' => $row->cancelled_at ? Carbon::createFromTimestamp($row->cancelled_at)->format('Y-m-d H:i:s') : null,
            ];
        }

        return $list;
    }

    /**
     * Retry a specific failed job.
     *
     * @param int|string $id
     * @return string
     */
    public function retryJob($id): string
    {
        Artisan::call('queue:retry', ['id' => [(string)$id]]);

        return trim(Artisan::output());
    }

    /**
     * Retry all failed jobs.
     *
     * @return string
     */
    public function retryAll(): string
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        return trim(Artisan::output());
    }

    /**
     * Forget/delete a single failed job.
     *
     * @param int|string $id
     * @return string
     */
    public function forgetJob($id): string
    {
        Artisan::call('queue:forget', ['id' => (string)$id]);

        return trim(Artisan::output());
    }

    /**
     * Flush all failed jobs.
     *
     * @return string
     */
    public function flushAll(): string
    {
        Artisan::call('queue:flush');

        return trim(Artisan::output());
    }

    /**
     * Restart queue workers.
     *
     * @return string
     */
    public function restartWorkers(): string
    {
        Artisan::call('queue:restart');

        return trim(Artisan::output());
    }
}
