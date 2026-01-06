#!/bin/bash
# Usage: ./config_ftp.sh [upload|download]

ACTION=$1
# Utilisation de ton domaine DNS local pour la démo
SERVEUR_FTP="ftp.ceri.com" 
USER="ftpuser"
PASS="ftp123"
FICHIER_TEST="/tmp/test_debit.dat"

# 1. Création du fichier de test de 10Mo si absent
if [ ! -f $FICHIER_TEST ]; then
    echo" Création du fichier de test..."
    dd if=/dev/zero of=$FICHIER_TEST bs=1M count=10 2>/dev/null
fi

# 2. Lancement du test de débit
if [ "$ACTION" == "upload" ]; then
    echo "Test d'envoi (Upload) vers $SERVEUR_FTP..."
    START=$(date +%s.%N)
    curl -T $FICHIER_TEST ftp://$SERVEUR_FTP/ --user $USER:$PASS
    END=$(date +%s.%N)
elif [ "$ACTION" == "download" ]; then
    echo " Test de réception (Download) depuis $SERVEUR_FTP..."
    START=$(date +%s.%N)
    # Téléchargement vers /dev/null pour mesurer uniquement le débit
    curl -o /dev/null ftp://$SERVEUR_FTP/test_10M.dat --user $USER:$PASS
    END=$(date +%s.%N)
else
    echo " Usage: $0 [upload|download]"
    exit 1
fi

# 3. Calcul de la vitesse (nécessite le paquet 'bc')
DIFF=$(echo "$END - $START" | bc)
VITESSE=$(echo "scale=2; 10 / $DIFF" | bc)

echo "---"
echo "✅ Terminé en $DIFF secondes."
echo "📈 Débit : $VITESSE Mo/s"
