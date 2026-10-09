from fastapi import FastAPI
from pydantic import BaseModel
from sentence_transformers import SentenceTransformer, util

app = FastAPI()

# Chargé UNE SEULE FOIS au démarrage du serveur, pas à chaque requête.
modele = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')

# Phrases-ancres par niveau de risque — à affiner avec de vrais bulletins
# dès que tu en auras (cf. section 9 du doc, risque non résolu).
ANCRES = {
    "secheresse_severe": "risque de sécheresse sévère, absence de pluie prolongée",
    "secheresse_moderee": "risque de sécheresse modérée, déficit pluviométrique",
    "risque_faible": "conditions climatiques normales, pluviométrie suffisante",
}

embeddings_ancres = {
    niveau: modele.encode(phrase) for niveau, phrase in ANCRES.items()
}

class BulletinRequest(BaseModel):
    texte: str

@app.post("/classifier")
def classifier(requete: BulletinRequest):
    embedding_bulletin = modele.encode(requete.texte)

    scores = {
        niveau: util.cos_sim(embedding_bulletin, ancre).item()
        for niveau, ancre in embeddings_ancres.items()
    }

    meilleur_niveau = max(scores, key=scores.get)

    return {
        "niveau_risque": meilleur_niveau,
        "confiance": scores[meilleur_niveau],
        "methode": "embeddings",
    }