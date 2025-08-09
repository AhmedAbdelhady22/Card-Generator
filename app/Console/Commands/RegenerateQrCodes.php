<?php

namespace App\Console\Commands;

use App\Models\Card;
use Illuminate\Console\Command;

class RegenerateQrCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cards:regenerate-qr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate QR codes for all cards';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cards = Card::all();
        
        if ($cards->isEmpty()) {
            $this->info('No cards found.');
            return;
        }

        $this->info("Found {$cards->count()} cards. Regenerating QR codes...");
        
        $bar = $this->output->createProgressBar($cards->count());
        $bar->start();

        foreach ($cards as $card) {
            try {
                $card->generateQrCode();
                $this->line("\n✅ Regenerated QR for card {$card->id} - {$card->name}");
            } catch (\Exception $e) {
                $this->line("\n❌ Failed to regenerate QR for card {$card->id}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('QR code regeneration completed!');
    }
}
