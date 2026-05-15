@extends('layouts.app')

@section('title', 'Holiday Calendar')

@section('content')
    <div class="container-fluid px-0" data-layout="container">
        <div class="content">

            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url({{ asset('assets/img/icons/spot-illustrations/corner-4.png') }});">
                </div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Holiday Calendar</h3>
                            <p class="text-muted mb-0">
                                Manage Philippine Regular Holidays and Special Non-Working Days.
                            </p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('holidays.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Add Holiday
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-4">
                            <form method="GET" action="{{ route('holidays.index') }}">
                                <label class="form-label">View Year</label>
                                <div class="input-group">
                                    <input type="number" name="year" class="form-control" value="{{ $year }}"
                                        min="2020" max="2100">
                                    <button class="btn btn-falcon-primary">
                                        <i class="fas fa-search me-1"></i> View
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-4">
                            <form method="POST" action="{{ route('holidays.generate') }}">
                                @csrf
                                <label class="form-label">Generate Philippine Holidays</label>
                                <div class="input-group">
                                    <input type="number" name="year" class="form-control" value="{{ $year }}"
                                        min="2020" max="2100">
                                    <button class="btn btn-falcon-success">
                                        <i class="fas fa-calendar-plus me-1"></i> Generate
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-4">
                            <div class="alert alert-info mb-0 py-2">
                                <strong>Pay Rules:</strong><br>
                                Regular: 100% if unworked, 200% if worked.<br>
                                Special: no work no pay, 130% if worked.
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row g-3">

                @php
                    $months = collect(range(1, 12));
                @endphp

                @foreach ($months as $month)
                    @php
                        $firstDay = \Carbon\Carbon::create($year, $month, 1);
                        $daysInMonth = $firstDay->daysInMonth;
                        $startDay = $firstDay->dayOfWeek;

                        $monthHolidays = $holidays->filter(function ($holiday) use ($month) {
                            return $holiday->holiday_date->month == $month;
                        });
                    @endphp

                    <div class="col-12 col-xl-6">
                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-header bg-body-tertiary border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        {{ $firstDay->format('F Y') }}
                                    </h5>

                                    <span class="badge badge-subtle-primary">
                                        {{ $monthHolidays->count() }} Holidays
                                    </span>
                                </div>
                            </div>

                            <div class="card-body">

                                <div class="table-responsive">
                                    <table class="table table-bordered text-center align-middle mb-0">

                                        <thead class="bg-200">
                                            <tr>
                                                <th class="text-danger">Sun</th>
                                                <th>Mon</th>
                                                <th>Tue</th>
                                                <th>Wed</th>
                                                <th>Thu</th>
                                                <th>Fri</th>
                                                <th class="text-primary">Sat</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            @php
                                                $day = 1;
                                                $totalCells = ceil(($daysInMonth + $startDay) / 7) * 7;
                                            @endphp

                                            @for ($i = 0; $i < $totalCells / 7; $i++)
                                                <tr>

                                                    @for ($j = 0; $j < 7; $j++)
                                                        @php
                                                            $cell = $i * 7 + $j;
                                                        @endphp

                                                        @if ($cell < $startDay || $day > $daysInMonth)
                                                            <td class="bg-light"></td>
                                                        @else
                                                            @php
                                                                $currentDate = \Carbon\Carbon::create(
                                                                    $year,
                                                                    $month,
                                                                    $day,
                                                                )->format('Y-m-d');

                                                                $holiday = $holidays->first(function ($h) use (
                                                                    $currentDate,
                                                                ) {
                                                                    return $h->holiday_date->format('Y-m-d') ===
                                                                        $currentDate;
                                                                });
                                                            @endphp

                                                            <td class="position-relative p-1
                                                    @if ($holiday) @if ($holiday->type === 'regular')
                                                            bg-danger-subtle
                                                        @else
                                                            bg-warning-subtle @endif
                                                    @endif
                                                "
                                                                style="height: 90px; width: 14%;">

                                                                <div class="fw-bold mb-1">
                                                                    {{ $day }}
                                                                </div>

                                                                @if ($holiday)

                                                                    <div
                                                                        class="small fw-semibold
                                                            @if ($holiday->type === 'regular') text-danger
                                                            @else
                                                                text-warning @endif
                                                        ">
                                                                        {{ $holiday->name }}
                                                                    </div>

                                                                    <div class="mt-1">

                                                                        @if ($holiday->type === 'regular')
                                                                            <span class="badge badge-subtle-danger">
                                                                                Regular
                                                                            </span>
                                                                        @else
                                                                            <span class="badge badge-subtle-warning">
                                                                                Special
                                                                            </span>
                                                                        @endif

                                                                    </div>

                                                                    <div class="mt-2 d-flex justify-content-center gap-1">

                                                                        <a href="{{ route('holidays.edit', $holiday) }}"
                                                                            class="btn btn-falcon-default btn-sm px-2 py-1">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>

                                                                        <form
                                                                            action="{{ route('holidays.destroy', $holiday) }}"
                                                                            method="POST"
                                                                            onsubmit="return confirm('Delete this holiday?')">
                                                                            @csrf
                                                                            @method('DELETE')

                                                                            <button
                                                                                class="btn btn-falcon-danger btn-sm px-2 py-1">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </form>

                                                                    </div>

                                                                @endif

                                                            </td>

                                                            @php $day++; @endphp

                                                        @endif
                                                    @endfor

                                                </tr>
                                            @endfor

                                        </tbody>

                                    </table>
                                </div>

                            </div>

                        </div>
                    </div>
                @endforeach

            </div>

        </div>
    </div>
@endsection
