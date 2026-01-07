#!/bin/bash

# On ignore $1 (qui contient "manuel")
# On récupère les adresses de début et de fin envoyées par le PHP
DEBUT=$2
FIN=$3

# Paramètres fixes pour garantir que le réseau ne casse pas
RESEAU="192.168.1.0"
MASQUE="255.255.255.0"
PASSERELLE="192.168.1.1"

echo "Configuration du DHCP sur le réseau $RESEAU ..."

# Écriture sécurisée du fichier de configuration
sudo bash -c "cat > /etc/dhcp/dhcpd.conf" <<EOF
default-lease-time 600;
max-lease-time 7200;
authoritative;

subnet $RESEAU netmask $MASQUE {
  range $DEBUT $FIN;
  option routers $PASSERELLE;
  option domain-name-servers $PASSERELLE;
  option domain-name "ceri.com";
}
EOF

# Configuration de l'interface LAN (eth0)
sudo sed -i 's/INTERFACESv4=.*/INTERFACESv4="eth0"/' /etc/default/isc-dhcp-server

# Redémarrage du service
sudo systemctl restart isc-dhcp-server

# Affichage du résultat pour les logs Web
echo "DHCP configuré avec succès sur eth0 !"
sudo systemctl status isc-dhcp-server --no-pager | head -n 6
