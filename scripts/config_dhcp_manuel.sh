#!/bin/bash

# En mode manuel, on reçoit TOUT le texte de la configuration en un seul bloc
NOUVELLE_CONFIG=$1

echo "Application de la configuration manuelle..."

# On utilise sudo pour écraser le fichier avec le texte reçu
sudo bash -c "echo -e '$NOUVELLE_CONFIG' > /etc/dhcp/dhcpd.conf"

# Redémarrage du service pour appliquer les changements
sudo systemctl restart isc-dhcp-server

echo "Le fichier /etc/dhcp/dhcpd.conf a été mis à jour manuellement."
