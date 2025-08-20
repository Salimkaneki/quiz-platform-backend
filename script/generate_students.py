import csv
import random
from datetime import datetime, timedelta

# Listes de prénoms et noms togolais
prenoms_masculins = [
    "Kafui", "Abalo", "Koffi", "Edem", "Selom", "Kodjo", "Kwame", "Prosper", 
    "Emmanuel", "Yao", "Mawuli", "Komla", "Kossi", "Kokou", "Mensah",
    "Agbeko", "Amegah", "Bodjona", "Hedidor", "Lawson", "Nukunu", "Quarshie"
]

prenoms_feminins = [
    "Linda", "Ama", "Nana", "Dziedzom", "Akosua", "Grace", "Comfort", 
    "Delali", "Adjoa", "Akaba", "Efua", "Afi", "Abla", "Akpene",
    "Amenyo", "Sena", "Dela", "Kukua", "Yawa", "Abena"
]

noms_famille = [
    "Mensah", "Adjaho", "Kokou", "Agbeko", "Tettey", "Nyaku", "Kpogo",
    "Akakpo", "Amegah", "Afeli", "Bodjona", "Dzeble", "Hedidor", "Kpakpo",
    "Lawson", "Nukunu", "Ofori", "Quarshie", "Tengey", "Adotey", "Baku",
    "Dzokoto", "Fiagbe", "Gado", "Honu", "Klu", "Kumah", "Lartey",
    "Mawuko", "Nartey", "Ocloo", "Plange", "Quaye", "Soglo", "Teye"
]

def generer_date_naissance():
    """Génère une date de naissance aléatoire entre 2000 et 2002"""
    start_date = datetime(2000, 1, 1)
    end_date = datetime(2002, 12, 31)
    
    time_between = end_date - start_date
    days_between = time_between.days
    random_days = random.randrange(days_between)
    
    return (start_date + timedelta(days=random_days)).strftime('%Y-%m-%d')

def generer_email(prenom, nom):
    """Génère un email à partir du prénom et nom"""
    return f"{prenom.lower()}.{nom.lower()}@example.com"

def generer_telephone():
    """Génère un numéro de téléphone togolais ou retourne None (vide)"""
    if random.choice([True, False, False]):  # 1/3 de chance d'être vide
        return ""
    
    # Numéros togolais commencent par +228
    numero = f"+2289{random.randint(0000000, 9999999):07d}"
    return numero

def generer_numero_etudiant(index):
    """Génère un numéro d'étudiant unique"""
    return f"ETU2024-{index:03d}"

def generer_csv_etudiants(nombre_etudiants=50, nom_fichier="students.csv"):
    """Génère un fichier CSV avec des étudiants"""
    
    # En-têtes du CSV
    headers = ['student_number', 'first_name', 'last_name', 'birth_date', 'email', 'phone', 'class_id']
    
    # Générer les données
    etudiants = []
    
    for i in range(1, nombre_etudiants + 1):
        # Choisir un prénom (masculin ou féminin)
        genre = random.choice(['M', 'F'])
        if genre == 'M':
            prenom = random.choice(prenoms_masculins)
        else:
            prenom = random.choice(prenoms_feminins)
        
        nom = random.choice(noms_famille)
        
        etudiant = {
            'student_number': generer_numero_etudiant(i),
            'first_name': prenom,
            'last_name': nom,
            'birth_date': generer_date_naissance(),
            'email': generer_email(prenom, nom),
            'phone': generer_telephone(),
            # 'class_id': random.randint(1)  # Classes de 1 à 3
            'class_id': 1  

        }
        
        etudiants.append(etudiant)
    
    # Écrire dans le fichier CSV
    with open(nom_fichier, 'w', newline='', encoding='utf-8') as fichier_csv:
        writer = csv.DictWriter(fichier_csv, fieldnames=headers)
        writer.writeheader()
        writer.writerows(etudiants)
    
    print(f"✅ Fichier '{nom_fichier}' généré avec {nombre_etudiants} étudiants!")
    return nom_fichier

def afficher_apercu(nom_fichier="students.csv", nb_lignes=5):
    """Affiche un aperçu du fichier généré"""
    print(f"\n📋 Aperçu de {nom_fichier} (premières {nb_lignes} lignes):")
    print("-" * 80)
    
    with open(nom_fichier, 'r', encoding='utf-8') as fichier:
        reader = csv.reader(fichier)
        for i, ligne in enumerate(reader):
            if i <= nb_lignes:
                print(" | ".join(ligne))
            else:
                break

# =================== UTILISATION ===================

if __name__ == "__main__":
    print("🎓 Générateur CSV d'étudiants togolais")
    print("=" * 50)
    
    # Paramètres personnalisables
    NOMBRE_ETUDIANTS = 30  # Changez ce nombre selon vos besoins
    NOM_FICHIER = "students_togo.csv"
    
    # Générer le fichier
    fichier_genere = generer_csv_etudiants(
        nombre_etudiants=NOMBRE_ETUDIANTS,
        nom_fichier=NOM_FICHIER
    )
    
    # Afficher un aperçu
    afficher_apercu(fichier_genere, 10)
    
    print(f"\n📁 Fichier sauvegardé : {fichier_genere}")
    print(f"📊 Total d'étudiants : {NOMBRE_ETUDIANTS}")

# =================== FONCTIONS BONUS ===================

def generer_avec_metadata(nombre_etudiants=20):
    """Version avancée avec des métadonnées JSON"""
    import json
    
    headers = ['student_number', 'first_name', 'last_name', 'birth_date', 'email', 'phone', 'class_id', 'metadata']
    
    quartiers_lome = [
        "Agoè", "Bè", "Tokoin", "Nyékonakpoè", "Adidogomé", "Kégué", 
        "Djidjolé", "Amadahomé", "Cacavéli", "Gbadago"
    ]
    
    etudiants = []
    
    for i in range(1, nombre_etudiants + 1):
        genre = random.choice(['M', 'F'])
        prenom = random.choice(prenoms_masculins if genre == 'M' else prenoms_feminins)
        nom = random.choice(noms_famille)
        
        # Métadonnées JSON
        metadata = {
            "gender": genre,
            "address": random.choice(quartiers_lome),
            "parent_phone": f"+2289{random.randint(1000000, 9999999):07d}",
            "emergency_contact": random.choice(noms_famille) + " " + random.choice(prenoms_masculins + prenoms_feminins)
        }
        
        etudiant = {
            'student_number': generer_numero_etudiant(i),
            'first_name': prenom,
            'last_name': nom,
            'birth_date': generer_date_naissance(),
            'email': generer_email(prenom, nom),
            'phone': generer_telephone(),
            'class_id': random.randint(1, 3),
            'metadata': json.dumps(metadata, ensure_ascii=False)
        }
        
        etudiants.append(etudiant)
    
    # Sauvegarder
    nom_fichier = "students_with_metadata.csv"
    with open(nom_fichier, 'w', newline='', encoding='utf-8') as fichier_csv:
        writer = csv.DictWriter(fichier_csv, fieldnames=headers)
        writer.writeheader()
        writer.writerows(etudiants)
    
    print(f"✅ Fichier avec métadonnées généré: {nom_fichier}")
    return nom_fichier

# Décommentez pour générer avec métadonnées :
# generer_avec_metadata(25)