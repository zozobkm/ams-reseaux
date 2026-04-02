#!/bin/bash
DOMAINE=$1
IP_BOX="192.168.10.1"

echo "Configuration du domaine $DOMAINE..."

# Vérifie si le domaine existe déjà pour éviter les doublons
if grep -q "zone \"$DOMAINE\"" /etc/bind/named.conf.local; then
    echo "Le domaine $DOMAINE existe déjà !"
    exit 1
fi

# 1. On AJOUTE (>>) à la fin du fichier sans écraser le reste
cat >> /etc/bind/named.conf.local <<EOF
zone "$DOMAINE" {
    type master;
    file "/etc/bind/db.$DOMAINE";
};
EOF

# 2. Création du fichier de zone
cat > /etc/bind/db.$DOMAINE <<EOF
\$TTL    604800
@       IN      SOA     $DOMAINE. root.$DOMAINE. ( 2 604800 86400 2419200 604800 )
@       IN      NS      $DOMAINE.
@       IN      A       $IP_BOX
www     IN      A       $IP_BOX
EOF

systemctl restart bind9
# On vérifie si le redémarrage a réussi avant de dire succès
if [ $? -eq 0 ]; then
    echo "Succès : Le domaine $DOMAINE pointe désormais vers $IP_BOX"
else
    echo "Erreur : Bind9 n'a pas pu redémarrer. Vérifiez la syntaxe."
fi
