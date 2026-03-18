import mysql.connector
import os
import datetime
import time

# --- CONFIGURATION ---
DB_CONFIG = {
    'host': 'localhost',
    'user': 'forumuser',      
    'password': 'forum123',   
    'database': 'box'        
}

LAN_IFACE = "eth1" 

def init_traffic_control():
    """Initialise la hiérarchie de classes TC au démarrage"""
    # Nettoyage initial
    os.system(f"sudo tc qdisc del dev {LAN_IFACE} root 2>/dev/null")
    # Création du gestionnaire de file d'attente (HTB)
    os.system(f"sudo tc qdisc add dev {LAN_IFACE} root handle 1: htb default 10")
    # Trafic normal (100 Mbps)
    os.system(f"sudo tc class add dev {LAN_IFACE} parent 1: classid 1:10 htb rate 100mbit")
    # Trafic limité à 1 Mbps 
    os.system(f"sudo tc class add dev {LAN_IFACE} parent 1: classid 1:20 htb rate 1mbit")

def apply_all_rules():
    try:
        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor(dictionary=True)

        # 1. RÉCUPÉRATION DES DONNÉES GLOBALES (Planning & Services)
        jours_fr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']
        now = datetime.datetime.now()
        jour_actuel = jours_fr[now.weekday()]
        heure_actuelle = now.hour

        # Vérification du planning
        cursor.execute("SELECT statut FROM planning_acces WHERE jour = %s AND heure = %s", (jour_actuel, heure_actuelle))
        res_time = cursor.fetchone()
        is_blocked_time = (res_time and res_time['statut'] == 'bloque')

        # Vérification des services (Ping/Web)
        cursor.execute("SELECT service_name, est_actif FROM config_securite")
        services = {row['service_name']: row['est_actif'] for row in cursor.fetchall()}

        # 2. RÉINITIALISATION DES RÈGLES IPTABLES
        os.system("sudo iptables -F BOX_SECURITY 2>/dev/null || sudo iptables -N BOX_SECURITY")
        
        # Nettoyage des filtres TC existants avant de les reconstruire
        os.system(f"sudo tc filter del dev {LAN_IFACE} parent 1: 2>/dev/null")

        if is_blocked_time:
            # Blocage par planning
            os.system(f"sudo iptables -A BOX_SECURITY -i {LAN_IFACE} -j DROP")
            print(f"[{now}] SÉCURITÉ : Accès TOTAL coupé par le planning.")
        else:
            # Filtrage par mots-clés
            cursor.execute("SELECT mot_cle FROM contenu_bloque")
            for row in cursor.fetchall():
                os.system(f"sudo iptables -A BOX_SECURITY -i {LAN_IFACE} -m string --string '{row['mot_cle']}' --algo bm -j DROP")
            
            # Blocage du PING si désactivé
            if services.get('ping') == 0:
                os.system(f"sudo iptables -A BOX_SECURITY -i {LAN_IFACE} -p icmp -j DROP")

            # 3. FILTRAGE INDIVIDUEL ET BRIDAGE 
            cursor.execute("SELECT ip_address, mac_address, statut_debit FROM devices")
            for dev in cursor.fetchall():
                mac = dev['mac_address']
                ip = dev['ip_address']
                statut = dev['statut_debit']
                
                if statut == 'alerte':
                    # Blocage total par MAC
                    os.system(f"sudo iptables -A BOX_SECURITY -m mac --mac-source {mac} -j DROP")
                elif statut == 'limite':
                    # BRIDAGE RÉEL : On cible l'IP de destination pour brider le DOWNLOAD
                    os.system(f"sudo tc filter add dev {LAN_IFACE} protocol ip parent 1: prio 1 u32 match ip dst {ip} flowid 1:20")
                    print(f"[LIMITÉ] Application du bridage 1 Mbps pour l'IP : {ip}")

        # 4. LIAISON DES CHAÎNES SYSTÈME
        os.system("sudo iptables -C FORWARD -j BOX_SECURITY 2>/dev/null || sudo iptables -I FORWARD -j BOX_SECURITY")
        os.system("sudo iptables -C INPUT -j BOX_SECURITY 2>/dev/null || sudo iptables -I INPUT -j BOX_SECURITY")

        db.close()
    except Exception as e:
        print(f"Erreur Gardien : {e}")

if __name__ == "__main__":
    print("Démarrage du Gardien de la CeriBox...")
    init_traffic_control()
    while True:
        apply_all_rules()
        time.sleep(10) # Mise à jour toutes les 10 secondes
