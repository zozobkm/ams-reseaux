#!/bin/bash
DOMAINE=$1
ZONEFILE="/etc/bind/db.$DOMAINE"
CONF="/etc/bind/named.conf.local"

echo "Mise à jour du domaine $DOMAINE..."

# Nettoyage : Si la zone existe déjà, on la supprime proprement du fichier de conf
if grep -q "zone \"$DOMAINE\"" $CONF; then
    echo "Zone existante détectée. Nettoyage avant recréation..."
    # On supprime la ligne spécifique à cette zone dans named.conf.local
    sudo sed -i "/zone \"$DOMAINE\"/d" $CONF
fi

# Suppression de l'ancien fichier de zone s'il existe
if [ -f "$ZONEFILE" ]; then
    sudo rm "$ZONEFILE"
fi

# Ajout propre de la zone
echo "➕ Ajout de la zone $DOMAINE dans named.conf.local..."
sudo bash -c "echo 'zone \"$DOMAINE\" { type master; file \"$ZONEFILE\"; };' >> $CONF"

# Création du fichier de zone depuis le template
echo "📄 Génération du fichier $ZONEFILE..."
sudo cp /etc/bind/db.local "$ZONEFILE"
# Remplacement de 'localhost' par le domaine (obligatoire pour bind9)
sudo sed -i "s/localhost/$DOMAINE/g" "$ZONEFILE"
# Remplacement de 127.0.0.1 par l'IP de ta Box
sudo sed -i "s/127.0.0.1/192.168.10.1/g" "$ZONEFILE"

echo "🔄 Redémarrage de bind9..."
sudo systemctl restart bind9

if systemctl is-active --quiet bind9; then
    echo "✅ DNS opérationnel pour $DOMAINE"
else
    echo "❌ ERREUR : bind9 a échoué. Vérifiez /var/log/syslog"
fi
