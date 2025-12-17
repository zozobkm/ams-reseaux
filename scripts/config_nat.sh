#!/bin/bash

# Interface vers Internet (WAN) et interface réseau interne (LAN)
WAN="eth0"
LAN="eth1"

echo "Activation du NAT..."

# Activer le routage IP
sudo sysctl -w net.ipv4.ip_forward=1

# Rendre la modification permanente
sudo sed -i 's/^net.ipv4.ip_forward=.*/net.ipv4.ip_forward=1/' /etc/sysctl.conf

# Supprimer les anciennes règles iptables
sudo iptables -t nat -F
sudo iptables -F
sudo iptables -X

# Ajouter les règles NAT et de filtrage
sudo iptables -t nat -A POSTROUTING -o $WAN -j MASQUERADE
sudo iptables -A FORWARD -i $LAN -o $WAN -j ACCEPT
sudo iptables -A FORWARD -i $WAN -o $LAN -m state --state RELATED,ESTABLISHED -j ACCEPT

# Sauvegarder les règles
sudo sh -c "iptables-save > /etc/iptables.rules"

# Rendre persistant au démarrage
if ! grep -q 'iptables-restore < /etc/iptables.rules' /etc/rc.local 2>/dev/null; then
    sudo bash -c "echo 'iptables-restore < /etc/iptables.rules' >> /etc/rc.local"
fi

echo "Configuration NAT terminée."
