#!/bin/bash

# On ignore le premier argument ($1 = "manuel")
# On récupère les adresses IP envoyées par le PHP
IP_DEBUT=$2
IP_FIN=$3

# Variables fixes pour la CeriBox
RESEAU="192.168.1.0"
MASQUE="255.255.255.0"
PASSERELLE="192.168.1.1"

echo "Configuration du DHCP pour le réseau $RESEAU..."

# Écriture sécurisée du fichier
sudo bash -c "cat > /etc/dhcp/dhcpd.conf" <<EOF
default-lease-time 600;
max-lease-time 7200;
authoritative;

subnet $RESEAU netmask $MASQUE {
  range $IP_DEBUT $IP_FIN;
  option routers $PASSERELLE;
  option domain-name-servers $PASSERELLE;
}
EOF

# Configuration de l'interface d'écoute
sudo sed -i "s/INTERFACESv4=.*/INTERFACESv4=\"eth1\"/" /etc/default/isc-dhcp-server

# Redémarrage du service
sudo systemctl restart isc-dhcp-server

# Affichage du statut pour les logs de l'interface Web
echo "DHCP configuré avec succès."
sudo systemctl status isc-dhcp-server --no-pager | head -n 6
