#!/bin/bash

# En mode manuel, $1 contient TOUT le texte tapé dans la zone de texte
NOUVELLE_CONFIG=$1

# On écrase le fichier avec le texte reçu (on utilise echo -e pour les retours à la ligne)
sudo bash -c "echo -e '$NOUVELLE_CONFIG' > /etc/dhcp/dhcpd.conf"

# On redémarre le service pour appliquer les changements
sudo systemctl restart isc-dhcp-server

echo "Succès : Fichier dhcpd.conf mis à jour manuellement."
