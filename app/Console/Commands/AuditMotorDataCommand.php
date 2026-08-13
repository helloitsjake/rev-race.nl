<?php

namespace App\Console\Commands;

use App\Models\Motor;
use App\Services\MotorDataAuditor;
use Illuminate\Console\Command;

class AuditMotorDataCommand extends Command
{
    protected $signature = 'motors:audit-data {--dry-run : Alleen tonen, niets opslaan}';
    protected $description = 'Signaleert onwaarschijnlijke motordata (placeholder-merken, waarden buiten bereik, ontbrekende categorie) voor menselijke controle.';

    public function handle(MotorDataAuditor $auditor)
    {
        $findings = $auditor->auditAll();

        if (empty($findings)) {
            $this->info('Geen bevindingen — alle motoren vallen binnen de verwachte bereiken.');

            if (! $this->option('dry-run')) {
                Motor::query()->whereNotNull('data_flags')->update(['data_flags' => null]);
            }

            return self::SUCCESS;
        }

        $this->warn(count($findings).' motor(en) met een of meer bevindingen:');

        foreach ($findings as $row) {
            $motor = $row['motor'];
            $this->line("- [{$motor->id}] {$motor->label()} ({$motor->verification_status}):");
            foreach ($row['flags'] as $flag) {
                $this->line("    · {$flag}");
            }

            if (! $this->option('dry-run')) {
                $motor->data_flags = implode('; ', $row['flags']);
                if ($motor->verification_status !== 'verified') {
                    $motor->verification_status = 'flagged';
                }
                $motor->save();
            }
        }

        if (! $this->option('dry-run')) {
            $flaggedIds = collect($findings)->pluck('motor.id');
            Motor::query()
                ->whereNotIn('id', $flaggedIds)
                ->whereNotNull('data_flags')
                ->update(['data_flags' => null]);

            $this->info('Bevindingen opgeslagen in data_flags. Al geverifieerde motoren zijn niet teruggezet naar flagged.');
        } else {
            $this->comment('--dry-run: niets opgeslagen.');
        }

        return self::SUCCESS;
    }
}
