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
    os.system("sudo tc qdisc del dev {} root 2>/dev/null".format(LAN_IFACE))
    os.system("sudo tc qdisc add dev {} root handle 1: htb default 10".format(LAN_IFACE))
    os.system("sudo tc class add dev {} parent 1: classid 1:10 htb rate 100mbit".format(LAN_IFACE))
    os.system("sudo tc class add dev {} parent 1: classid 1:20 htb rate 1mbit".format(LAN_IFACE))

def apply_all_rules():
    try:
        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor(dictionary=True)

        # 1. RÉCUPÉRATION DU PLANNING ET DES SERVICES
        jours_fr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']
        now = datetime.datetime.now()
        jour_actuel = jours_fr[now.weekday()]
        heure_actuelle = now.hour

        cursor.execute("SELECT statut FROM planning_acces WHERE jour = %s AND heure = %s", (jour_actuel, heure_actuelle))
        res_time = cursor.fetchone()
        is_blocked_time = (res_time and res_time['statut'] == 'bloque')

        cursor.execute("SELECT service_name, est_actif FROM config_securite")
        services = {row['service_name']: row['est_actif'] for row in cursor.fetchall()}

        # 2. NETTOYAGE IPTABLES ET TC
        os.system("sudo iptables -F BOX_SECURITY 2>/dev/null || sudo iptables -N BOX_SECURITY")
        os.system("sudo tc filter del dev {} parent 1: 2>/dev/null".format(LAN_IFACE))

        if is_blocked_time:
            os.system("sudo iptables -A BOX_SECURITY -i {} -j DROP".format(LAN_IFACE))
            print("[{}] SÉCURITÉ : Accès TOTAL coupé par le planning.".format(now))
        else:
            # Filtrage par mots-clés
            cursor.execute("SELECT mot_cle FROM contenu_bloque")
            for row in cursor.fetchall():
                os.system("sudo iptables -A BOX_SECURITY -i {} -m string --string '{}' --algo bm -j DROP".format(LAN_IFACE, row['mot_cle']))
            
            # Blocage du PING
            if services.get('ping') == 0:
                os.system("sudo iptables -A BOX_SECURITY -i {} -p icmp -j DROP".format(LAN_IFACE))

            # 3. FILTRAGE INDIVIDUEL ET BRIDAGE 
            cursor.execute("SELECT ip_address, mac_address, statut_debit FROM devices")
            for dev in cursor.fetchall():
                mac = dev['mac_address']
                ip = dev['ip_address']
                statut = dev['statut_debit']
                
                if statut == 'alerte':
                    os.system("sudo iptables -A BOX_SECURITY -m mac --mac-source {} -j DROP".format(mac))
                elif statut == 'limite':
                    # Utilisation de l'IP de destination pour brider le download
                    os.system("sudo tc filter add dev {} protocol ip parent 1: prio 1 u32 match ip dst {} flowid 1:20".format(LAN_IFACE, ip))
                    print("[LIMITÉ] Application du bridage 1 Mbps pour l'IP : {}".format(ip))

        # 4. LIAISON SYSTÈME
        os.system("sudo iptables -C FORWARD -j BOX_SECURITY 2>/dev/null || sudo iptables -I FORWARD -j BOX_SECURITY")
        os.system("sudo iptables -C INPUT -j BOX_SECURITY 2>/dev/null || sudo iptables -I INPUT -j BOX_SECURITY")

        db.close()
    except Exception as e:
        print("Erreur Gardien : {}".format(e))

if __name__ == "__main__":
    print("Démarrage du Gardien de la CeriBox...")
    init_traffic_control()
    while True:
        apply_all_rules()
        time.sleep(10)
