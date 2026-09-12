# CAVI-Alibori

**Conversion de bulletins météo en consignes agricoles vocales, en langues locales (Bariba, Peulh, Dendi), pour les producteurs de l'Alibori (Bénin).**

Hackathon IndabaX Bénin 2026 — thème Deep Learning.

---

## Le problème

Dans l'Alibori (Nord-Est du Bénin) :
- **82 %** de la population est analphabète (INSAE).
- Les bulletins météo sont en **français écrit**, format technique.
- **Moins de 4 %** des terres agricoles sont irriguées (RNA).
- Une poche de sécheresse non anticipée peut détruire **30 à 50 %** des rendements d'une commune.

**Résultat** : une information météo existe, mais elle n'atteint pas ceux qui en ont besoin, sous une forme qu'ils peuvent utiliser.

---

## La solution

Une plateforme qui :

1. **Ingère** les bulletins météo (texte brut).
2. **Extrait** par IA la commune, le niveau de risque, la durée.
3. **Décide** via une table de règles validées par des agronomes ATDA.
4. **Génère** un message audio par assemblage de segments pré-enregistrés (slot-filling).
5. **Diffuse** via SVI (numéro vert), radios communautaires et WhatsApp coopératives.

**Principe central** : l'IA ne décide jamais seule. Elle structure l'information ; une règle auditée par l'ATDA prend la décision.

---

## Démo

**Périmètre de démo** : une commune (Banikoara), un scénario (sécheresse sévère 8-14j → paillage du sol), une langue (Bariba), un canal (SVI simulé en page web).

### Lancer la démo

    # 1. Cloner
    git clone https://github.com/TON_PSEUDO/cavi-alibori.git
    cd cavi-alibori

    # 2. Installer les dépendances PHP
    composer install

    # 3. Copier et configurer l'environnement
    cp .env.example .env
    php artisan key:generate

    # 4. Créer la base SQLite
    type nul > database\database.sqlite
    php artisan migrate
    php artisan db:seed

    # 5. Lancer le serveur
    php artisan serve

### Accéder

- **Pipeline live (SVI simulé)** : http://127.0.0.1:8000/svi
  - Cliquer « Ingérer un nouveau bulletin de test » → le pipeline tourne → cliquer play sur le lecteur audio.
- **Panel ATDA** : http://127.0.0.1:8000/admin
  - Login : `admin@cavi.bj` / `cavi2026`
  - Voir « Règles ATDA » dans le menu.

---

## Prérequis

- **PHP 8.2+** avec extensions `sqlite3`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- **Composer 2+**
- **FFmpeg** dans le PATH (obligatoire pour la génération audio)
  - Windows : https://github.com/BtbN/FFmpeg-Builds/releases → `ffmpeg-master-latest-win64-gpl.zip`
  - Linux : `sudo apt install ffmpeg`
  - macOS : `brew install ffmpeg`
- **Node.js 18+** (optionnel, si build front)

Vérifier FFmpeg :

    ffmpeg -version

---

## Architecture

    Bulletin (texte brut)
        ↓
    Extraction (regex, repli)          ← microservice Python optionnel (embeddings)
        ↓
    DecisionEngine (règles ATDA)        ← table `regles_decision` validée par un agronome
        ↓
    AudioAssemblyService (slot-filling) ← FFmpeg, concaténation de segments pré-enregistrés
        ↓
    MessageGenere (chemin_fichier_final)
        ↓
    Diffusion (SVI simulé pour la démo) ← WhatsApp / radio en vision

Détails complets dans `docs/architecture-technique.pdf` et `docs/analyse-marche.pdf`.

---

## Stack

| Couche | Choix |
|---|---|
| Backend | Laravel 11+ |
| Admin | Filament 3 |
| Base (démo) | SQLite |
| Base (prod) | MySQL / PostgreSQL |
| Files d'attente | Laravel Queue (driver `database`) |
| Scheduler | Laravel Task Scheduling |
| Audio | FFmpeg via `Symfony\Process` |
| Extraction NLP | Regex (repli) + microservice Python (embeddings, optionnel) |
| Tests | PHPUnit |

---

## Structure du projet

    app/
      Console/Commands/         Commandes Artisan (ingestion bulletins)
      DataTransferObjects/      ExtractionResult (DTO immuable)
      Jobs/                     ExtractVariablesJob, ApplyDecisionRuleJob
      Models/                   Bulletin, Commune, RegleDecision, SegmentAudio, MessageGenere...
      Services/
        Audio/                  AudioAssemblyService (FFmpeg)
        Decision/               DecisionEngine (logique pure)
        Extraction/             NlpExtractorInterface, RegexFallbackExtractor, EmbeddingExtractor
    database/
      migrations/               Schéma complet
      seeders/                  Données de démo (communes, règles, users)
    resources/views/svi/        Page SVI simulée
    routes/web.php              Routes /svi, /svi/ingest, /admin

---

## État d'avancement (livrable hackathon)

### Fait et fonctionnel

- [x] Pipeline complet : bulletin → extraction → décision → audio → message en base
- [x] Page SVI simulée avec ingestion live
- [x] Panel Filament avec règles ATDA traçables
- [x] FFmpeg installé et utilisé pour la concaténation audio
- [x] Traçabilité complète (logs + BD)

### Feuille de route post-hackathon

- [ ] Microservice Python d'extraction par embeddings (installé, non branché en démo)
- [ ] Diffusion WhatsApp réelle (Meta Cloud API)
- [ ] Numéro vert réel (partenariat opérateur, hors scope 72h)
- [ ] Multilangue complet (Peulh, Dendi) — nécessite enregistrements
- [ ] Migration MySQL/PostgreSQL
- [ ] Tests PHPUnit exhaustifs
- [ ] Métriques de succès (taux d'appels menant à une action suivie)

---

## Limites assumées

- **Granularité des bulletins Météo-Bénin non confirmée** (commune vs département) — tout le pipeline suppose une extraction par commune.
- **Couverture réseau mobile par commune non chiffrée** — c'est cette donnée, pas l'alphabétisation, qui détermine si le SVI est utilisable.
- **Coût / partenariat du numéro vert non résolu** — problème de partenariat, pas technique.
- **Incertitude météo non traitée** — pas de mécanisme de communication du niveau de confiance ni de gestion des faux positifs.
- **SQLite en démo** — gère mal les écritures concurrentes ; acceptable pour un flux unique, pas pour un pilote multi-commune.

---

## Équipe

- **Ismail AGOHOUNDJE**
- **Souraka HAMIDA**
- **Gaïus Chanis HONTONWAKOU**
- **Salem MIGAN**

---

## Licence

MIT — voir `LICENSE`.
