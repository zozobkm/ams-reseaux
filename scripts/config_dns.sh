#!/bin/bash
SLAVE_IP=$1
ZONEFILE="/etc/bind/db.ceri.com"
CONFFILE="/etc/bind/named.conf.local"

if [[ ! $SLAVE_IP =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Erreur : $SLAVE_IP n'est pas une IP valide."
    exit 1
fi

# 1. Création du fichier de zone s'il n'existe pas
if [ ! -f "$ZONEFILE" ]; then
    sudo bash -c "cat > $ZONEFILE" <<EOF
\$TTL    604800
@       IN      SOA     ns1.ceri.com. admin.ceri.com. (
                     $(date +%s)         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      ns1.ceri.com.
@       IN      A       192.168.10.1
ns1     IN      A       192.168.10.1
EOF
fi

# 2. Ajout du Slave (seulement s'il n'est pas déjà présent)
if ! grep -q "$SLAVE_IP" "$ZONEFILE"; then
    sudo bash -c "echo '@    IN    NS    ns2.ceri.com.' >> $ZONEFILE"
    sudo bash -c "echo 'ns2  IN    A     $SLAVE_IP' >> $ZONEFILE"
fi

# 3. Mise à jour du Serial (Format Unix Timestamp)
NEW_SERIAL=$(date +%s)
sudo sed -i "s/[0-9]\{10\}/$NEW_SERIAL/" "$ZONEFILE"

# 4. Autorisation du transfert dans named.conf.local
sudo sed -i "s/allow-transfer { .* };/allow-transfer { $SLAVE_IP; };/" "$CONFFILE"

sudo systemctl restart bind9
echo "Succès : Le Slave $SLAVE_IP est configuré sur le Master."
