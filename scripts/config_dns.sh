#!/bin/bash

# Récupération du nom de domaine
DOMAINE=$1

# IP de ta Box (DNS)
IP_BOX="192.168.10.1"

echo "Configuration du domaine $DOMAINE..."

# 1. Mise à jour du fichier named.conf.local
# On écrase pour ne pas avoir de doublons si on change de nom
cat > /etc/bind/named.conf.local <<EOF
zone "$DOMAINE" {
    type master;
    file "/etc/bind/db.$DOMAINE";
};
EOF

# 2. Création du fichier de zone
cat > /etc/bind/db.$DOMAINE <<EOF
\$TTL    604800
@       IN      SOA     $DOMAINE. root.$DOMAINE. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      $DOMAINE.
@       IN      A       $IP_BOX
www     IN      A       $IP_BOX
EOF

# 3. Redémarrage de Bind9 pour appliquer
systemctl restart bind9

echo "Succès : Le domaine $DOMAINE pointe désormais vers $IP_BOX"
