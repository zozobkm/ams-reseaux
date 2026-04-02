#!/bin/bash
# Script de mise à jour dynamique de la Blacklist

echo "Téléchargement de la dernière blacklist depuis Internet..."
# Télécharge un fichier hosts (format: 0.0.0.0 domaine.com)
wget -qO- https://raw.githubusercontent.com/StevenBlack/hosts/master/hosts | grep "^0\.0\.0\.0" > /etc/blacklist_dns.conf

echo "Redémarrage du service DNS pour appliquer les règles..."
# Si tu utilises dnsmasq (très fréquent sur les box)
systemctl restart dnsmasq

echo "Blacklist mise à jour avec succès !"
