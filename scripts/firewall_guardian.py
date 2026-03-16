import mysql.connector
import os
import datetime

# --- CONFIGURATION ---
DB_CONFIG = {
    'host': 'localhost',
    'user': 'ton_user',      # Identifiants de ton fichier db.php
    'password': 'ton_password',
    'database': 'ams_reseaux'
}

# Interface LAN identifiée sur ta capture
LAN_IFACE = "eth1" 

def apply_rules():
    try:
        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor()

        # 1. RÉCUPÉRATION DU PLANNING HORAIRE [cite: 116, 135]
        jours_fr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']
        now = datetime.datetime.now()
        jour_actuel = jours_fr[now.weekday()]
        heure_actuelle = now.hour

        cursor.execute("SELECT statut FROM planning_acces WHERE jour = %s AND heure = %s", (jour_actuel, heure_actuelle))
        res_time = cursor.fetchone()
        is_blocked_time = (res_time and res_time[0] == 'bloque')

        # 2. RÉCUPÉRATION DES MOTS-CLÉS (Tâche S6) [cite: 148]
        cursor.execute("SELECT mot_cle FROM contenu_bloque")
        keywords = [row[0] for row in cursor.fetchall()]

        # 3. RÉCUPÉRATION DES SERVICES (Ping/Web) 
        cursor.execute("SELECT service_name, est_actif FROM config_securite")
        services = dict(cursor.fetchall())

        # --- EXÉCUTION DES COMMANDES SYSTEME ---
        # On commence par nettoyer les anciennes règles de sécurité pour ne pas saturer
        os.system("sudo iptables -F BOX_SECURITY 2>/dev/null || sudo iptables -N BOX_SECURITY")
        
        # Règle pour le planning horaire [cite: 112]
        if is_blocked_time:
            os.system(f"sudo iptables -A BOX_SECURITY -i {LAN_IFACE} -j DROP")
            print(f"[{now}] SÉCURITÉ : Accès TOTAL coupé (Planning)")
        else:
            # Règle pour les mots-clés (Filtrage URL) [cite: 103, 152]
            for kw in keywords:
                os.system(f"sudo iptables -A BOX_SECURITY -i {LAN_IFACE} -m string --string '{kw}' --algo bm -j DROP")
            
            # Blocage du PING si désactivé 
            if services.get('ping') == 0:
                os.system(f"sudo iptables -A BOX_SECURITY -i {LAN_IFACE} -p icmp -j DROP")

        # On s'assure que la chaine BOX_SECURITY est bien branchée au flux principal
        os.system("sudo iptables -C FORWARD -j BOX_SECURITY 2>/dev/null || sudo iptables -I FORWARD -j BOX_SECURITY")

        db.close()
    except Exception as e:
        print(f"Erreur script firewall: {e}")

if __name__ == "__main__":
    apply_rules()
