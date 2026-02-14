#!/bin/bash

# Récupération du nombre d'appareils envoyé par le PHP
NB=$1
DEBUT_IP=10
# Calcul de la fin de plage
FIN_IP=$((DEBUT_IP + NB - 1))

# Sécurité pour ne pas dépasser la limite du réseau
if [ $FIN_IP -gt 254 ]; then FIN_IP=254; fi

echo "Configuration Auto pour $NB appareils..."

# On écrit directement dans le fichier (le script sera lancé en sudo par le PHP)
cat > /etc/dhcp/dhcpd.conf <<EOF
default-lease-time 600;
max-lease-time 7200;
authoritative;

subnet 192.168.10.0 netmask 255.255.255.0 {
  range 192.168.10.$DEBUT_IP 192.168.10.$FIN_IP;
  option routers 192.168.10.1;
  option domain-name-servers 192.168.10.1;
  option domain-name "ceri.com";
}
EOF

# Redémarrage du service
systemctl restart isc-dhcp-server
echo "DHCP Auto configuré : 192.168.10.$DEBUT_IP à 192.168.10.$FIN_IP"
