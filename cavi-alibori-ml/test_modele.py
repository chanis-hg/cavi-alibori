import time
from sentence_transformers import SentenceTransformer

print("Chargement du modèle...")
debut = time.time()

modele = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')

duree = time.time() - debut
print(f"Modèle chargé en {duree:.1f} secondes")

ancre = "risque de sécheresse sévère"
bulletin = "Bulletin agrométéo — Commune de Banikoara : risque de sécheresse sévère estimé à 10 jours."

embeddings = modele.encode([ancre, bulletin])
from sentence_transformers.util import cos_sim
score = cos_sim(embeddings[0], embeddings[1])

print(f"Score de similarité : {score.item():.3f}")