#!/bin/bash
# Usage: sudo ./config_dns.sh nom_du_sous_domaine

SUB=$1
ZONEFILE="/etc/bind/db.ceri.com"
IP_BOX="192.168.1.1" # Ton IP fixe

if [ -z "$SUB" ]; then
    echo "Erreur : spécifiez un nom de sous-domaine."
    exit 1
fi

# 1. Vérification si le sous-domaine existe déjà dans le fichier
if grep -q "^$SUB" "$ZONEFILE"; then
    echo "Le sous-domaine $SUB existe déjà dans ceri.com."
    exit 1
fi

# 2. Ajout de l'enregistrement A à la fin du fichier
sudo bash -c "echo '$SUB    IN    A    $IP_BOX' >> $ZONEFILE"

# 3. Mise à jour automatique du numéro de série (Serial)
# Le format est AAAAMMDDNN. On utilise la date du jour.
NEW_SERIAL=$(date +%Y%m%d01)
sudo sed -i "s/[0-9]\{10\}/$NEW_SERIAL/" "$ZONEFILE"

# 4. Rechargement du service DNS
sudo systemctl reload bind9
echo "Succès : $SUB.ceri.com a été ajouté avec l'IP $IP_BOX."
