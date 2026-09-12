<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>CAVI-Alibori — SVI simulé</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
            color: #222;
        }

        h1 {
            color: #2c5f2d;
        }

        .subtitle {
            color: #666;
            margin-bottom: 2rem;
        }

        .msg {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #fafafa;
        }

        .meta {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 0.5rem;
        }

        .meta strong {
            color: #222;
        }

        audio {
            width: 100%;
            margin-top: 0.5rem;
        }

        .empty {
            color: #888;
            font-style: italic;
        }
    </style>
</head>

<body>
    <h1>SVI simulé — CAVI-Alibori</h1>
    <p class="subtitle">Simulation de l'appel vocal. Chaque message ci-dessous correspond à un bulletin ingéré et une règle ATDA appliquée.</p>

    @if($messages->isEmpty())
    <p class="empty">Aucun message généré pour l'instant. Lance l'ingestion d'un bulletin.</p>
    @else
    @foreach($messages as $m)
    <div class="msg">
        <div class="meta">
            <strong>Message #{{ $m->id }}</strong> —
            Bulletin #{{ $m->bulletin_id }} —
            Règle : {{ $m->regle->niveau_risque }} ({{ $m->regle->duree_min }}-{{ $m->regle->duree_max }}j)
            → action : <strong>{{ $m->regle->action->code }}</strong> —
            Langue : {{ $m->langue }} —
            Généré le {{ $m->genere_le->format('d/m/Y H:i') }}
        </div>
        <audio controls preload="none" src="{{ route('svi.play', $m) }}"></audio>
    </div>
    @endforeach
    @endif

    <form method="POST" action="{{ route('svi.ingest') }}" style="margin-bottom: 2rem;">
        @csrf
        <button type="submit" style="
            background: #2c5f2d;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 6px;
            cursor: pointer;
        ">
            Ingérer un nouveau bulletin de test
        </button>
        <span style="margin-left: 1rem; color: #666; font-size: 0.9rem;">
            Simule la réception d'un bulletin Météo-Bénin (Banikoara, sécheresse sévère, 12 jours)
        </span>
    </form>
</body>

</html>