#!/bin/bash
RESEAU=$1
MASQUE=$2
DEBUT=$3
FIN=$4
PASSERELLE=$5

echo " Configuration du DHCP..."
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

# On active le service sur eth1
sudo sed -i "s/INTERFACESv4=.*/INTERFACESv4=\"eth1\"/" /etc/default/isc-dhcp-server

# Redémarrage
sudo systemctl restart isc-dhcp-server
echo "DHCP configuré  $RESEAU"
sudo systemctl status isc-dhcp-server --no-pager | head -n 6
