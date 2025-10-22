#!/bin/bash
DOMAINE=$1
echo "Création du domaine $DOMAINE..."
sudo bash -c "echo 'zone \"$DOMAINE\" { type master; file \"/etc/bind/db.$DOMAINE\"; };' >> /etc/bind/named.conf.local"
sudo cp /etc/bind/db.local /etc/bind/db.$DOMAINE
sudo sed -i "s/local/$DOMAINE/g" /etc/bind/db.$DOMAINE
sudo systemctl restart bind9
echo "DNS configuré pour $DOMAINE "
