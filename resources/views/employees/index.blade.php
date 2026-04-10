@extends('layouts.app')

@section('title', 'Employees')

@section('content')
    <div class="container-fluid px-0" data-layout="container">
        <div class="content">

            {{-- HEADER --}}
            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url({{ asset('assets/img/icons/spot-illustrations/corner-4.png') }});"></div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Employees</h3>
                            <p class="text-muted mb-0">Manage employee records and profiles.</p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('employees.create') }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i> Add Employee
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MAIN --}}
            <div class="card border-0 shadow-sm">

                {{-- SEARCH --}}
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="row g-3 align-items-center justify-content-between">
                        <div class="col-lg-auto">
                            <h5 class="mb-1 text-900">Employee Directory</h5>
                            <p class="mb-0 fs--1 text-600">View and manage employee records.</p>
                        </div>

                        <div class="col-lg-4">
                            <form>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-search text-400"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-start-0"
                                        placeholder="Search employee no / name..." value="{{ request('search') }}">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover align-middle mb-0 fs--1">
                            <thead class="bg-200 text-800">
                                <tr>
                                    <th class="ps-3">EMPLOYEE</th>
                                    <th>EMPLOYEE NO</th>
                                    <th>DEPARTMENT</th>
                                    <th>POSITION</th>
                                    <th>STATUS</th>
                                    <th class="text-end pe-3">ACTION</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($employees as $emp)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xl me-3">
                                                    @if ($emp->photo_path)
                                                        <img src="{{ asset('storage/' . $emp->photo_path) }}"
                                                            class="rounded-circle">
                                                    @else
                                                        <div
                                                            class="avatar-name rounded-circle bg-soft-primary text-primary">
                                                            {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $emp->full_name }}</h6>
                                                    <div class="fs--2 text-600">Employee Profile</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>{{ $emp->employee_no }}</td>
                                        <td>{{ $emp->department ?? '-' }}</td>
                                        <td>{{ $emp->position ?? '-' }}</td>

                                        <td>
                                            <span class="badge bg-{{ $emp->is_active ? 'success' : 'secondary' }} px-3 py-2">
                                                {{ $emp->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>

                                        <td class="text-end pe-3">
                                            <a href="{{ route('employees.show', $emp) }}"
                                                class="btn btn-falcon-info btn-sm">
                                                View
                                            </a>
                                            <a href="{{ route('employees.edit', $emp) }}"
                                                class="btn btn-falcon-warning btn-sm">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <h6 class="text-600">No employees found</h6>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PAGINATION --}}
                <div class="card-footer bg-body-tertiary border-top py-3">
                    {{ $employees->withQueryString()->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection
