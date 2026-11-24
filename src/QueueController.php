<?php

namespace LaveroAdmin\Admin\QueueManager;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaveroAdmin\Admin\Facades\Admin;
use LaveroAdmin\Admin\Layout\Content;

class QueueController
{
    protected QueueService $service;

    public function __construct(QueueService $service)
    {
        $this->service = $service;
    }

    public function index(): Content
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('lavero-admin-queue-manager::queue.title'));
            $content->description(trans('lavero-admin-queue-manager::queue.description'));

            $stats = $this->service->getStats();
            $failedJobs = $this->service->getFailedJobs(50);
            $pendingJobs = $this->service->getPendingJobs(50);
            $batches = $this->service->getBatches(20);

            $content->body(view('lavero-admin-queue-manager::index', compact('stats', 'failedJobs', 'pendingJobs', 'batches')));
        });
    }

    public function detail(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $job = $this->service->getFailedJobDetail($id);

        if (!$job) {
            return response()->json([
                'status'  => false,
                'message' => 'Job not found',
            ]);
        }

        return response()->json([
            'status' => true,
            'data'   => $job,
        ]);
    }

    public function retry(Request $request): JsonResponse
    {
        $id = $request->input('id');

        try {
            $output = $this->service->retryJob($id);

            return response()->json([
                'status'  => true,
                'message' => trans('lavero-admin-queue-manager::queue.retry_success'),
                'output'  => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function retryAll(): JsonResponse
    {
        try {
            $output = $this->service->retryAll();

            return response()->json([
                'status'  => true,
                'message' => trans('lavero-admin-queue-manager::queue.retry_all_success'),
                'output'  => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function forget(Request $request): JsonResponse
    {
        $id = $request->input('id');

        try {
            $output = $this->service->forgetJob($id);

            return response()->json([
                'status'  => true,
                'message' => trans('lavero-admin-queue-manager::queue.forget_success'),
                'output'  => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function flush(): JsonResponse
    {
        try {
            $output = $this->service->flushAll();

            return response()->json([
                'status'  => true,
                'message' => trans('lavero-admin-queue-manager::queue.flush_success'),
                'output'  => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function restart(): JsonResponse
    {
        try {
            $output = $this->service->restartWorkers();

            return response()->json([
                'status'  => true,
                'message' => trans('lavero-admin-queue-manager::queue.restart_success'),
                'output'  => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
