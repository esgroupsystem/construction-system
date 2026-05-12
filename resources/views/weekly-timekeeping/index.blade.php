@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Weekly Timekeeping</h5>
                <small class="text-muted">Create weekly cutoff before viewing employees</small>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('weekly-timekeeping.cutoffs.store') }}">
                    @csrf

                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Cutoff Name</label>
                            <input type="text" name="cutoff_name" class="form-control"
                                placeholder="Example: Week 1 Payroll">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" required>
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
                        <h6 class="mb-0 fw-bold">Weekly Cutoffs</h6>
                        <small class="text-muted">Open cutoff to view weekly employees</small>
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
                    <table class="table table-sm table-hover table-striped align-middle mb-0">
                        <thead class="bg-200 text-900">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Cutoff Name</th>
                                <th>Date From</th>
                                <th>Date To</th>
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
                                            {{ $cutoff->cutoff_name ?? 'Weekly Cutoff #' . $cutoff->id }}
                                        </div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($cutoff->date_from)->diffInDays(\Carbon\Carbon::parse($cutoff->date_to)) + 1 }}
                                            day(s)
                                        </small>
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($cutoff->date_from)->format('M d, Y') }}
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($cutoff->date_to)->format('M d, Y') }}
                                    </td>

                                    <td>
                                        @if ($cutoff->status === 'finalized')
                                            <span class="badge rounded-pill bg-success">Finalized</span>
                                        @else
                                            <span class="badge rounded-pill bg-warning text-dark">Open</span>
                                        @endif
                                    </td>

                                    <td>
                                        <small class="text-muted">
                                            {{ $cutoff->created_at->format('M d, Y') }}
                                        </small>
                                    </td>

                                    <td class="text-center pe-3">
                                        <div class="d-flex justify-content-center gap-2">

                                            <a href="{{ route('weekly-timekeeping.cutoffs.show', $cutoff->id) }}"
                                                class="btn btn-falcon-primary btn-sm">
                                                <span class="fas fa-folder-open me-1"></span>
                                                Open
                                            </a>

                                            <form action="{{ route('weekly-timekeeping.cutoffs.destroy', $cutoff->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-falcon-danger btn-sm confirm-action"
                                                    data-title="Delete Cutoff"
                                                    data-message="Are you sure you want to delete this cutoff?"
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
                                        <h6 class="mb-1">No cutoff created yet</h6>
                                        <p class="text-muted mb-0">
                                            Create your first weekly cutoff above.
                                        </p>
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
