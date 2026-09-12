<?php

namespace App\Jobs;

use App\Models\Bulletin;
use App\Services\Extraction\EmbeddingExtractor;
use App\Services\Extraction\RegexFallbackExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExtractVariablesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Bulletin $bulletin,
    ) {}

    public function handle(
        EmbeddingExtractor $extracteurPrincipal,
        RegexFallbackExtractor $extracteurRepli,
    ): void {
        try {
            $resultat = $extracteurPrincipal->extract($this->bulletin->texte_brut);
        } catch (Throwable $e) {
            // Timeout, service endormi, erreur HTTP — peu importe la cause
            // exacte, le repli doit toujours fonctionner. C'est le comportement
            // qu'on a délibérément choisi de câbler plutôt que de laisser
            // planter le job pendant une démo.
            Log::warning('EmbeddingExtractor indisponible, repli sur regex', [
                'bulletin_id' => $this->bulletin->id,
                'erreur' => $e->getMessage(),
            ]);

            $resultat = $extracteurRepli->extract($this->bulletin->texte_brut);
        }

        ApplyDecisionRuleJob::dispatchSync($this->bulletin->id, $resultat);
    }
}