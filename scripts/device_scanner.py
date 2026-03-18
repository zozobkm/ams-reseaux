import os
import re
import mysql.connector
from datetime import datetime

# Ta config SQL habituelle
DB_CONFIG = {
    'host': 'localhost',
    'user': 'forumuser',
    'password': 'forum123',
    'database': 'box'
}

def scan_network():
    # 1. On récupère la table ARP du système (Texte brut)
    stream = os.popen('arp -an')
    output = stream.read()

    # 2. TRAITEMENT DE CARACTÈRES (Analyse approfondie)
    # On cherche les patterns : (IP) at (MAC)
    devices_found = re.findall(r'\((\d+\.\d+\.\d+\.\d+)\) at ([0-9a-fA-F:]+)', output)

    try:
        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor()

        for ip, mac in devices_found:
            # On enregistre ou on met à jour chaque appareil 
            query = """
                INSERT INTO devices (ip_address, mac_address, last_seen)
                VALUES (%s, %s, %s)
                ON DUPLICATE KEY UPDATE ip_address=%s, last_seen=%s
            """
            cursor.execute(query, (ip, mac, datetime.now(), ip, datetime.now()))
        
        db.commit()
        db.close()
        print(f"[{datetime.now()}] Scan terminé : {len(devices_found)} appareils trouvés.")
    except Exception as e:
        print(f"Erreur SQL : {e}")

if __name__ == "__main__":
    scan_network()
