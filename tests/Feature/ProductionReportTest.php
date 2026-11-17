<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\ProductionReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionReportTest extends TestCase
{
    use RefreshDatabase;

    private function employee(string $code = 'BHR-109', string $first = 'Kebebush', string $last = 'Begna'): Employee
    {
        return Employee::create([
            'employee_code' => $code,
            'first_name'    => $first,
            'last_name'     => $last,
            'email'         => strtolower($code).'@erp.local',
            'department'    => 'Production',
            'position'      => 'Sorter',
            'base_salary'   => 10000,
            'hire_date'     => '2024-01-01',
            'status'        => 'active',
        ]);
    }

    public function test_production_user_submits_and_views_a_daily_report(): void
    {
        $first = $this->employee();
        $second = $this->employee('BHR-123', 'Zinash', 'Ejare');
        $user = User::factory()->create(['role' => 'production']);

        $this->actingAs($user)->get('/production-reports')->assertOk();
        $this->actingAs($user)->get('/production-reports/create')
            ->assertOk()
            ->assertSee('Kebebush Begna')
            ->assertSee('const initialRows', false)
            ->assertSee('pdGroups', false);

        $this->actingAs($user)->post('/production-reports', [
            'report_date'          => '2026-03-03',
            'week_no'              => '9',
            'coordinator_name'     => 'Firaol Debele',
            'coordinator_position' => 'Production Co-ordinator',
            'prepared_by'          => 'Haymanot Tekola',
            'prepared_date'        => '2026-03-03',
            'checked_by'           => 'Firaol Debele',
            'checked_date'         => '2026-03-03',
            'total_workers'        => 23,
            'working_hours'        => 8,
            'lines'                => [
                [
                    'employee_id'      => $first->id,
                    'group_code'       => 'G-1',
                    'material_code'    => '2023',
                    'material_name_am' => 'Kerkedi Shai',
                    'material_name_en' => 'Hibiscus Tea',
                    'work_type'        => 'Resorting',
                    'total_amount'     => 7,
                    'impurity'         => 0.7,
                    'remaining'        => 0,
                    'working_hours'    => 7.5,
                ],
                [
                    'employee_id'      => $second->id,
                    'group_code'       => 'G-1',
                    'material_code'    => '2023',
                    'material_name_am' => 'Kerkedi Shai',
                    'material_name_en' => 'Hibiscus Tea',
                    'work_type'        => 'Resorting',
                    'total_amount'     => 5,
                    'impurity'         => 0.8,
                    'remaining'        => 0,
                    'working_hours'    => 7.5,
                ],
            ],
        ])->assertRedirect();

        $report = ProductionReport::with('lines')->firstOrFail();
        $line = $report->lines->first();

        $this->assertSame('BHR-109', $line->employee_code);
        $this->assertSame('Kebebush Begna', $line->employee_name);
        $this->assertEquals(6.3, $line->clean);
        $this->assertEqualsWithDelta(0.84, $line->speed, 0.0001);
        $this->assertEqualsWithDelta(0.9, $line->yield_rate, 0.0001);
        $this->assertEquals(12, $report->total_amount);
        $this->assertEquals(1.5, $report->total_impurity);
        $this->assertEquals(10.5, $report->total_clean);
        $this->assertSame(2, $report->present_workers);
        $this->assertSame(21, $report->absent_workers);

        $this->actingAs($user)->get("/production-reports/{$report->id}")
            ->assertOk()
            ->assertSee('Daily Production Report week 9')
            ->assertSee('Kebebush Begna')
            ->assertSee('Hibiscus Tea')
            ->assertSee('Production Totals');
    }

    public function test_a_line_must_name_an_employee(): void
    {
        $user = User::factory()->create(['role' => 'production']);

        $this->actingAs($user)->post('/production-reports', [
            'report_date'   => '2026-03-03',
            'total_workers' => 23,
            'working_hours' => 8,
            'lines'         => [['total_amount' => 10]],
        ])->assertSessionHasErrors('lines.0.employee_id');

        $this->assertSame(0, ProductionReport::count());
    }

    public function test_cashier_cannot_open_reports(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)->get('/production-reports')->assertForbidden();
        $this->actingAs($cashier)->get('/production-reports/create')->assertForbidden();
    }

    public function test_inventory_can_view_but_not_submit(): void
    {
        $keeper = User::factory()->create(['role' => 'inventory']);

        $this->actingAs($keeper)->get('/production-reports')->assertOk();
        $this->actingAs($keeper)->get('/production-reports/create')->assertForbidden();
    }
}
