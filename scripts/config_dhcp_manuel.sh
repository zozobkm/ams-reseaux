#!/bin/bash

# Le PHP envoie deux arguments : $1 (IP début) et $2 (IP fin)
DEBUT=$1
FIN=$2

# Paramètres fixes alignés sur ta Box (192.168.10.1)
RESEAU="192.168.10.0"
MASQUE="255.255.255.0"
PASSERELLE="192.168.10.1"

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
echo "Succès Manuel : Plage $DEBUT à $FIN appliquée."
