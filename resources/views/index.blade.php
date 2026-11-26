<style>
    .queue-stat-card {
        border-radius: 8px;
        transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .queue-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .queue-stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 22px;
    }
    .code-box {
        background: #1e1e1e;
        color: #d4d4d4;
        font-family: Consolas, "Courier New", monospace;
        font-size: 12.5px;
        padding: 14px;
        border-radius: 6px;
        max-height: 450px;
        overflow-y: auto;
        white-space: pre-wrap;
    }
</style>

<!-- Metrics Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card queue-stat-card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="queue-stat-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="icon-exclamation-triangle"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted mb-1">{{ trans('lavero-admin-queue-manager::queue.failed_jobs') }}</h6>
                    <h3 class="card-title mb-0 fw-bold text-danger">{{ $stats['failed'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card queue-stat-card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="queue-stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="icon-hourglass-half"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted mb-1">{{ trans('lavero-admin-queue-manager::queue.pending_jobs') }}</h6>
                    <h3 class="card-title mb-0 fw-bold">{{ $stats['pending'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card queue-stat-card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="queue-stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="icon-cubes"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted mb-1">{{ trans('lavero-admin-queue-manager::queue.job_batches') }}</h6>
                    <h3 class="card-title mb-0 fw-bold">{{ $stats['batches'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card queue-stat-card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="queue-stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="icon-cogs"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted mb-1">{{ trans('lavero-admin-queue-manager::queue.driver') }}</h6>
                    <h5 class="card-title mb-0 fw-bold font-monospace text-uppercase">{{ $stats['driver'] }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Card with Tabs -->
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 py-2">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-failed" role="tab">
                    <i class="icon-times-circle text-danger me-1"></i> {{ trans('lavero-admin-queue-manager::queue.tab_failed') }}
                    @if($stats['failed'] > 0)
                        <span class="badge bg-danger ms-1">{{ $stats['failed'] }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-pending" role="tab">
                    <i class="icon-clock text-primary me-1"></i> {{ trans('lavero-admin-queue-manager::queue.tab_pending') }}
                    <span class="badge bg-secondary ms-1">{{ $stats['pending'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-batches" role="tab">
                    <i class="icon-cubes text-info me-1"></i> {{ trans('lavero-admin-queue-manager::queue.tab_batches') }}
                    <span class="badge bg-light text-dark border ms-1">{{ $stats['batches'] }}</span>
                </a>
            </li>
        </ul>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($stats['failed'] > 0)
                <select id="filter-failed-queue" class="form-select form-select-sm" style="width: auto;">
                    <option value="">All queues</option>
                    @foreach(array_unique(array_column($failedJobs, 'queue')) as $qName)
                        <option value="{{ $qName }}">{{ $qName }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-sm btn-outline-success d-none" id="btn-retry-selected">
                    <i class="icon-redo me-1"></i> Retry Selected
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger d-none" id="btn-forget-selected">
                    <i class="icon-trash me-1"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-sm btn-success" id="btn-retry-all">
                    <i class="icon-redo me-1"></i> {{ trans('lavero-admin-queue-manager::queue.retry_all') }}
                </button>
                <button type="button" class="btn btn-sm btn-danger" id="btn-flush-all">
                    <i class="icon-trash me-1"></i> {{ trans('lavero-admin-queue-manager::queue.flush') }}
                </button>
            @endif
            <div class="form-check form-switch mb-0 ms-1">
                <input class="form-check-input" type="checkbox" id="toggle-auto-refresh">
                <label class="form-check-label small text-muted" for="toggle-auto-refresh">Auto (10s)</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-warning" id="btn-restart-workers">
                <i class="icon-sync-alt me-1"></i> {{ trans('lavero-admin-queue-manager::queue.restart_workers') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="admin.ajax.reload();" title="{{ trans('admin.refresh') }}">
                <i class="icon-sync-alt"></i>
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="tab-content">
            <!-- Tab: Failed Jobs -->
            <div class="tab-pane fade show active" id="tab-failed" role="tabpanel">
                @if(empty($failedJobs))
                    <div class="text-center py-5 text-muted">
                        <i class="icon-check-circle text-success fs-1 mb-2 opacity-75"></i>
                        <h6 class="fw-normal">{{ trans('lavero-admin-queue-manager::queue.empty_failed') }}</h6>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 36px;"><input type="checkbox" class="form-check-input" id="select-all-failed"></th>
                                    <th style="width: 50px;">#</th>
                                    <th>{{ trans('lavero-admin-queue-manager::queue.job_name') }}</th>
                                    <th style="width: 120px;">{{ trans('lavero-admin-queue-manager::queue.queue') }}</th>
                                    <th>{{ trans('lavero-admin-queue-manager::queue.exception') }}</th>
                                    <th style="width: 180px;">{{ trans('lavero-admin-queue-manager::queue.failed_at') }}</th>
                                    <th style="width: 140px;" class="text-end pe-3">{{ trans('lavero-admin-queue-manager::queue.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($failedJobs as $job)
                                <tr class="failed-job-row" data-queue="{{ $job['queue'] }}">
                                    <td><input type="checkbox" class="form-check-input failed-job-check" value="{{ $job['id'] }}"></td>
                                    <td class="text-muted">{{ $job['id'] }}</td>
                                    <td>
                                        <div class="fw-semibold text-break font-monospace small">{{ $job['name'] }}</div>
                                        <small class="text-muted">{{ $job['uuid'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-75">{{ $job['queue'] }}</span>
                                    </td>
                                    <td>
                                        <div class="small text-danger text-truncate font-monospace" style="max-width: 380px;" title="{{ $job['exception'] }}">
                                            {{ $job['exception'] }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $job['failed_at'] }}</div>
                                        <small class="text-muted">{{ $job['age'] }}</small>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info btn-job-detail" data-id="{{ $job['id'] }}" title="{{ trans('lavero-admin-queue-manager::queue.details') }}">
                                                <i class="icon-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-retry-job" data-id="{{ $job['id'] }}" title="{{ trans('lavero-admin-queue-manager::queue.retry') }}">
                                                <i class="icon-redo"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-forget-job" data-id="{{ $job['id'] }}" title="{{ trans('lavero-admin-queue-manager::queue.forget') }}">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Tab: Pending Jobs -->
            <div class="tab-pane fade" id="tab-pending" role="tabpanel">
                @if(empty($pendingJobs))
                    <div class="text-center py-5 text-muted">
                        <i class="icon-hourglass text-secondary fs-1 mb-2 opacity-50"></i>
                        <h6 class="fw-normal">{{ trans('lavero-admin-queue-manager::queue.empty_pending') }}</h6>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>{{ trans('lavero-admin-queue-manager::queue.job_name') }}</th>
                                    <th style="width: 120px;">{{ trans('lavero-admin-queue-manager::queue.queue') }}</th>
                                    <th style="width: 100px;">{{ trans('lavero-admin-queue-manager::queue.attempts') }}</th>
                                    <th style="width: 170px;">{{ trans('lavero-admin-queue-manager::queue.available_at') }}</th>
                                    <th style="width: 170px;">{{ trans('lavero-admin-queue-manager::queue.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingJobs as $job)
                                <tr>
                                    <td class="text-muted">{{ $job['id'] }}</td>
                                    <td class="font-monospace small fw-medium">{{ $job['name'] }}</td>
                                    <td><span class="badge bg-primary bg-opacity-75">{{ $job['queue'] }}</span></td>
                                    <td><span class="badge bg-secondary">{{ $job['attempts'] }}</span></td>
                                    <td class="small">{{ $job['available_at'] }}</td>
                                    <td class="small text-muted">{{ $job['created_at'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Tab: Batches -->
            <div class="tab-pane fade" id="tab-batches" role="tabpanel">
                @if(empty($batches))
                    <div class="text-center py-5 text-muted">
                        <i class="icon-cubes text-secondary fs-1 mb-2 opacity-50"></i>
                        <h6 class="fw-normal">{{ trans('lavero-admin-queue-manager::queue.empty_batches') }}</h6>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ trans('admin.id') }}</th>
                                    <th>{{ trans('lavero-admin-queue-manager::queue.batch_name') }}</th>
                                    <th style="width: 180px;">{{ trans('lavero-admin-queue-manager::queue.progress') }}</th>
                                    <th style="width: 100px;">{{ trans('lavero-admin-queue-manager::queue.total_jobs') }}</th>
                                    <th style="width: 100px;">{{ trans('lavero-admin-queue-manager::queue.pending_jobs') }}</th>
                                    <th style="width: 100px;">{{ trans('lavero-admin-queue-manager::queue.failed_jobs_count') }}</th>
                                    <th style="width: 170px;">{{ trans('lavero-admin-queue-manager::queue.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $batch)
                                <tr>
                                    <td class="font-monospace small text-muted">{{ $batch['id'] }}</td>
                                    <td class="fw-medium">{{ $batch['name'] ?: 'Batch' }}</td>
                                    <td>
                                        <div class="progress" style="height: 16px;">
                                            <div class="progress-bar @if($batch['failed_jobs'] > 0) bg-danger @else bg-success @endif" role="progressbar" style="width: {{ $batch['progress'] }}%;" aria-valuenow="{{ $batch['progress'] }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ $batch['progress'] }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $batch['total_jobs'] }}</span></td>
                                    <td><span class="badge bg-primary">{{ $batch['pending_jobs'] }}</span></td>
                                    <td>
                                        @if($batch['failed_jobs'] > 0)
                                            <span class="badge bg-danger">{{ $batch['failed_jobs'] }}</span>
                                        @else
                                            <span class="badge bg-light text-dark border">0</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $batch['created_at'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal for Job Details -->
<div class="modal fade" id="modal-job-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-monospace" id="modal-job-title">{{ trans('lavero-admin-queue-manager::queue.job_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ trans('admin.close') }}"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold mb-2">{{ trans('lavero-admin-queue-manager::queue.exception') }}</h6>
                <pre class="code-box text-danger mb-4" id="modal-job-exception"></pre>

                <h6 class="fw-bold mb-2">{{ trans('lavero-admin-queue-manager::queue.payload') }}</h6>
                <pre class="code-box text-success mb-0" id="modal-job-payload"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ admin_trans('admin.close') }}</button>
            </div>
        </div>
    </div>
</div>

<script data-exec-on-popstate>
(function () {
    const retryUrl = '{{ route("queue-manager-retry") }}';
    const retryAllUrl = '{{ route("queue-manager-retry-all") }}';
    const forgetUrl = '{{ route("queue-manager-forget") }}';
    const flushUrl = '{{ route("queue-manager-flush") }}';
    const restartUrl = '{{ route("queue-manager-restart") }}';
    const detailUrl = '{{ route("queue-manager-detail") }}';

    // View Details Modal
    document.querySelectorAll('.btn-job-detail').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            admin.ajax.get(detailUrl + '?id=' + id, {}, function (res) {
                if (res && res.data && res.data.status && res.data.data) {
                    const job = res.data.data;
                    document.getElementById('modal-job-title').textContent = job.name + ' (#' + job.id + ')';
                    document.getElementById('modal-job-exception').textContent = job.exception;
                    document.getElementById('modal-job-payload').textContent = job.payload;
                    const modal = new bootstrap.Modal(document.getElementById('modal-job-detail'));
                    modal.show();
                }
            });
        });
    });

    // Retry Single Job
    document.querySelectorAll('.btn-retry-job').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            admin.ajax.post(retryUrl, { id: id }, function (res) {
                if (res && res.data && res.data.status) {
                    admin.toastr.success(res.data.message);
                    setTimeout(() => admin.ajax.reload(), 600);
                } else {
                    admin.toastr.error(res && res.data ? res.data.message : 'Error');
                }
            });
        });
    });

    // Forget Single Job
    document.querySelectorAll('.btn-forget-job').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            Swal.fire({
                title: '{{ trans("lavero-admin-queue-manager::queue.forget_confirm") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '{{ trans("lavero-admin-queue-manager::queue.forget") }}',
                cancelButtonText: '{{ admin_trans("admin.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteFn = (admin.ajax && typeof admin.ajax.delete === 'function') ? admin.ajax.delete.bind(admin.ajax) : admin.ajax.post.bind(admin.ajax);
                    deleteFn(forgetUrl + '?id=' + id, { id: id, _method: 'DELETE' }, function (res) {
                        if (res && res.data && res.data.status) {
                            admin.toastr.success(res.data.message);
                            setTimeout(() => admin.ajax.reload(), 600);
                        } else {
                            admin.toastr.error(res && res.data ? res.data.message : 'Error');
                        }
                    });
                }
            });
        });
    });

    // Retry All Jobs
    const btnRetryAll = document.getElementById('btn-retry-all');
    if (btnRetryAll) {
        btnRetryAll.addEventListener('click', function () {
            Swal.fire({
                title: '{{ trans("lavero-admin-queue-manager::queue.retry_all_confirm") }}',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '{{ trans("lavero-admin-queue-manager::queue.retry_all") }}',
                cancelButtonText: '{{ admin_trans("admin.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    admin.ajax.post(retryAllUrl, {}, function (res) {
                        if (res && res.data && res.data.status) {
                            admin.toastr.success(res.data.message);
                            setTimeout(() => admin.ajax.reload(), 600);
                        } else {
                            admin.toastr.error(res && res.data ? res.data.message : 'Error');
                        }
                    });
                }
            });
        });
    }

    // Flush All Jobs
    const btnFlushAll = document.getElementById('btn-flush-all');
    if (btnFlushAll) {
        btnFlushAll.addEventListener('click', function () {
            Swal.fire({
                title: '{{ trans("lavero-admin-queue-manager::queue.flush_confirm") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '{{ trans("lavero-admin-queue-manager::queue.flush") }}',
                cancelButtonText: '{{ admin_trans("admin.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    admin.ajax.post(flushUrl, {}, function (res) {
                        if (res && res.data && res.data.status) {
                            admin.toastr.success(res.data.message);
                            setTimeout(() => admin.ajax.reload(), 600);
                        } else {
                            admin.toastr.error(res && res.data ? res.data.message : 'Error');
                        }
                    });
                }
            });
        });
    }

    // Restart Workers
    const btnRestartWorkers = document.getElementById('btn-restart-workers');
    if (btnRestartWorkers) {
        btnRestartWorkers.addEventListener('click', function () {
            admin.ajax.post(restartUrl, {}, function (res) {
                if (res && res.data && res.data.status) {
                    admin.toastr.success(res.data.message);
                } else {
                    admin.toastr.error(res && res.data ? res.data.message : 'Error');
                }
            });
        });
    }

    // Queue filter
    const queueFilter = document.getElementById('filter-failed-queue');
    if (queueFilter) {
        queueFilter.addEventListener('change', function () {
            const val = this.value;
            document.querySelectorAll('.failed-job-row').forEach(row => {
                row.style.display = (!val || row.dataset.queue === val) ? '' : 'none';
            });
        });
    }

    // Bulk selection & actions
    const selectAll = document.getElementById('select-all-failed');
    const btnRetrySel = document.getElementById('btn-retry-selected');
    const btnForgetSel = document.getElementById('btn-forget-selected');
    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.failed-job-check:checked')).map(el => el.value);
    }
    function syncBulkButtons() {
        const count = getSelectedIds().length;
        if (btnRetrySel) btnRetrySel.classList.toggle('d-none', count === 0);
        if (btnForgetSel) btnForgetSel.classList.toggle('d-none', count === 0);
    }
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.failed-job-check').forEach(cb => {
                if (cb.closest('tr').style.display !== 'none') cb.checked = this.checked;
            });
            syncBulkButtons();
        });
    }
    document.querySelectorAll('.failed-job-check').forEach(cb => cb.addEventListener('change', syncBulkButtons));

    if (btnRetrySel) {
        btnRetrySel.addEventListener('click', function () {
            const ids = getSelectedIds();
            if (!ids.length) return;
            admin.ajax.post('{{ route("queue-manager-bulk-retry") }}', { ids: ids }, function (res) {
                if (res && res.data && res.data.status) {
                    admin.toastr.success(res.data.message);
                    setTimeout(() => admin.ajax.reload(), 600);
                }
            });
        });
    }
    if (btnForgetSel) {
        btnForgetSel.addEventListener('click', function () {
            const ids = getSelectedIds();
            if (!ids.length) return;
            admin.ajax.post('{{ route("queue-manager-bulk-forget") }}', { ids: ids }, function (res) {
                if (res && res.data && res.data.status) {
                    admin.toastr.success(res.data.message);
                    setTimeout(() => admin.ajax.reload(), 600);
                }
            });
        });
    }

    // Auto-refresh (10s)
    const autoRefresh = document.getElementById('toggle-auto-refresh');
    if (window._queueRefreshTimer) clearInterval(window._queueRefreshTimer);
    if (autoRefresh) {
        autoRefresh.checked = localStorage.getItem('lavero_queue_autorefresh') === '1';
        const startTimer = () => {
            if (window._queueRefreshTimer) clearInterval(window._queueRefreshTimer);
            if (autoRefresh.checked) {
                window._queueRefreshTimer = setInterval(() => admin.ajax.reload(), 10000);
            }
        };
        autoRefresh.addEventListener('change', function () {
            localStorage.setItem('lavero_queue_autorefresh', this.checked ? '1' : '0');
            startTimer();
        });
        startTimer();
    }
})();
</script>
