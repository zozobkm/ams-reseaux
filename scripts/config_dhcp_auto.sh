#!/bin/bash
NB_APPAREILS=$1

# Détection automatique de l'interface réseau interne
INTERFACE=$(ip -o link show | awk -F': ' '{print $2}' | grep -E '^eth1|enp0s8' | head -n1)
if [ -z "$INTERFACE" ]; then
  echo "Erreur : impossible de détecter l'interface réseau interne."
  exit 1
fi


RESEAU="192.168.10.0"
MASQUE="255.255.255.0"
DEBUT="192.168.10.10"
FIN="192.168.10.$((10+NB_APPAREILS))"
PASSERELLE="192.168.10.1"

echo "Interface utilisée : $INTERFACE"
echo "Configuration du DHCP pour $NB_APPAREILS appareils..."

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

# On redémarre le service DHCP
sudo sed -i "s/INTERFACESv4=.*/INTERFACESv4=\"$INTERFACE\"/" /etc/default/isc-dhcp-server
sudo systemctl restart isc-dhcp-server

echo "DHCP configuré réussite"
sudo systemctl status isc-dhcp-server --no-pager
