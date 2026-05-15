@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Locations</h5>
                    <small class="text-muted">Manage project/site locations</small>
                </div>

                <a href="{{ route('locations.create') }}" class="btn btn-primary btn-sm">
                    <span class="fas fa-plus me-1"></span>
                    Add Location
                </a>
            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive scrollbar">
                    <table class="table table-hover table-striped overflow-hidden">
                        <thead class="bg-200">
                            <tr>
                                <th>#</th>
                                <th>Location Name</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Date Created</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($locations as $location)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td class="fw-semibold">
                                        {{ $location->name }}
                                    </td>

                                    <td>
                                        {{ $location->address ?? 'N/A' }}
                                    </td>

                                    <td>
                                        @if ($location->is_active)
                                            <span class="badge badge-subtle-success">Active</span>
                                        @else
                                            <span class="badge badge-subtle-danger">Inactive</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $location->created_at->format('M d, Y') }}
                                    </td>

                                    <td class="text-end">
                                        <a href="{{ route('locations.edit', $location->id) }}"
                                            class="btn btn-falcon-default btn-sm">
                                            <span class="fas fa-edit"></span>
                                        </a>

                                        <form action="{{ route('locations.destroy', $location->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this location?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-falcon-danger btn-sm">
                                                <span class="fas fa-trash"></span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No locations found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $locations->links() }}
                </div>

            </div>
        </div>

    </div>
@endsection
