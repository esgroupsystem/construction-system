<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);

        $holidays = Holiday::whereYear('holiday_date', $year)
            ->orderBy('holiday_date')
            ->get();

        return view('holidays.index', compact('holidays', 'year'));
    }

    public function create()
    {
        return view('holidays.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'holiday_date' => 'required|date',
            'name' => 'required|string|max:255',
            'type' => 'required|in:regular,special_non_working',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Holiday::create($validated);

        return redirect()
            ->route('holidays.index', ['year' => date('Y', strtotime($validated['holiday_date']))])
            ->with('success', 'Holiday created successfully.');
    }

    public function edit(Holiday $holiday)
    {
        return view('holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'holiday_date' => 'required|date',
            'name' => 'required|string|max:255',
            'type' => 'required|in:regular,special_non_working',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $holiday->update($validated);

        return redirect()
            ->route('holidays.index', ['year' => date('Y', strtotime($validated['holiday_date']))])
            ->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $year = $holiday->holiday_date->format('Y');

        $holiday->delete();

        return redirect()
            ->route('holidays.index', ['year' => $year])
            ->with('success', 'Holiday deleted successfully.');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $year = (int) $validated['year'];

        $holidays = $this->philippineHolidays($year);

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                [
                    'holiday_date' => $holiday['holiday_date'],
                    'name' => $holiday['name'],
                ],
                [
                    'type' => $holiday['type'],
                    'is_active' => true,
                    'notes' => $holiday['notes'] ?? null,
                ]
            );
        }

        return redirect()
            ->route('holidays.index', ['year' => $year])
            ->with('success', 'Philippine holidays generated successfully.');
    }

    private function philippineHolidays(int $year): array
    {
        $holidayMap = [

            2026 => [
                ['2026-01-01', 'New Year’s Day', 'regular'],
                ['2026-01-16', 'Lailatul Isra Wal Mi Raj', 'special_non_working'],
                ['2026-01-23', 'First Philippine Republic Day', 'special_non_working'],
                ['2026-02-17', 'Chinese New Year', 'special_non_working'],
                ['2026-02-19', 'Ramadan Start', 'special_non_working'],
                ['2026-02-25', 'People Power Anniversary', 'special_non_working'],
                ['2026-03-20', 'Eid al-Fitr Holiday', 'regular'],
                ['2026-03-20', 'March Equinox', 'special_non_working'],
                ['2026-03-21', 'Eid al-Fitr', 'special_non_working'],
                ['2026-04-02', 'Maundy Thursday', 'regular'],
                ['2026-04-03', 'Good Friday', 'regular'],
                ['2026-04-04', 'Black Saturday', 'special_non_working'],
                ['2026-04-05', 'Easter Sunday', 'special_non_working'],
                ['2026-04-09', 'The Day of Valor', 'regular'],
                ['2026-05-01', 'Labor Day', 'regular'],
                ['2026-05-27', 'Eid al-Adha', 'regular'],
                ['2026-05-28', 'Eid al-Adha Day 2', 'special_non_working'],
                ['2026-06-12', 'Independence Day', 'regular'],
                ['2026-06-17', 'Amun Jadid', 'special_non_working'],
                ['2026-06-21', 'June Solstice', 'special_non_working'],
                ['2026-07-27', 'Founding Anniversary of Iglesia ni Cristo', 'special_non_working'],
                ['2026-08-21', 'Ninoy Aquino Day', 'special_non_working'],
                ['2026-08-26', 'Maulid un-Nabi', 'special_non_working'],
                ['2026-08-31', 'National Heroes Day', 'regular'],
                ['2026-09-03', 'Yamashita Surrender Day', 'special_non_working'],
                ['2026-09-08', 'Feast of the Nativity of Mary', 'special_non_working'],
                ['2026-09-23', 'September Equinox', 'special_non_working'],
                ['2026-11-01', 'All Saints’ Day', 'special_non_working'],
                ['2026-11-02', 'All Souls’ Day', 'special_non_working'],
                ['2026-11-07', 'Sheikh Karim’ul Makhdum Day', 'special_non_working'],
                ['2026-11-30', 'Bonifacio Day', 'regular'],
                ['2026-12-08', 'Feast of the Immaculate Conception', 'special_non_working'],
                ['2026-12-22', 'December Solstice', 'special_non_working'],
                ['2026-12-24', 'Christmas Eve', 'special_non_working'],
                ['2026-12-25', 'Christmas Day', 'regular'],
                ['2026-12-30', 'Rizal Day', 'regular'],
                ['2026-12-31', 'New Year’s Eve', 'special_non_working'],
            ],

            2027 => [
                ['2027-01-01', 'New Year’s Day', 'regular'],
                ['2027-01-06', 'Lailatul Isra Wal Mi Raj', 'special_non_working'],
                ['2027-01-23', 'First Philippine Republic Day', 'special_non_working'],
                ['2027-02-06', 'Lunar New Year’s Day', 'special_non_working'],
                ['2027-02-08', 'Ramadan Start', 'special_non_working'],
                ['2027-02-25', 'People Power Anniversary', 'special_non_working'],
                ['2027-03-10', 'Eid al-Fitr', 'regular'],
                ['2027-03-21', 'March Equinox', 'special_non_working'],
                ['2027-03-25', 'Maundy Thursday', 'regular'],
                ['2027-03-26', 'Good Friday', 'regular'],
                ['2027-03-27', 'Black Saturday', 'special_non_working'],
                ['2027-03-28', 'Easter Sunday', 'special_non_working'],
                ['2027-04-09', 'The Day of Valor', 'regular'],
                ['2027-05-01', 'Labor Day', 'regular'],
                ['2027-05-17', 'Eid al-Adha', 'regular'],
                ['2027-05-18', 'Eid al-Adha Day 2', 'special_non_working'],
                ['2027-06-06', 'Amun Jadid', 'special_non_working'],
                ['2027-06-12', 'Independence Day', 'regular'],
                ['2027-06-21', 'June Solstice', 'special_non_working'],
                ['2027-07-27', 'Founding Anniversary of Iglesia ni Cristo', 'special_non_working'],
                ['2027-08-15', 'Maulid un-Nabi', 'special_non_working'],
                ['2027-08-21', 'Ninoy Aquino Day', 'special_non_working'],
                ['2027-08-30', 'National Heroes Day', 'regular'],
                ['2027-09-03', 'Yamashita Surrender Day', 'special_non_working'],
                ['2027-09-08', 'Feast of the Nativity of Mary', 'special_non_working'],
                ['2027-09-23', 'September Equinox', 'special_non_working'],
                ['2027-11-01', 'All Saints’ Day', 'special_non_working'],
                ['2027-11-02', 'All Souls’ Day', 'special_non_working'],
                ['2027-11-07', 'Sheikh Karim’ul Makhdum Day', 'special_non_working'],
                ['2027-11-30', 'Bonifacio Day', 'regular'],
                ['2027-12-08', 'Feast of the Immaculate Conception', 'special_non_working'],
                ['2027-12-22', 'December Solstice', 'special_non_working'],
                ['2027-12-24', 'Christmas Eve', 'special_non_working'],
                ['2027-12-25', 'Christmas Day', 'regular'],
                ['2027-12-26', 'Lailatul Isra Wal Mi Raj', 'special_non_working'],
                ['2027-12-30', 'Rizal Day', 'regular'],
                ['2027-12-31', 'New Year’s Eve', 'special_non_working'],
            ],

            2028 => [
                ['2028-01-01', 'New Year’s Day', 'regular'],
                ['2028-01-23', 'First Philippine Republic Day', 'special_non_working'],
                ['2028-01-26', 'Lunar New Year’s Day', 'special_non_working'],
                ['2028-01-28', 'Ramadan Start', 'special_non_working'],
                ['2028-02-25', 'People Power Anniversary', 'special_non_working'],
                ['2028-02-27', 'Eid al-Fitr', 'regular'],
                ['2028-03-20', 'March Equinox', 'special_non_working'],
                ['2028-04-09', 'The Day of Valor', 'regular'],
                ['2028-04-13', 'Maundy Thursday', 'regular'],
                ['2028-04-14', 'Good Friday', 'regular'],
                ['2028-04-15', 'Black Saturday', 'special_non_working'],
                ['2028-04-16', 'Easter Sunday', 'special_non_working'],
                ['2028-05-01', 'Labor Day', 'regular'],
                ['2028-05-05', 'Eid al-Adha', 'regular'],
                ['2028-05-06', 'Eid al-Adha Day 2', 'special_non_working'],
                ['2028-05-25', 'Amun Jadid', 'special_non_working'],
                ['2028-06-12', 'Independence Day', 'regular'],
                ['2028-06-21', 'June Solstice', 'special_non_working'],
                ['2028-07-27', 'Founding Anniversary of Iglesia ni Cristo', 'special_non_working'],
                ['2028-08-03', 'Maulid un-Nabi', 'special_non_working'],
                ['2028-08-21', 'Ninoy Aquino Day', 'special_non_working'],
                ['2028-08-28', 'National Heroes Day', 'regular'],
                ['2028-09-03', 'Yamashita Surrender Day', 'special_non_working'],
                ['2028-09-08', 'Feast of the Nativity of Mary', 'special_non_working'],
                ['2028-09-22', 'September Equinox', 'special_non_working'],
                ['2028-11-01', 'All Saints’ Day', 'special_non_working'],
                ['2028-11-02', 'All Souls’ Day', 'special_non_working'],
                ['2028-11-07', 'Sheikh Karim’ul Makhdum Day', 'special_non_working'],
                ['2028-11-30', 'Bonifacio Day', 'regular'],
                ['2028-12-08', 'Feast of the Immaculate Conception', 'special_non_working'],
                ['2028-12-14', 'Lailatul Isra Wal Mi Raj', 'special_non_working'],
                ['2028-12-21', 'December Solstice', 'special_non_working'],
                ['2028-12-24', 'Christmas Eve', 'special_non_working'],
                ['2028-12-25', 'Christmas Day', 'regular'],
                ['2028-12-30', 'Rizal Day', 'regular'],
                ['2028-12-31', 'New Year’s Eve', 'special_non_working'],
            ],

            2029 => [
                ['2029-01-01', 'New Year’s Day', 'regular'],
                ['2029-01-16', 'Ramadan Start', 'special_non_working'],
                ['2029-01-23', 'First Philippine Republic Day', 'special_non_working'],
                ['2029-02-13', 'Lunar New Year’s Day', 'special_non_working'],
                ['2029-02-15', 'Eid al-Fitr', 'regular'],
                ['2029-02-25', 'People Power Anniversary', 'special_non_working'],
                ['2029-03-20', 'March Equinox', 'special_non_working'],
                ['2029-03-29', 'Maundy Thursday', 'regular'],
                ['2029-03-30', 'Good Friday', 'regular'],
                ['2029-03-31', 'Black Saturday', 'special_non_working'],
                ['2029-04-01', 'Easter Sunday', 'special_non_working'],
                ['2029-04-09', 'The Day of Valor', 'regular'],
                ['2029-04-24', 'Eid al-Adha', 'regular'],
                ['2029-04-25', 'Eid al-Adha Day 2', 'special_non_working'],
                ['2029-05-01', 'Labor Day', 'regular'],
                ['2029-05-15', 'Amun Jadid', 'special_non_working'],
                ['2029-06-12', 'Independence Day', 'regular'],
                ['2029-06-21', 'June Solstice', 'special_non_working'],
                ['2029-07-24', 'Maulid un-Nabi', 'special_non_working'],
                ['2029-07-27', 'Founding Anniversary of Iglesia ni Cristo', 'special_non_working'],
                ['2029-08-21', 'Ninoy Aquino Day', 'special_non_working'],
                ['2029-08-27', 'National Heroes Day', 'regular'],
                ['2029-09-03', 'Yamashita Surrender Day', 'special_non_working'],
                ['2029-09-08', 'Feast of the Nativity of Mary', 'special_non_working'],
                ['2029-09-23', 'September Equinox', 'special_non_working'],
                ['2029-11-01', 'All Saints’ Day', 'special_non_working'],
                ['2029-11-02', 'All Souls’ Day', 'special_non_working'],
                ['2029-11-07', 'Sheikh Karim’ul Makhdum Day', 'special_non_working'],
                ['2029-11-30', 'Bonifacio Day', 'regular'],
                ['2029-12-04', 'Lailatul Isra Wal Mi Raj', 'special_non_working'],
                ['2029-12-08', 'Feast of the Immaculate Conception', 'special_non_working'],
                ['2029-12-21', 'December Solstice', 'special_non_working'],
                ['2029-12-24', 'Christmas Eve', 'special_non_working'],
                ['2029-12-25', 'Christmas Day', 'regular'],
                ['2029-12-30', 'Rizal Day', 'regular'],
                ['2029-12-31', 'New Year’s Eve', 'special_non_working'],
            ],

            2030 => [
                ['2030-01-01', 'New Year’s Day', 'regular'],
                ['2030-01-06', 'Ramadan Start', 'special_non_working'],
                ['2030-01-23', 'First Philippine Republic Day', 'special_non_working'],
                ['2030-02-03', 'Lunar New Year’s Day', 'special_non_working'],
                ['2030-02-05', 'Eid al-Fitr', 'regular'],
                ['2030-02-25', 'People Power Anniversary', 'special_non_working'],
                ['2030-03-20', 'March Equinox', 'special_non_working'],
                ['2030-04-09', 'The Day of Valor', 'regular'],
                ['2030-04-14', 'Eid al-Adha', 'regular'],
                ['2030-04-15', 'Eid al-Adha Day 2', 'special_non_working'],
                ['2030-04-18', 'Maundy Thursday', 'regular'],
                ['2030-04-19', 'Good Friday', 'regular'],
                ['2030-04-20', 'Black Saturday', 'special_non_working'],
                ['2030-04-21', 'Easter Sunday', 'special_non_working'],
                ['2030-05-01', 'Labor Day', 'regular'],
                ['2030-05-04', 'Amun Jadid', 'special_non_working'],
                ['2030-06-12', 'Independence Day', 'regular'],
                ['2030-06-21', 'June Solstice', 'special_non_working'],
                ['2030-07-13', 'Maulid un-Nabi', 'special_non_working'],
                ['2030-07-27', 'Founding Anniversary of Iglesia ni Cristo', 'special_non_working'],
                ['2030-08-21', 'Ninoy Aquino Day', 'special_non_working'],
                ['2030-08-26', 'National Heroes Day', 'regular'],
                ['2030-09-03', 'Yamashita Surrender Day', 'special_non_working'],
                ['2030-09-08', 'Feast of the Nativity of Mary', 'special_non_working'],
                ['2030-09-23', 'September Equinox', 'special_non_working'],
                ['2030-11-01', 'All Saints’ Day', 'special_non_working'],
                ['2030-11-02', 'All Souls’ Day', 'special_non_working'],
                ['2030-11-07', 'Sheikh Karim’ul Makhdum Day', 'special_non_working'],
                ['2030-11-23', 'Lailatul Isra Wal Mi Raj', 'special_non_working'],
                ['2030-11-30', 'Bonifacio Day', 'regular'],
                ['2030-12-08', 'Feast of the Immaculate Conception', 'special_non_working'],
                ['2030-12-22', 'December Solstice', 'special_non_working'],
                ['2030-12-24', 'Christmas Eve', 'special_non_working'],
                ['2030-12-25', 'Christmas Day', 'regular'],
                ['2030-12-26', 'Ramadan Start', 'special_non_working'],
                ['2030-12-30', 'Rizal Day', 'regular'],
                ['2030-12-31', 'New Year’s Eve', 'special_non_working'],
            ],

            2031 => [
                ['2031-01-01', 'New Year’s Day', 'regular'],
                ['2031-01-23', 'Lunar New Year’s Day', 'special_non_working'],
                ['2031-01-23', 'First Philippine Republic Day', 'special_non_working'],
                ['2031-01-25', 'Eid al-Fitr', 'regular'],
                ['2031-02-25', 'People Power Anniversary', 'special_non_working'],
                ['2031-04-03', 'Eid al-Adha', 'regular'],
                ['2031-04-04', 'Eid al-Adha Day 2', 'special_non_working'],
                ['2031-04-09', 'The Day of Valor', 'regular'],
                ['2031-04-10', 'Maundy Thursday', 'regular'],
                ['2031-04-11', 'Good Friday', 'regular'],
                ['2031-04-12', 'Black Saturday', 'special_non_working'],
                ['2031-04-13', 'Easter Sunday', 'special_non_working'],
                ['2031-04-23', 'Amun Jadid', 'special_non_working'],
                ['2031-05-01', 'Labor Day', 'regular'],
                ['2031-06-12', 'Independence Day', 'regular'],
                ['2031-07-02', 'Maulid un-Nabi', 'special_non_working'],
                ['2031-07-27', 'Founding Anniversary of Iglesia ni Cristo', 'special_non_working'],
                ['2031-08-21', 'Ninoy Aquino Day', 'special_non_working'],
                ['2031-08-25', 'National Heroes Day', 'regular'],
                ['2031-09-03', 'Yamashita Surrender Day', 'special_non_working'],
                ['2031-09-08', 'Feast of the Nativity of Mary', 'special_non_working'],
                ['2031-11-01', 'All Saints’ Day', 'special_non_working'],
                ['2031-11-02', 'All Souls’ Day', 'special_non_working'],
                ['2031-11-07', 'Sheikh Karim’ul Makhdum Day', 'special_non_working'],
                ['2031-11-12', 'Lailatul Isra Wal Mi Raj', 'special_non_working'],
                ['2031-11-30', 'Bonifacio Day', 'regular'],
                ['2031-12-08', 'Feast of the Immaculate Conception', 'special_non_working'],
                ['2031-12-15', 'Ramadan Start', 'special_non_working'],
                ['2031-12-24', 'Christmas Eve', 'special_non_working'],
                ['2031-12-25', 'Christmas Day', 'regular'],
                ['2031-12-30', 'Rizal Day', 'regular'],
                ['2031-12-31', 'New Year’s Eve', 'special_non_working'],
            ],

            2032 => [
                ['2032-01-01', 'New Year’s Day', 'regular'],
                ['2032-01-14', 'Eid al-Fitr', 'regular'],
                ['2032-01-23', 'First Philippine Republic Day', 'special_non_working'],
                ['2032-02-11', 'Lunar New Year’s Day', 'special_non_working'],
                ['2032-02-25', 'People Power Anniversary', 'special_non_working'],
                ['2032-03-22', 'Eid al-Adha', 'regular'],
                ['2032-03-23', 'Eid al-Adha Day 2', 'special_non_working'],
                ['2032-03-25', 'Maundy Thursday', 'regular'],
                ['2032-03-26', 'Good Friday', 'regular'],
                ['2032-03-27', 'Black Saturday', 'special_non_working'],
                ['2032-03-28', 'Easter Sunday', 'special_non_working'],
                ['2032-04-09', 'The Day of Valor', 'regular'],
                ['2032-04-12', 'Amun Jadid', 'special_non_working'],
                ['2032-05-01', 'Labor Day', 'regular'],
                ['2032-06-12', 'Independence Day', 'regular'],
                ['2032-06-21', 'Maulid un-Nabi', 'special_non_working'],
                ['2032-07-27', 'Founding Anniversary of Iglesia ni Cristo', 'special_non_working'],
                ['2032-08-21', 'Ninoy Aquino Day', 'special_non_working'],
                ['2032-08-30', 'National Heroes Day', 'regular'],
                ['2032-09-03', 'Yamashita Surrender Day', 'special_non_working'],
                ['2032-09-08', 'Feast of the Nativity of Mary', 'special_non_working'],
                ['2032-11-01', 'All Saints’ Day', 'special_non_working'],
                ['2032-11-01', 'Lailatul Isra Wal Mi Raj', 'special_non_working'],
                ['2032-11-02', 'All Souls’ Day', 'special_non_working'],
                ['2032-11-07', 'Sheikh Karim’ul Makhdum Day', 'special_non_working'],
                ['2032-11-30', 'Bonifacio Day', 'regular'],
                ['2032-12-04', 'Ramadan Start', 'special_non_working'],
                ['2032-12-08', 'Feast of the Immaculate Conception', 'special_non_working'],
                ['2032-12-24', 'Christmas Eve', 'special_non_working'],
                ['2032-12-25', 'Christmas Day', 'regular'],
                ['2032-12-30', 'Rizal Day', 'regular'],
                ['2032-12-31', 'New Year’s Eve', 'special_non_working'],
            ],

            2033 => [
                ['2033-01-01', 'New Year’s Day', 'regular'],
                ['2033-01-03', 'Eid al-Fitr', 'regular'],
                ['2033-01-23', 'First Philippine Republic Day', 'special_non_working'],
                ['2033-01-31', 'Lunar New Year’s Day', 'special_non_working'],
                ['2033-02-25', 'People Power Anniversary', 'special_non_working'],
                ['2033-03-12', 'Eid al-Adha', 'regular'],
                ['2033-03-13', 'Eid al-Adha Day 2', 'special_non_working'],
                ['2033-04-01', 'Amun Jadid', 'special_non_working'],
                ['2033-04-09', 'The Day of Valor', 'regular'],
                ['2033-04-14', 'Maundy Thursday', 'regular'],
                ['2033-04-15', 'Good Friday', 'regular'],
                ['2033-04-16', 'Black Saturday', 'special_non_working'],
                ['2033-04-17', 'Easter Sunday', 'special_non_working'],
                ['2033-05-01', 'Labor Day', 'regular'],
                ['2033-06-10', 'Maulid un-Nabi', 'special_non_working'],
                ['2033-06-12', 'Independence Day', 'regular'],
                ['2033-07-27', 'Founding Anniversary of Iglesia ni Cristo', 'special_non_working'],
                ['2033-08-21', 'Ninoy Aquino Day', 'special_non_working'],
                ['2033-08-29', 'National Heroes Day', 'regular'],
                ['2033-09-03', 'Yamashita Surrender Day', 'special_non_working'],
                ['2033-09-08', 'Feast of the Nativity of Mary', 'special_non_working'],
                ['2033-10-21', 'Lailatul Isra Wal Mi Raj', 'special_non_working'],
                ['2033-11-01', 'All Saints’ Day', 'special_non_working'],
                ['2033-11-02', 'All Souls’ Day', 'special_non_working'],
                ['2033-11-07', 'Sheikh Karim’ul Makhdum Day', 'special_non_working'],
                ['2033-11-23', 'Ramadan Start', 'special_non_working'],
                ['2033-11-30', 'Bonifacio Day', 'regular'],
                ['2033-12-08', 'Feast of the Immaculate Conception', 'special_non_working'],
                ['2033-12-23', 'Eid al-Fitr', 'regular'],
                ['2033-12-24', 'Christmas Eve', 'special_non_working'],
                ['2033-12-25', 'Christmas Day', 'regular'],
                ['2033-12-30', 'Rizal Day', 'regular'],
                ['2033-12-31', 'New Year’s Eve', 'special_non_working'],
            ],

            2034 => [
                ['2034-01-01', 'New Year’s Day', 'regular'],
                ['2034-01-23', 'First Philippine Republic Day', 'special_non_working'],
                ['2034-02-19', 'Lunar New Year’s Day', 'special_non_working'],
                ['2034-02-25', 'People Power Anniversary', 'special_non_working'],
                ['2034-03-01', 'Eid al-Adha', 'regular'],
                ['2034-03-02', 'Eid al-Adha Day 2', 'special_non_working'],
                ['2034-03-21', 'Amun Jadid', 'special_non_working'],
                ['2034-04-06', 'Maundy Thursday', 'regular'],
                ['2034-04-07', 'Good Friday', 'regular'],
                ['2034-04-08', 'Black Saturday', 'special_non_working'],
                ['2034-04-09', 'Easter Sunday', 'special_non_working'],
                ['2034-04-09', 'The Day of Valor', 'regular'],
                ['2034-05-01', 'Labor Day', 'regular'],
                ['2034-05-30', 'Maulid un-Nabi', 'special_non_working'],
                ['2034-06-12', 'Independence Day', 'regular'],
                ['2034-07-27', 'Founding Anniversary of Iglesia ni Cristo', 'special_non_working'],
                ['2034-08-21', 'Ninoy Aquino Day', 'special_non_working'],
                ['2034-08-28', 'National Heroes Day', 'regular'],
                ['2034-09-03', 'Yamashita Surrender Day', 'special_non_working'],
                ['2034-09-08', 'Feast of the Nativity of Mary', 'special_non_working'],
                ['2034-10-10', 'Lailatul Isra Wal Mi Raj', 'special_non_working'],
                ['2034-11-01', 'All Saints’ Day', 'special_non_working'],
                ['2034-11-02', 'All Souls’ Day', 'special_non_working'],
                ['2034-11-07', 'Sheikh Karim’ul Makhdum Day', 'special_non_working'],
                ['2034-11-12', 'Ramadan Start', 'special_non_working'],
                ['2034-11-30', 'Bonifacio Day', 'regular'],
                ['2034-12-08', 'Feast of the Immaculate Conception', 'special_non_working'],
                ['2034-12-12', 'Eid al-Fitr', 'regular'],
                ['2034-12-24', 'Christmas Eve', 'special_non_working'],
                ['2034-12-25', 'Christmas Day', 'regular'],
                ['2034-12-30', 'Rizal Day', 'regular'],
                ['2034-12-31', 'New Year’s Eve', 'special_non_working'],
            ],

            2035 => [
                ['2035-01-01', 'New Year’s Day', 'regular'],
                ['2035-01-23', 'First Philippine Republic Day', 'special_non_working'],
                ['2035-02-08', 'Lunar New Year’s Day', 'special_non_working'],
                ['2035-02-18', 'Eid al-Adha', 'regular'],
                ['2035-02-19', 'Eid al-Adha Day 2', 'special_non_working'],
                ['2035-02-25', 'People Power Anniversary', 'special_non_working'],
                ['2035-03-11', 'Amun Jadid', 'special_non_working'],
                ['2035-03-22', 'Maundy Thursday', 'regular'],
                ['2035-03-23', 'Good Friday', 'regular'],
                ['2035-03-24', 'Black Saturday', 'special_non_working'],
                ['2035-03-25', 'Easter Sunday', 'special_non_working'],
                ['2035-04-09', 'The Day of Valor', 'regular'],
                ['2035-05-01', 'Labor Day', 'regular'],
                ['2035-05-20', 'Maulid un-Nabi', 'special_non_working'],
                ['2035-06-12', 'Independence Day', 'regular'],
                ['2035-07-27', 'Founding Anniversary of Iglesia ni Cristo', 'special_non_working'],
                ['2035-08-21', 'Ninoy Aquino Day', 'special_non_working'],
                ['2035-08-27', 'National Heroes Day', 'regular'],
                ['2035-09-03', 'Yamashita Surrender Day', 'special_non_working'],
                ['2035-09-08', 'Feast of the Nativity of Mary', 'special_non_working'],
                ['2035-09-30', 'Lailatul Isra Wal Mi Raj', 'special_non_working'],
                ['2035-11-01', 'All Saints’ Day', 'special_non_working'],
                ['2035-11-02', 'All Souls’ Day', 'special_non_working'],
                ['2035-11-02', 'Ramadan Start', 'special_non_working'],
                ['2035-11-07', 'Sheikh Karim’ul Makhdum Day', 'special_non_working'],
                ['2035-11-30', 'Bonifacio Day', 'regular'],
                ['2035-12-02', 'Eid al-Fitr', 'regular'],
                ['2035-12-08', 'Feast of the Immaculate Conception', 'special_non_working'],
                ['2035-12-24', 'Christmas Eve', 'special_non_working'],
                ['2035-12-25', 'Christmas Day', 'regular'],
                ['2035-12-30', 'Rizal Day', 'regular'],
                ['2035-12-31', 'New Year’s Eve', 'special_non_working'],
            ],
        ];

        if (isset($holidayMap[$year])) {
            return collect($holidayMap[$year])->map(function ($holiday) {
                return [
                    'holiday_date' => $holiday[0],
                    'name' => $holiday[1],
                    'type' => $holiday[2],
                ];
            })->toArray();
        }

        return [
            ['holiday_date' => "$year-01-01", 'name' => 'New Year’s Day', 'type' => 'regular'],
            ['holiday_date' => "$year-04-09", 'name' => 'The Day of Valor', 'type' => 'regular'],
            ['holiday_date' => "$year-05-01", 'name' => 'Labor Day', 'type' => 'regular'],
            ['holiday_date' => "$year-06-12", 'name' => 'Independence Day', 'type' => 'regular'],
            ['holiday_date' => $this->lastMondayOfAugust($year), 'name' => 'National Heroes Day', 'type' => 'regular'],
            ['holiday_date' => "$year-11-30", 'name' => 'Bonifacio Day', 'type' => 'regular'],
            ['holiday_date' => "$year-12-25", 'name' => 'Christmas Day', 'type' => 'regular'],
            ['holiday_date' => "$year-12-30", 'name' => 'Rizal Day', 'type' => 'regular'],

            ['holiday_date' => "$year-08-21", 'name' => 'Ninoy Aquino Day', 'type' => 'special_non_working'],
            ['holiday_date' => "$year-11-01", 'name' => 'All Saints’ Day', 'type' => 'special_non_working'],
            ['holiday_date' => "$year-11-02", 'name' => 'All Souls’ Day', 'type' => 'special_non_working'],
            ['holiday_date' => "$year-12-08", 'name' => 'Feast of the Immaculate Conception', 'type' => 'special_non_working'],
            ['holiday_date' => "$year-12-24", 'name' => 'Christmas Eve', 'type' => 'special_non_working'],
            ['holiday_date' => "$year-12-31", 'name' => 'New Year’s Eve', 'type' => 'special_non_working'],
        ];
    }

    private function lastMondayOfAugust(int $year): string
    {
        return date('Y-m-d', strtotime("last monday of August $year"));
    }
}
