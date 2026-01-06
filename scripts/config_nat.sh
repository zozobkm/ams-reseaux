#!/bin/bash

# Correction des interfaces selon tes captures
WAN="eth1"
LAN="eth0"

echo "Activation du NAT sur $WAN pour le reseau $LAN..."

# 1. Activation du routage IP au niveau du noyau
sudo sysctl -w net.ipv4.ip_forward=1
sudo sed -i 's/^#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/' /etc/sysctl.conf

# 2. Nettoyage des anciennes regles pour repartir sur une base propre
sudo iptables -t nat -F
sudo iptables -F
sudo iptables -X

# 3. Masquage d'IP (MASQUERADE)
# Permet aux paquets du LAN de sortir avec l'IP de l'interface WAN
sudo iptables -t nat -A POSTROUTING -o $WAN -j MASQUERADE

# 4. Autorisation du transfert de paquets (Forwarding)
# Autorise le flux LAN -> WAN
sudo iptables -A FORWARD -i $LAN -o $WAN -j ACCEPT
# Autorise le retour des connexions deja etablies
sudo iptables -A FORWARD -i $WAN -o $LAN -m state --state RELATED,ESTABLISHED -j ACCEPT

echo "Configuration NAT terminee. La Box sert maintenant de passerelle."
