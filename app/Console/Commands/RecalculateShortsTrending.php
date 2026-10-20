<?php

namespace App\Console\Commands;

use App\Models\Short;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateShortsTrending extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'shorts:recalculate-trending {--days=7 : Rolling window in days}';

    /**
     * The console command description.
     */
    protected $description = 'Recalculate trending engagement score for shorts using 7-day weighted window';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $since = now()->subDays($days);

        // Calculate scores from short_events table:
        // Score = (views * 1) + (likes * 4) + (shares * 6) + (clicks * 5) + (comments * 3)
        $scores = DB::table('short_events')
            ->select('short_id', DB::raw("
                (SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) * 1) +
                (SUM(CASE WHEN event_type = 'like' THEN 1 ELSE 0 END) * 4) +
                (SUM(CASE WHEN event_type = 'share' THEN 1 ELSE 0 END) * 6) +
                (SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) * 5) +
                (SUM(CASE WHEN event_type = 'comment' THEN 1 ELSE 0 END) * 3) AS score
            "))
            ->where('created_at', '>=', $since)
            ->groupBy('short_id')
            ->get();

        $updatedCount = 0;
        foreach ($scores as $row) {
            Short::where('id', $row->short_id)->update([
                'trending_score' => (float) $row->score,
            ]);
            $updatedCount++;
        }

        $this->info("Recalculated trending scores for {$updatedCount} short(s) over the last {$days} days.");

        return Command::SUCCESS;
    }
}
