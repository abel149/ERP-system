<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('employee')->latest()->paginate(15);

        return view('attendances.index', compact('attendances'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->get();

        return view('attendances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:present,absent,late,half_day'],
            'notes' => ['nullable', 'string'],
        ]);

        Attendance::create($data);

        return redirect()->route('attendances.index')->with('success', 'Attendance recorded successfully.');
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $today = now()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            ['status' => 'present']
        );

        if ($attendance->check_in) {
            return back()->with('error', 'Already checked in today.');
        }

        $attendance->check_in = now()->toTimeString();
        $attendance->status = now()->format('H:i') > '09:00' ? 'late' : 'present';
        $attendance->save();

        return back()->with('success', 'Checked in successfully at ' . $attendance->check_in);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return back()->with('error', 'No check-in record found for today.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'Already checked out today.');
        }

        $attendance->check_out = now()->toTimeString();
        $attendance->save();

        return back()->with('success', 'Checked out successfully at ' . $attendance->check_out);
    }

    public function edit(Attendance $attendance)
    {
        $employees = Employee::where('status', 'active')->get();

        return view('attendances.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:present,absent,late,half_day'],
            'notes' => ['nullable', 'string'],
        ]);

        $attendance->update($data);

        return redirect()->route('attendances.index')->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return back()->with('success', 'Attendance record deleted successfully.');
    }

    public function employeeReport(Employee $employee)
    {
        $attendances = $employee->attendances()
            ->orderBy('date', 'desc')
            ->paginate(30);

        return view('attendances.employee-report', compact('employee', 'attendances'));
    }
}
