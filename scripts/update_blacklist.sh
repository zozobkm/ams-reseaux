#!/bin/bash
echo "1. Téléchargement de la Blacklist (StevenBlack)..."
wget -qO /tmp/hosts https://raw.githubusercontent.com/StevenBlack/hosts/master/hosts

# CRÉATION DU FICHIER BLACKHOLE MANQUANT !
cat > /etc/bind/db.blackhole <<EOF
\$TTL    86400
@       IN      SOA     localhost. root.localhost. ( 1 604800 86400 2419200 86400 )
@       IN      NS      localhost.
@       IN      A       0.0.0.0
EOF

echo "2. Conversion pour Bind9..."
grep "^0\.0\.0\.0" /tmp/hosts | grep -v "0.0.0.0 0.0.0.0" | awk '{print $2}' | sort -u | awk '{print "zone \""$1"\" { type master; file \"/etc/bind/db.blackhole\"; };"}' > /etc/bind/named.conf.blacklist

echo "3. Application des nouvelles règles..."
systemctl restart bind9
echo "Mise à jour terminée avec succès !"
