from fastapi import FastAPI, HTTPException, status
import pymysql
from pydantic import BaseModel
from typing import List, Optional

# 1. CONFIGURATION DE LA CONNEXION MARIADB
DB_CONFIG = {
    "host": "localhost",
    "port": 3306,
    "user": "chambre_user",
    "password": "ton_mot_de_passe_securise",
    "database": "chambredescles_db",
    "cursorclass": pymysql.cursors.DictCursor  # Permet de récupérer les lignes sous forme de dict {colonne: valeur}
}

def get_db_connection():
    return pymysql.connect(**DB_CONFIG)

# 2. SCHÉMAS DE VALIDATION (Pydantic)
class ChambreBase(BaseModel):
    nom: str
    prix_nuit: float
    capacite: int = 2
    description: Optional[str] = None

class ChambreResponse(ChambreBase):
    id: int

app = FastAPI(title="API Chambres d'Hôte - SQL Edition")

# 4. ENDPOINTS EN SQL PUR

# Créer une chambre (INSERT)
@app.post("/chambres/", response_model=ChambreResponse, status_code=status.HTTP_201_CREATED)
def create_chambre(chambre: ChambreBase):
    connection = get_db_connection()
    try:
        with connection.cursor() as cursor:
            sql = """
                INSERT INTO chambres (nom, prix_nuit, capacite, description) 
                VALUES (%s, %s, %s, %s)
            """
            cursor.execute(sql, (chambre.nom, chambre.prix_nuit, chambre.capacite, chambre.description))
            chambre_id = cursor.lastrowid  # Récupère l'ID auto-incrémenté
        connection.commit()  # On valide la transaction
        
        return {**chambre.model_dump(), "id": chambre_id}
    except Exception as e:
        connection.rollback()
        raise HTTPException(status_code=500, detail=f"Erreur BDD : {str(e)}")
    finally:
        connection.close()

# Lire toutes les chambres (SELECT)
@app.get("/chambres/", response_model=List[ChambreResponse])
def get_all_chambres():
    connection = get_db_connection()
    try:
        with connection.cursor() as cursor:
            cursor.execute("SELECT id, nom, prix_nuit, capacite, description FROM chambres")
            result = cursor.fetchall()
        return result
    finally:
        connection.close()

# Lire une chambre par son ID (SELECT ... WHERE)
@app.get("/chambres/{chambre_id}", response_model=ChambreResponse)
def get_chambre(chambre_id: int):
    connection = get_db_connection()
    try:
        with connection.cursor() as cursor:
            sql = "SELECT id, nom, prix_nuit, capacite, description FROM chambres WHERE id = %s"
            cursor.execute(sql, (chambre_id,))
            chambre = cursor.fetchone()
            
        if not chambre:
            raise HTTPException(status_code=404, detail="Chambre introuvable")
        return chambre
    finally:
        connection.close()

# Supprimer une chambre (DELETE)
@app.delete("/chambres/{chambre_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_chambre(chambre_id: int):
    connection = get_db_connection()
    try:
        with connection.cursor() as cursor:
            # On vérifie d'abord si elle existe
            cursor.execute("SELECT id FROM chambres WHERE id = %s", (chambre_id,))
            if not cursor.fetchone():
                raise HTTPException(status_code=404, detail="Chambre introuvable")
                
            # Suppression
            cursor.execute("DELETE FROM chambres WHERE WHERE id = %s", (chambre_id,))
        connection.commit()
        return None
    except HTTPException:
        raise
    except Exception as e:
        connection.rollback()
        raise HTTPException(status_code=500, detail=f"Erreur BDD : {str(e)}")
    finally:
        connection.close()