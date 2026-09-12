# CAVI-Alibori — Document de passation

**Hackathon** : IndabaX Bénin 2026 (thème Deep Learning)
**Dernière mise à jour** : 11 septembre 2026

---

## 1. Contexte

Le hackathon demande de partir d'un problème réel au Bénin et de proposer une solution IA utile, faisable en 72h et honnête sur ses limites. La pertinence des choix compte plus que la complexité technique.

## 2. Le problème & la cible

**Zone** : Alibori, extrême Nord du Bénin (zone agro-climatique la plus vulnérable aux poches de sécheresse).
**Public cible** : petits producteurs ruraux de l'Alibori (coton, céréales).

**Triple barrière** :
- **Textuelle** : 82,5 % de la population de l'Alibori est analphabète (INSAE / Le Matinal) → toute solution écrite/SMS/app visuelle est inapplicable sur le terrain.
- **Linguistique** : les bulletins météo sont en français, langue non maîtrisée par la majorité des ruraux.
- **Technique** : les alertes météo classiques restent théoriques (jargon scientifique), sans traduction en action concrète pour le champ.

**Enjeu** : moins de 4 % des terres agricoles de l'Alibori sont irriguées (RNA). Une poche de sécheresse non anticipée peut détruire 30 à 50 % des rendements d'une commune.

## 3. La solution en une phrase

Une plateforme qui convertit les prévisions de sécheresse en consignes agricoles concrètes, diffusées 100 % à l'oral en Bariba, Peulh et Dendi, via un numéro vert (SVI), les radios communautaires et un relais WhatsApp pour les coopératives — zéro smartphone requis.

## 4. Architecture — décisions actuelles

**Principe central** : séparation stricte extraction (IA) / décision (règles). L'IA ne décide jamais seule d'une consigne agricole ; elle structure l'information. Une table de règles auditée par l'ATDA prend la décision — argument de fiabilité face au jury, et système maintenable sans réentraînement.

**Pipeline** :
1. **Ingestion** — récupération des bulletins texte (Météo-Bénin / SAP-MR).
2. **Extraction** — un microservice **Python séparé** (FastAPI + `sentence-transformers`, modèle `paraphrase-multilingual-MiniLM-L12-v2`) classe le niveau de risque par similarité d'embeddings contre des phrases-ancres. C'est la seule brique qui justifie un vrai travail de deep learning pour le jury. La zone et la durée sont extraites par **regex côté Laravel** (assumé comme non-ML). Repli local (regex) si le microservice tombe ou met trop de temps à répondre.
3. **Décision** — table de règles strictes validées par des agronomes ATDA (`commune=X, risque=Y, durée=Z` → `action`).
4. **Génération audio** — **Slot-Filling** : assemblage dynamique de segments audio pré-enregistrés par un locuteur natif (pas de TTS génératif — corpus insuffisants en Bariba/Peulh/Dendi).
5. **Diffusion** — SVI (simulé en page web pour la démo), radios communautaires (export fichier + notification), chatbot WhatsApp (API officielle Meta Cloud, numéro de test).

**Pourquoi pas de TTS génératif ni d'app mobile comme canal principal** : quasi-absence de corpus pour ces langues (contrairement au Fongbé) ; dépendance à la 4G/data qui exclurait une bonne partie de la cible.

## 5. Stack technique

| Couche | Choix | Statut |
|---|---|---|
| Backend | Laravel 11+ | OBLIGATOIRE |
| Microservice ML | FastAPI + sentence-transformers | OBLIGATOIRE (le mécanisme), fournisseur/modèle remplaçable |
| Base de données (démo) | SQLite | OBLIGATOIRE |
| Base de données (prod) | MySQL/PostgreSQL | Facultatif — après hackathon |
| Admin / validation | Filament PHP | OBLIGATOIRE |
| Files d'attente | Laravel Queue (driver `database`) | OBLIGATOIRE |
| Scheduler | Laravel Task Scheduling | OBLIGATOIRE |
| Audio | FFmpeg (via `Symfony\Process`) | OBLIGATOIRE |
| Extraction NLP | Microservice Python + repli regex | Le repli est OBLIGATOIRE |
| Tests | PHPUnit | Facultatif mais recommandé |

