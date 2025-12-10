#!/bin/bash

# Interface vers Internet (NAT)
WAN="eth0"
# Interface réseau local (privé)
LAN="eth1"

echo "Activation du NAT..."

# Activer le routage IP
sudo sysctl -w net.ipv4.ip_forward=1

# Le rendre permanent
sudo sed -i 's/net.ipv4.ip_forward=.*/net.ipv4.ip_forward=1/' /etc/sysctl.conf

# Nettoyer anciennes règles
sudo iptables -t nat -F

# Ajouter règle NAT MASQUERADE
sudo iptables -t nat -A POSTROUTING -o $WAN -j MASQUERADE

echo "NAT activé : $LAN → $WAN"

# Sauvegarde des règles (pour redémarrage)
sudo sh -c "iptables-save > /etc/iptables.rules"

# Rendre persistant au boot
if ! grep -q 'iptables-restore < /etc/iptables.rules' /etc/rc.local 2>/dev/null; then
    sudo bash -c "echo 'iptables-restore < /etc/iptables.rules' >> /etc/rc.local"
fi

echo "Configuration NAT terminée."

