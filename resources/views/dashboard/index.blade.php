@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-md-6 col-xl-4">
            <div class="card h-md-100">
                <div class="card-body">
                    <h5 class="card-title">Employees</h5>
                    <p class="fs-4 mb-0">0</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-md-100">
                <div class="card-body">
                    <h5 class="card-title">Attendance Today</h5>
                    <p class="fs-4 mb-0">0</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-md-100">
                <div class="card-body">
                    <h5 class="card-title">Payroll Records</h5>
                    <p class="fs-4 mb-0">0</p>
                </div>
            </div>
        </div>
    </div>
@endsection
