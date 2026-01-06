#!/bin/bash
DOMAINE=$1
IP_BOX="192.168.1.1" # Ton IP réelle
ZONEFILE="/etc/bind/db.$DOMAINE"
CONF="/etc/bind/named.conf.local"

echo "Mise à jour du domaine $DOMAINE..."

# Nettoyage propre du bloc zone complet
sudo sed -i "/zone \"$DOMAINE\"/,/};/d" $CONF

# Ajout de la zone
sudo bash -c "echo 'zone \"$DOMAINE\" { type master; file \"$ZONEFILE\"; };' >> $CONF"

# Création du fichier de zone
sudo cp /etc/bind/db.local "$ZONEFILE"
sudo sed -i "s/localhost/$DOMAINE/g" "$ZONEFILE"
sudo sed -i "s/127.0.0.1/$IP_BOX/g" "$ZONEFILE"

# AJOUT AUTOMATIQUE DES SOUS-DOMAINES (Indispensable pour ton projet)
sudo bash -c "cat >> $ZONEFILE" <<EOF
www     IN      A       $IP_BOX
mail    IN      A       $IP_BOX
ftp     IN      A       $IP_BOX
forum   IN      A       $IP_BOX
EOF

sudo systemctl restart bind9
