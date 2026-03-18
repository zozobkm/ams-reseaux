#!/bin/bash


ACTION=$1
# --- CONFIGURATION ---
SERVEUR_FTP="ftp.ceri.com" 
USER="ftpuser"
PASS="ftp123"
FICHIER_TEST="/tmp/test_debit.dat"
LOG_FILE="/home/stud/ftp_audit.log" 

# 1. Création du fichier de test de 10Mo si absent
if [ ! -f $FICHIER_TEST ]; then
    dd if=/dev/zero of=$FICHIER_TEST bs=1M count=10 2>/dev/null
fi

# 2. Exécution du transfert
if [ "$ACTION" == "upload" ]; then
    START=$(date +%s.%N)
    curl -T $FICHIER_TEST ftp://$SERVEUR_FTP/ --user $USER:$PASS
    END=$(date +%s.%N)
elif [ "$ACTION" == "download" ]; then
    START=$(date +%s.%N)
    curl -o /dev/null ftp://$SERVEUR_FTP/test_debit.dat --user $USER:$PASS
    END=$(date +%s.%N)
else
    echo "Usage : $0 [upload|download]"
    exit 1
fi

# 3. Calcul de la vitesse
DIFF=$(echo "$END - $START" | bc)
VITESSE=$(echo "scale=2; 10 / $DIFF" | bc)

# --- Journalisation dans le fichier texte ---
TIMESTAMP=$(date "+%Y-%m-%d %H:%M:%S")
echo "$TIMESTAMP | $ACTION | $VITESSE" >> "$LOG_FILE"

# Sortie pour que le PHP puisse lire le résultat
echo "Termine en $DIFF secondes."
echo "Debit : $VITESSE Mo/s"
