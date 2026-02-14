#!/bin/bash

# Récupération du nombre d'appareils
NB=$1

# Calcul de la plage : on commence à .10 sur le réseau 10.x
DEBUT="192.168.10.10"
FIN_OCTET=$((10 + NB - 1)) # -1 pour avoir exactement le nombre demandé
FIN="192.168.10.$FIN_OCTET"

# Paramètres fixes alignés sur eth1 (192.168.10.1)
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
echo "Succès : Configuration automatique pour $NB appareils ($DEBUT à $FIN)."
