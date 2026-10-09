# cavi-alibori-ml — microservice d'extraction par embeddings

Classe le niveau de risque d'un bulletin météo (`secheresse_severe`, `secheresse_moderee`, `risque_faible`) par similarité cosinus entre l'embedding du bulletin et des phrases-ancres.

- Modèle : `paraphrase-multilingual-MiniLM-L12-v2` (sentence-transformers), environ 470 Mo, téléchargé au premier lancement.
- La commune et la durée restent extraites par regex côté Laravel.
- Si ce service ne répond pas dans le délai (`CAVI_ML_TIMEOUT`, 3 s par défaut), Laravel bascule automatiquement sur le repli regex : la démo fonctionne sans lui.

## Lancer

    python -m venv venv
    venv\Scripts\activate          # Windows  (Linux/macOS : source venv/bin/activate)
    pip install -r requirements.txt
    uvicorn main:app --port 8001

Le port 8001 est volontaire : `php artisan serve` occupe déjà le 8000. L'URL se règle dans le `.env` de Laravel (`CAVI_ML_URL`).

## Tester

    curl -X POST http://127.0.0.1:8001/classifier -H "Content-Type: application/json" -d "{\"texte\": \"Banikoara : risque de sécheresse sévère sur 10 jours\"}"

Réponse attendue : `{"niveau_risque": "secheresse_severe", "confiance": 0.xx, "methode": "embeddings"}`.

## Limites

- Les phrases-ancres sont écrites à la main : à recalibrer sur de vrais bulletins Météo-Bénin.
- Le score de confiance n'est pas encore exploité par le moteur de règles.
