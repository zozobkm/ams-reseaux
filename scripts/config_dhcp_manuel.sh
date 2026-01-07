#!/bin/bash

# Récupération des deux IPs envoyées par le PHP
DEBUT=$1
FIN=$2

# Paramètres fixes pour éviter de casser le réseau
RESEAU="192.168.1.0"
MASQUE="255.255.255.0"
PASSERELLE="192.168.1.1"

# Écriture du fichier avec les DEUX adresses dans le range
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

sudo systemctl restart isc-dhcp-server
echo "DHCP Manuel configuré : $DEBUT à $FIN"
