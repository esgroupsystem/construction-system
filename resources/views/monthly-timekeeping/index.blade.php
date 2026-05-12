@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Monthly Timekeeping</h5>
                <small class="text-muted">Create monthly cutoff before viewing employees</small>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('monthly-timekeeping.cutoffs.store') }}">
                    @csrf

                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Cutoff Name</label>
                            <input type="text" name="cutoff_name" class="form-control" value="{{ old('cutoff_name') }}"
                                placeholder="Example: May 2026 Payroll">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Month</label>
                            <select name="month" class="form-select" required>
                                <option value="">Select Month</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}"
                                        {{ old('month', now()->month) == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" value="{{ old('year', now()->year) }}"
                                min="2020" max="2100" required>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                Create Cutoff
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="mb-0 fw-bold">Monthly Cutoffs</h6>
                        <small class="text-muted">Open cutoff to view monthly employees</small>
                    </div>

                    <div class="col-auto">
                        <span class="badge rounded-pill bg-primary">
                            {{ $cutoffs->total() }} cutoff(s)
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive scrollbar">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="bg-200 text-900">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Cutoff Name</th>
                                <th>Month</th>
                                <th>Covered Dates</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center pe-3">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($cutoffs as $cutoff)
                                <tr>
                                    <td class="ps-3 text-muted">
                                        {{ $loop->iteration + ($cutoffs->currentPage() - 1) * $cutoffs->perPage() }}
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $cutoff->cutoff_name ?? 'Monthly Cutoff #' . $cutoff->id }}
                                        </div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($cutoff->date_from)->diffInDays(\Carbon\Carbon::parse($cutoff->date_to)) + 1 }}
                                            day(s)
                                        </small>
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($cutoff->date_from)->format('F Y') }}
                                    </td>

                                    <td>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($cutoff->date_from)->format('M d') }}
                                            -
                                            {{ \Carbon\Carbon::parse($cutoff->date_to)->format('M d, Y') }}
                                        </small>
                                    </td>

                                    <td>
                                        @if ($cutoff->status === 'finalized')
                                            <span class="badge rounded-pill px-3 py-2"
                                                style="background-color:#d1f7e5;color:#198754;font-weight:600;">
                                                Finalized
                                            </span>
                                        @else
                                            <span class="badge rounded-pill px-3 py-2"
                                                style="background-color:#fff3cd;color:#b76e00;font-weight:600;">
                                                Open
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <small class="text-muted">
                                            {{ $cutoff->created_at->format('M d, Y') }}
                                        </small>
                                    </td>

                                    <td class="text-center pe-3">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('monthly-timekeeping.cutoffs.show', $cutoff->id) }}"
                                                class="btn btn-falcon-primary btn-sm">
                                                <span class="fas fa-folder-open me-1"></span>
                                                Open
                                            </a>

                                            <form action="{{ route('monthly-timekeeping.cutoffs.destroy', $cutoff->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-falcon-danger btn-sm confirm-action"
                                                    data-title="Delete Monthly Cutoff"
                                                    data-message="Are you sure you want to delete this monthly cutoff?"
                                                    data-confirm-text="Delete">
                                                    <span class="fas fa-trash-alt me-1"></span>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <h6 class="mb-1">No monthly cutoff created yet</h6>
                                        <p class="text-muted mb-0">Create your first monthly cutoff above.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if ($cutoffs->count() > 0)
                            <tfoot class="bg-light border-top">
                                <tr>
                                    <th colspan="7" class="px-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                Showing {{ $cutoffs->firstItem() }} to {{ $cutoffs->lastItem() }}
                                                of {{ $cutoffs->total() }} cutoff(s)
                                            </small>

                                            <small class="text-muted">
                                                Open: {{ $cutoffs->where('status', 'open')->count() }}
                                                |
                                                Finalized: {{ $cutoffs->where('status', 'finalized')->count() }}
                                            </small>
                                        </div>
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            @if ($cutoffs->hasPages())
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-end">
                        {{ $cutoffs->onEachSide(1)->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection
