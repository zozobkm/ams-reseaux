#!/bin/bash

DOMAINE=$1
ZONEFILE="/etc/bind/db.$DOMAINE"
CONF="/etc/bind/named.conf.local"

echo "Création du domaine $DOMAINE..."

# Vérifier si la zone existe déjà dans named.conf.local
if grep -q "zone \"$DOMAINE\"" $CONF; then
    echo "La zone $DOMAINE existe déjà dans $CONF. Aucun ajout effectué."
else
    echo "➕ Ajout de la zone $DOMAINE dans named.conf.local..."
    sudo bash -c "echo 'zone \"$DOMAINE\" { type master; file \"$ZONEFILE\"; };' >> $CONF"
fi

# Vérifier si le fichier de zone existe déjà
if [ -f "$ZONEFILE" ]; then
    echo "Le fichier $ZONEFILE existe déjà, pas de copie."
else
    echo "📄 Création du fichier $ZONEFILE..."
    sudo cp /etc/bind/db.local "$ZONEFILE"
    sudo sed -i "s/local/$DOMAINE/g" "$ZONEFILE"
fi

echo "🔄 Redémarrage de bind9..."
sudo systemctl restart bind9

# Vérification du statut
if systemctl is-active --quiet bind9; then
    echo "DNS configuré pour $DOMAINE (bind9 actif)"
else
    echo " ERREUR : bind9 ne démarre pas. Vérifiez le fichier de zone."
fi
