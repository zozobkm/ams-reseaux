#!/bin/bash
# Usage: ./config_ftp.sh [upload|download]

ACTION=$1
SERVEUR_FAI="192.168.20.2" # Adresse IP du serveur distant (FAI)
USER="ftpuser"
PASS="ftp123"
FICHIER_TEST="/tmp/test_debit.dat"

# Créer un fichier de 10Mo pour le test si nécessaire
if [ ! -f $FICHIER_TEST ]; then
    dd if=/dev/zero of=$FICHIER_TEST bs=1M count=10
fi

if [ "$ACTION" == "upload" ]; then
    echo "🚀 Test d'envoi (Upload) en cours..."
    # Mesure du temps avec la commande 'time'
    START=$(date +%s.%N)
    curl -T $FICHIER_TEST ftp://$SERVEUR_FAI/ --user $USER:$PASS
    END=$(date +%s.%N)
elif [ "$ACTION" == "download" ]; then
    echo "📥 Test de réception (Download) en cours..."
    START=$(date +%s.%N)
    curl -o /dev/null ftp://$SERVEUR_FAI/test_10M.dat --user $USER:$PASS
    END=$(date +%s.%N)
fi

# Calcul de la vitesse
DIFF=$(echo "$END - $START" | bc)
VITESSE=$(echo "scale=2; 10 / $DIFF" | bc)
echo "✅ Terminé en $DIFF secondes. Débit : $VITESSE Mo/s"
