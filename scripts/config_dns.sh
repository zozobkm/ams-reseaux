#!/bin/bash
# Usage: sudo ./config_dns_slave.sh IP_DU_SLAVE

SLAVE_IP=$1
ZONEFILE="/etc/bind/db.ceri.com"
CONFFILE="/etc/bind/named.conf.local"

# 1. Vérifier si l'IP est valide (évite le bug "lui")
if [[ ! $SLAVE_IP =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Erreur : $SLAVE_IP n'est pas une adresse IP valide."
    exit 1
fi

# 2. Ajouter le Slave comme serveur de noms officiel dans la zone
# On ajoute une ligne NS et une ligne A pour le nom ns2
sudo bash -c "echo '@    IN    NS    ns2.ceri.com.' >> $ZONEFILE"
sudo bash -c "echo 'ns2  IN    A     $SLAVE_IP' >> $ZONEFILE"

# 3. Mettre à jour le Serial pour forcer le transfert
NEW_SERIAL=$(date +%s)
sudo sed -i "s/[0-9]\{10\}/$NEW_SERIAL/" "$ZONEFILE"

# 4. Autoriser le transfert vers cette IP spécifique dans la config
# On remplace l'ancienne ligne par la nouvelle avec la vraie IP
sudo sed -i "s/allow-transfer { .* };/allow-transfer { $SLAVE_IP; };/" "$CONFFILE"
# Si la ligne n'existait pas, on peut utiliser une approche plus brute pour l'insérer
# mais le sed ci-dessus corrige ton erreur actuelle "lui".

sudo systemctl restart bind9
echo "Succès : Le Slave $SLAVE_IP a été déclaré et autorisé sur le Master."
