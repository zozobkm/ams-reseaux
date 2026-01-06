#!/bin/bash
# Usage: sudo ./config_dns.sh ceri.com

DOMAINE=$1
IP_BOX="192.168.1.1" # Ton IP réelle constatée
ZONEFILE="/etc/bind/db.$DOMAINE"
CONF="/etc/bind/named.conf.local"

echo "⚙️ Configuration DNS pour $DOMAINE..."

# 1. Nettoyage du bloc zone s'il existe déjà pour éviter les doublons
sudo sed -i "/zone \"$DOMAINE\"/,/};/d" $CONF

# 2. Ajout de la déclaration de zone dans named.conf.local
sudo bash -c "cat >> $CONF <<EOF
zone \"$DOMAINE\" {
    type master;
    file \"$ZONEFILE\";
};
EOF"

# 3. Création du fichier de zone avec sous-domaines
sudo cp /etc/bind/db.local "$ZONEFILE"
sudo sed -i "s/localhost/$DOMAINE/g" "$ZONEFILE"
sudo sed -i "s/127.0.0.1/$IP_BOX/g" "$ZONEFILE"

# 4. Ajout des enregistrements A pour les services
sudo bash -c "cat >> $ZONEFILE <<EOF
www     IN      A       $IP_BOX
mail    IN      A       $IP_BOX
ftp     IN      A       $IP_BOX
forum   IN      A       $IP_BOX
ns1     IN      A       $IP_BOX
EOF"

# 5. Redémarrage et vérification
sudo systemctl restart bind9
if systemctl is-active --quiet bind9; then
    echo "✅ DNS opérationnel. Test : nslookup mail.$DOMAINE 127.0.0.1"
else
    echo "❌ Erreur Bind9. Vérifiez la syntaxe."
fi