**Scope de démo volontairement restreint** : une seule commune (Banikoara), un seul scénario météo (sécheresse sévère, 8-14 jours → paillage du sol), un seul canal (SVI simulé en page web — la couverture Bénin d'Africa's Talking n'est pas confirmée, un vrai numéro vert nécessite un partenariat opérateur hors scope 72h).

## 6. Schéma de données (cœur du système)

`communes`, `bulletins`, `variables_extraites`, `catalogue_actions`, `regles_decision`, `segments_audio`, `messages_generes`, `journaux_diffusion`, `relais_cooperatives`, `users` (Filament, rôles admin/agronome_atda/dev).

Chaque table correspond à une étape auditable du pipeline — traçabilité complète de `messages_generes` jusqu'à `bulletins`.

## 7. État d'avancement

- Projet Laravel créé, Filament installé (`^3.2`, flag `-W` nécessaire pour un conflit de versions).
- Toutes les migrations et modèles Eloquent écrits, `php artisan migrate` et `php artisan db:seed` fonctionnent (6 communes de l'Alibori + 2 règles de décision actives : `secheresse_severe` 8-14j, `secheresse_moderee` 1-7j).
  - *Piège rencontré* : la migration par défaut `create_users_table` avait disparu du dossier `database/migrations` (probablement écrasée en copiant les migrations custom) — recréée manuellement.
- `App\DataTransferObjects\ExtractionResult` (DTO immuable) créé.
- `App\Services\Extraction\NlpExtractorInterface` et `RegexFallbackExtractor` créés et **testés en tinker avec succès** (détection commune + niveau de risque + durée sur un bulletin de test).
- Fichiers audio (Slot-Filling) déjà disponibles en local.

## 8. Prochaines étapes (dans l'ordre)

1. `DecisionEngine` (logique pure, sans dépendance externe) — décision prise : si aucune règle ne matche, on log et on arrête le pipeline proprement (pas de statut supplémentaire pour le hackathon, sur-ingénierie au vu du scope 72h).
2. `ApplyDecisionRuleJob` (câblage extraction → décision → dispatch génération audio par langue).
3. Microservice Python (FastAPI + sentence-transformers) pour l'extraction par embeddings.
4. `AudioAssemblyService` (FFmpeg, Slot-Filling) — tester tôt la fluidité aux jonctions de segments sur le scénario unique de démo.
5. Panel Filament minimal (validation des règles par un profil "agronome").
6. Page Blade simulant l'appel SVI, qui joue le message généré.

## 9. Risques et points ouverts connus

- **Granularité réelle des bulletins Météo-Bénin non confirmée** (commune vs département) — tout le pipeline suppose une extraction par commune.
- **Antériorité possible non vérifiée** : le SPIAM (IDID-ONG, projet PARBCC) diffuse déjà des conseils agrométéo au Bénin — identifier la différenciation réelle (langues locales + SVI + relais WhatsApp) avant que le jury ne le découvre en Q&A.
- **Coût/partenariat du numéro vert non résolu** — problème de partenariat, pas technique, non finalisable en 72h.
- **Couverture réseau mobile par commune non chiffrée** — c'est cette donnée, pas l'alphabétisation, qui détermine si le SVI est utilisable sur le terrain.
- **Incertitude de la prévision météo non traitée** — pas de mécanisme de communication du niveau de confiance ni de gestion des faux positifs.
- **Métriques de succès absentes** — à définir avant la présentation (ex. taux d'appels menant à une action suivie).
- **Risque d'infrastructure (microservice Python)** : deux services à budget gratuit (Laravel + FastAPI) doublent le risque de mise en veille après inactivité pendant la démo. Le modèle `sentence-transformers` (~470 Mo) peut être limite en RAM sur un tier gratuit (souvent 512 Mo). Mitigation : timeout court côté Laravel (pas seulement repli sur erreur, aussi sur lenteur) + pré-chauffage des deux services avant la démo.
- **SQLite en démo** : gère mal les écritures concurrentes — acceptable pour une démo à un seul flux, pas pour un vrai pilote multi-commune.

## 10. Environnement de développement

- **OS** : Windows, terminal cmd.exe/PowerShell.
- **Éditeur** : VSCode (`code .` depuis la racine du projet).
- **Commandes clés** :
  ```
  cd C:\Users\PC\Documents\cavi-alibori
  php artisan migrate
  php artisan db:seed
  php artisan tinker
  php artisan test
  ```
- **Vérification rapide en tinker** :
  ```php
  $extractor = new App\Services\Extraction\RegexFallbackExtractor();
  $resultat = $extractor->extract("Commune de Banikoara : sécheresse sévère, 10 jours.");
  $resultat;
  ```
