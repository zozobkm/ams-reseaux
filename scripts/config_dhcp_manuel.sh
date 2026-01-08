#!/bin/bash

# Récupération des deux IPs complètes envoyées par le PHP
DEBUT=$1
FIN=$2

# Paramètres fixes
RESEAU="192.168.1.0"
MASQUE="255.255.255.0"
PASSERELLE="192.168.1.1"

# Écriture du fichier dhcpd.conf
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

# Redémarrage du service
sudo systemctl restart isc-dhcp-server
echo "Succès : DHCP Manuel configuré sur la plage $DEBUT - $FIN"
