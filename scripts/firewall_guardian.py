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

LAN_IFACE = "eth1" # Interface cote clients

def init_traffic_control():
    """Initialise la limitation de debit (TC) au demarrage"""
    os.system("sudo tc qdisc del dev {} root 2>/dev/null".format(LAN_IFACE))
    os.system("sudo tc qdisc add dev {} root handle 1: htb default 10".format(LAN_IFACE))
    # Trafic normal (100 Mbps)
    os.system("sudo tc class add dev {} parent 1: classid 1:10 htb rate 100mbit".format(LAN_IFACE))
    # Trafic limite (1 Mbps) 
    os.system("sudo tc class add dev {} parent 1: classid 1:20 htb rate 1mbit".format(LAN_IFACE))

def apply_all_rules():
    try:
        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor(dictionary=True)

        # --- 1. SÉCURITÉ GLOBALE ---
        jours_fr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']
        now = datetime.datetime.now()
        jour_actuel = jours_fr[now.weekday()]
        heure_actuelle = now.hour

        # Planning horaire
        cursor.execute("SELECT statut FROM planning_acces WHERE jour = %s AND heure = %s", (jour_actuel, heure_actuelle))
        res_time = cursor.fetchone()
        is_blocked_time = (res_time and res_time['statut'] == 'bloque')

        # Nettoyage des chaines iptables
        os.system("sudo iptables -F BOX_SECURITY 2>/dev/null || sudo iptables -N BOX_SECURITY")
        
        if is_blocked_time:
            os.system("sudo iptables -A BOX_SECURITY -i {} -j DROP".format(LAN_IFACE))
            print("[{}] SÉCURITÉ : Accès TOTAL coupé (Planning)".format(now))
        else:
            # Filtrage par mots-clés
            cursor.execute("SELECT mot_cle FROM contenu_bloque")
            for row in cursor.fetchall():
                os.system("sudo iptables -A BOX_SECURITY -i {} -m string --string '{}' --algo bm -j DROP".format(LAN_IFACE, row['mot_cle']))
            
            # --- 2. SÉCURITÉ INDIVIDUELLE (MAC & DÉBIT) ---
            cursor.execute("SELECT mac_address, statut_debit FROM devices")
            for dev in cursor.fetchall():
                mac = dev['mac_address']
                statut = dev['statut_debit']
                
                if statut == 'alerte':
                    # Blocage individuel par MAC
                    os.system("sudo iptables -A BOX_SECURITY -m mac --mac-source {} -j DROP".format(mac))
                elif statut == 'limite':
                    # Bridage individuel a 1 Mbps
                    os.system("sudo tc filter add dev {} protocol ip parent 1: prio 1 u32 match ether src {} flowid 1:20 2>/dev/null".format(LAN_IFACE, mac))

        # Application finale des regles
        os.system("sudo iptables -C FORWARD -j BOX_SECURITY 2>/dev/null || sudo iptables -I FORWARD -j BOX_SECURITY")

        db.close()
    except Exception as e:
        print("Erreur Gardien : {}".format(e))

if __name__ == "__main__":
    print("Activation du Gardien (Planning + Mots-cles + MAC Limiter)...")
    init_traffic_control()
    while True:
        apply_all_rules()
        time.sleep(10) # Mise a jour toutes les 10 secondes
