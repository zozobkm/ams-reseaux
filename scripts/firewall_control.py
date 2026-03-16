import mysql.connector
import os
import datetime

# --- CONFIGURATION ---
DB_CONFIG = {
    'host': 'localhost',
    'user': 'votre_user',      # Utilise les mêmes que dans ton db.php
    'password': 'votre_password',
    'database': 'ams_reseaux'
}

# Ton interface LAN identifiée sur ta capture
LAN_INTERFACE = "eth1" 

def apply_security():
    now = datetime.datetime.now()
    # On fait correspondre les noms de jours avec ta base PHP
    jours_fr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']
    jour_actuel = jours_fr[now.weekday()]
    heure_actuelle = now.hour

    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        
        # 1. Vérification du planning horaire [cite: 114, 135]
        cursor.execute("SELECT statut FROM planning_acces WHERE jour = %s AND heure = %s", (jour_actuel, heure_actuelle))
        res = cursor.fetchone()
        is_blocked_time = (res and res[0] == 'bloque')

        # 2. Nettoyage préventif (pour éviter les règles en double)
        os.system(f"sudo iptables -D FORWARD -i {LAN_INTERFACE} -j DROP 2>/dev/null")

        # 3. Application du blocage [cite: 112]
        if is_blocked_time:
            os.system(f"sudo iptables -I FORWARD -i {LAN_INTERFACE} -j DROP")
            print(f"[{now}] SÉCURITÉ : Internet BLOQUÉ pour le LAN ({jour_actuel} {heure_actuelle}h)")
        else:
            print(f"[{now}] SÉCURITÉ : Internet AUTORISÉ")

        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Erreur script firewall : {e}")

if __name__ == "__main__":
    apply_security()
