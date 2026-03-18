import os
import re
import mysql.connector
from datetime import datetime

# Configuration extraite de ton config.php
DB_CONFIG = {
    'host': 'localhost',
    'user': 'forumuser',
    'password': 'forum123',
    'database': 'box'
}

def scan():
    # Lecture de la table ARP du système
    output = os.popen('arp -an').read()
    
    # TRAITEMENT DE CARACTÈRES : Extraction IP et MAC
    devices_found = re.findall(r'\((\d+\.\d+\.\d+\.\d+)\) at ([0-9a-fA-F:]+)', output)

    try:
        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor()

        for ip, mac in devices_found:
            query = """
                INSERT INTO devices (ip_address, mac_address, last_seen)
                VALUES (%s, %s, %s)
                ON DUPLICATE KEY UPDATE ip_address=%s, last_seen=%s
            """
            cursor.execute(query, (ip, mac, datetime.now(), ip, datetime.now()))
        
        db.commit()
        db.close()
        # Correction de la syntaxe pour les anciennes versions de Python
        print("[{}] Scan terminé : {} appareils trouvés.".format(datetime.now(), len(devices_found)))
    except Exception as e:
        print("Erreur : {}".format(e))

if __name__ == "__main__":
    scan()
