import mysql.connector
import os
import datetime

# --- CONFIGURATION (Extraite de ton config.php) ---
DB_CONFIG = {
    'host': 'localhost',
    'user': 'forumuser',      # Identifiant de ton config.php
    'password': 'forum123',   # Mot de passe de ton config.php
    'database': 'box'         # Nom de la base de ton config.php
}

# Interface LAN identifiée sur ta capture
LAN_IFACE = "eth1" 

def apply_rules():
    try:
        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor()

        # 1. RÉCUPÉRATION DU PLANNING HORAIRE
        jours_fr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']
        now = datetime.datetime.now()
        jour_actuel = jours_fr[now.weekday()]
        heure_actuelle = now.hour

        cursor.execute("SELECT statut FROM planning_acces WHERE jour = %s AND heure = %s", (jour_actuel, heure_actuelle))
        res_time = cursor.fetchone()
        is_blocked_time = (res_time and res_time[0] == 'bloque')

        # 2. RÉCUPÉRATION DES MOTS-CLÉS
        cursor.execute("SELECT mot_cle FROM contenu_bloque")
        keywords = [row[0] for row in cursor.fetchall()]

        # 3. RÉCUPÉRATION DES SERVICES (Ping/Web)
        cursor.execute("SELECT service_name, est_actif FROM config_securite")
        services = dict(cursor.fetchall())

        # --- EXÉCUTION DES COMMANDES SYSTEME (Compatible ancienne version Python) ---
        # Nettoyage de la chaine de sécurité
        os.system("sudo iptables -F BOX_SECURITY 2>/dev/null || sudo iptables -N BOX_SECURITY")
        
        if is_blocked_time:
            # Blocage TOTAL si le carré est rouge
            cmd = "sudo iptables -A BOX_SECURITY -i {} -j DROP".format(LAN_IFACE)
            os.system(cmd)
            print("[{}] SÉCURITÉ : Accès TOTAL coupé (Planning)".format(now))
        else:
            # Filtrage par mots-clés (URL)
            for kw in keywords:
                cmd = "sudo iptables -A BOX_SECURITY -i {} -m string --string '{}' --algo bm -j DROP".format(LAN_IFACE, kw)
                os.system(cmd)
            
            # Blocage du PING si désactivé
            if services.get('ping') == 0:
                cmd = "sudo iptables -A BOX_SECURITY -i {} -p icmp -j DROP".format(LAN_IFACE)
                os.system(cmd)

        # Liaison avec le flux de transfert
        os.system("sudo iptables -C FORWARD -j BOX_SECURITY 2>/dev/null || sudo iptables -I FORWARD -j BOX_SECURITY")

        db.close()
    except Exception as e:
        print("Erreur script firewall: {}".format(e))

if __name__ == "__main__":
    apply_rules()
