#!/bin/bash
# Usage: sudo ./config_dns.sh IP_DU_SLAVE

SLAVE_IP=$1
ZONEFILE="/etc/bind/db.ceri.com"
CONFFILE="/etc/bind/named.conf.local"
SLAVE_NAME="ns2" # Nom du serveur esclave

if [ -z "$SLAVE_IP" ]; then
    echo "Erreur : spécifiez l'adresse IP du serveur Slave."
    exit 1
fi

# 1. Ajout des enregistrements NS et A dans le fichier de zone
# On ajoute le serveur de nom (NS) et son adresse (A)
sudo bash -c "echo '@         IN      NS      $SLAVE_NAME.ceri.com.' >> $ZONEFILE"
sudo bash -c "echo '$SLAVE_NAME       IN      A       $SLAVE_IP' >> $ZONEFILE"

# 2. Mise à jour du numéro de série (Serial)
# Obligatoire pour que le Slave sache qu'il doit se mettre à jour
# On cherche un nombre (le serial) et on le remplace par le timestamp actuel
NEW_SERIAL=$(date +%s)
sudo sed -i "s/[0-9]\{1,10\}/$NEW_SERIAL/1" "$ZONEFILE"

# 3. Mise à jour de named.conf.local pour autoriser le transfert
# On remplace 'allow-update { none; };' par 'allow-transfer { IP_SLAVE; };'
sudo sed -i "s/allow-update { none; };/allow-transfer { $SLAVE_IP; };/" "$CONFFILE"

# 4. Rechargement du service DNS
sudo systemctl restart bind9
echo "Succès : Le serveur Slave ($SLAVE_IP) a été déclaré et autorisé."
