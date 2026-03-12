#!/bin/bash
# Usage: ./config_ftp.sh [upload|download]

ACTION=$1
# --- CONFIGURATION (À VÉRIFIER) ---
SERVEUR_FTP="ftp.ceri.com" 
USER="ftpuser"
PASS="ftp123"
FICHIER_TEST="/tmp/test_debit.dat"
# AJOUT INDISPENSABLE POUR LA TÂCHE S6 :
LOG_FILE="/home/stud/ftp_audit.log" 

# 1. Verification et creation du fichier de test de 10Mo
if [ ! -f $FICHIER_TEST ]; then
    echo "Creation du fichier de test (10Mo)..."
    dd if=/dev/zero of=$FICHIER_TEST bs=1M count=10 2>/dev/null
fi

# 2. Execution du test selon l'argument
if [ "$ACTION" == "upload" ]; then
    echo "Test d'envoi (Upload) vers $SERVEUR_FTP en cours..."
    START=$(date +%s.%N)
    curl -T $FICHIER_TEST ftp://$SERVEUR_FTP/ --user $USER:$PASS
    END=$(date +%s.%N)
elif [ "$ACTION" == "download" ]; then
    echo "Test de reception (Download) depuis $SERVEUR_FTP en cours..."
    START=$(date +%s.%N)
    # On télécharge test_debit.dat (celui envoyé par l'upload)
    curl -o /dev/null ftp://$SERVEUR_FTP/test_debit.dat --user $USER:$PASS
    END=$(date +%s.%N)
else
    echo "Erreur : Usage : $0 [upload|download]"
    exit 1
fi

# 3. Calcul de la vitesse de transfert
DIFF=$(echo "$END - $START" | bc)
VITESSE=$(echo "scale=2; 10 / $DIFF" | bc)

# --- STOCKAGE DANS LE FICHIER (TÂCHE S6) ---
TIMESTAMP=$(date "+%Y-%m-%d %H:%M:%S")
# Utilisation de guillemets pour éviter l'erreur "ambiguous redirect"
echo "$TIMESTAMP | $ACTION | $VITESSE" >> "$LOG_FILE"

echo "---"
echo "Resultat : Termine en $DIFF secondes."
echo "Debit : $VITESSE Mo/s"
