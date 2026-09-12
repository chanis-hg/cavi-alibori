# CAVI-Alibori — Démo

## Lancer la démo

    php artisan serve

Puis ouvrir :
- http://127.0.0.1:8000/svi         → pipeline live (bouton "Ingérer un bulletin")
- http://127.0.0.1:8000/admin       → panel ATDA (login: admin@cavi.bj / cavi2026)

## Scénario de démo

1. Cliquer "Ingérer un nouveau bulletin de test"
2. Le pipeline tourne : extraction → décision → audio
3. Écouter le message en Bariba
4. Montrer /admin/regle-decisions : règles validées par l'ATDA

## Architecture

- Laravel 11+ / Filament 3
- Extraction : regex (repli) + microservice Python (embeddings, optionnel)
- Décision : table de règles validées par l'ATDA
- Audio : slot-filling FFmpeg sur segments pré-enregistrés
- Diffusion : SVI simulé (page Blade) pour la démo

## Limites assumées

- Granularité des bulletins Météo-Bénin non confirmée (commune vs département)
- Couverture réseau mobile par commune non chiffrée
- Numéro vert : partenariat opérateur hors scope 72h
- Incertitude météo : pas de gestion du niveau de confiance