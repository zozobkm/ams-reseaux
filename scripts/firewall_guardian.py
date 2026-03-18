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

# Interface LAN cote clients (192.168.10.x)
LAN_IFACE = "eth1" 

def init_traffic_control():
    """Initialise le Traffic Control pour le bridage (Tache S7)"""
    os.system("sudo tc qdisc del dev {} root 2>/dev/null".format(LAN_IFACE))
    os.system("sudo tc qdisc add dev {} root handle 1: htb default 10".format(LAN_IFACE))
    # Classe 1:10 : Trafic normal (100 Mbps)
    os.system("sudo tc class add dev {} parent 1: classid 1:10 htb rate 100mbit".format(LAN_IFACE))
    # Classe 1:20 : Trafic limite (1 Mbps)
    os.system("sudo tc class add dev {} parent 1: classid 1:20 htb rate 1mbit".format(LAN_IFACE))

def apply_all_rules():
    try:
        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor(dictionary=True)

        # --- 1. SÉCURITÉ GLOBALE (Planning & Mots-cles) ---
        jours_fr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']
        now = datetime.datetime.now()
        jour_actuel = jours_fr[now.weekday()]
        heure_actuelle = now.hour

        cursor.execute("SELECT statut FROM planning_acces WHERE jour = %s AND heure = %s", (jour_actuel, heure_actuelle))
        res_time = cursor.fetchone()
        is_blocked_time = (res_time and res_time['statut'] == 'bloque')

        # Nettoyage et creation de la chaine personnalisee
        os.system("sudo iptables -F BOX_SECURITY 2>/dev/null || sudo iptables -N BOX_SECURITY")
        
        if is_blocked_time:
            # Blocage TOTAL par planning
            os.system("sudo iptables -A BOX_SECURITY -i {} -j DROP".format(LAN_IFACE))
            print("[{}] SECURITE : Acces TOTAL coupe (Planning)".format(now))
        else:
            # Filtrage par mots-clés (URL)
            cursor.execute("SELECT mot_cle FROM contenu_bloque")
            for row in cursor.fetchall():
                os.system("sudo iptables -A BOX_SECURITY -i {} -m string --string '{}' --algo bm -j DROP".format(LAN_IFACE, row['mot_cle']))
            
            # --- 2. SÉCURITÉ INDIVIDUELLE (MAC & DÉBIT - Tache S7) ---
            cursor.execute("SELECT mac_address, statut_debit FROM devices")
            devices = cursor.fetchall()
            
            # On nettoie les filtres TC existants pour ne pas les accumuler
            os.system("sudo tc filter del dev {} parent 1: 2>/dev/null".format(LAN_IFACE))
            
            for dev in devices:
                mac = dev['mac_address']
                statut = dev['statut_debit']
                
                if statut == 'alerte':
                    # Blocage individuel par adresse MAC
                    os.system("sudo iptables -A BOX_SECURITY -m mac --mac-source {} -j DROP".format(mac))
                    print("[ALERTE] MAC bloquee : {}".format(mac))
                elif statut == 'limite':
                    # Bridage individuel a 1 Mbps
                    os.system("sudo tc filter add dev {} protocol ip parent 1: prio 1 u32 match ether src {} flowid 1:20".format(LAN_IFACE, mac))
                    print("[LIMITE] MAC bridee : {}".format(mac))

        # --- 3. LIAISON AVEC LES CHAINES SYSTÈME ---
        # Liaison FORWARD (Trafic traversant vers Internet)
        os.system("sudo iptables -C FORWARD -j BOX_SECURITY 2>/dev/null || sudo iptables -I FORWARD -j BOX_SECURITY")
        
        # Liaison INPUT (Trafic s'arrêtant sur la Box - ex: PING 192.168.10.1)
        os.system("sudo iptables -C INPUT -j BOX_SECURITY 2>/dev/null || sudo iptables -I INPUT -j BOX_SECURITY")

        db.close()
    except Exception as e:
        # Sans accents pour eviter l'erreur ASCII
        print("Erreur Gardien : {}".format(e))

if __name__ == "__main__":
    print("Demarrage du Gardien (Planning + Mots-cles + MAC Control)...")
    init_traffic_control()
    while True:
        apply_all_rules()
        time.sleep(10)
