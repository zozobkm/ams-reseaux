#!/bin/bash
echo "1. Téléchargement de la Blacklist (StevenBlack)..."
wget -qO /tmp/hosts https://raw.githubusercontent.com/StevenBlack/hosts/master/hosts

echo "2. Conversion pour Bind9..."
# On prend les lignes qui commencent par 0.0.0.0, on enlève l'IP, et on formate en "zone" Bind9
# On ignore localhost et on évite de bloquer la machine elle-même.
grep "^0\.0\.0\.0" /tmp/hosts | grep -v "0.0.0.0 0.0.0.0" | awk '{print "zone \""$2"\" { type master; file \"/etc/bind/db.blackhole\"; };"}' > /etc/bind/named.conf.blacklist

echo "3. Application des nouvelles règles..."
systemctl reload bind9

echo "Mise à jour terminée avec succès !"
