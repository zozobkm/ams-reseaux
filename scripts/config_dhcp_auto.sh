#!/bin/bash
RESEAU=$1
MASQUE=$2
DEBUT=$3
FIN=$4
PASSERELLE=$5

echo "Configuration DHCP sur le réseau $RESEAU ..."
sudo bash -c "cat > /etc/dhcp/dhcpd.conf" <<EOF
default-lease-time 600;
max-lease-time 7200;
authoritative;
subnet $RESEAU netmask $MASQUE {
  range $DEBUT $FIN;
  option routers $PASSERELLE;
  option domain-name-servers 8.8.8.8;
}
EOF

sudo sed -i "s/INTERFACESv4=.*/INTERFACESv4=\"eth1\"/" /etc/default/isc-dhcp-server
sudo systemctl restart isc-dhcp-server
echo " DHCP configuré automatiquement !"
