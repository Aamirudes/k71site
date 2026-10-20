<?php

namespace App\Console\Commands;

use App\Models\Short;
use App\Models\ShortBoost;
use Illuminate\Console\Command;

class ExpireShortsBoosts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'shorts:expire-boosts';

    /**
     * The console command description.
     */
    protected $description = 'Expire finished shorts boosts and synchronize short boost flags';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();

        $expiredBoosts = ShortBoost::where('status', 'active')
            ->where('ends_at', '<=', $now)
            ->get();

        $shortIdsToCheck = [];

        foreach ($expiredBoosts as $boost) {
            $boost->update(['status' => 'expired']);
            $shortIdsToCheck[] = $boost->short_id;
        }

        $shortIdsToCheck = array_unique($shortIdsToCheck);
        $deactivatedCount = 0;

        foreach ($shortIdsToCheck as $shortId) {
            $hasOtherActiveBoost = ShortBoost::where('short_id', $shortId)
                ->where('status', 'active')
                ->where('ends_at', '>', $now)
                ->exists();

            if (!$hasOtherActiveBoost) {
                Short::where('id', $shortId)->update([
                    'is_boosted' => false,
                    'boost_type' => null,
                ]);
                $deactivatedCount++;
            }
        }

        $this->info("Expired {$expiredBoosts->count()} boost(s). Synchronized {$deactivatedCount} short(s).");

        return Command::SUCCESS;
    }
}
