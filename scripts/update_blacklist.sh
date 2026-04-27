#!/bin/bash
echo "1. Téléchargement de la Blacklist (StevenBlack)..."
# On enlève le -q pour voir les erreurs s'il y en a
wget -O /tmp/hosts https://raw.githubusercontent.com/StevenBlack/hosts/master/hosts

# Vérification : si le fichier a bien été téléchargé
if [ -s /tmp/hosts ]; then
    echo "2. Conversion de la liste globale..."
    # On la sauve dans un fichier temporaire
    grep "^0\.0\.0\.0" /tmp/hosts | grep -v "0.0.0.0 0.0.0.0" | awk '{print $2}' | sort -u | awk '{print "zone \""$1"\" { type master; file \"/etc/bind/db.blackhole\"; };"}' > /tmp/bind_global.conf
    
    # 3. Ajout des règles locales (Base de données)
    echo "3. Ajout de vos règles locales..."
    # On va lire la base de données
    mysql -u forumuser -pforum123 -D box -N -e "SELECT mot_cle FROM contenu_bloque" | while read domain; do
      echo "zone \"$domain\" { type master; file \"/etc/bind/db.blackhole\"; };" >> /tmp/bind_global.conf
    done
    
    # 4. Fusion propre et nettoyage des doublons
    sort -u /tmp/bind_global.conf > /etc/bind/named.conf.blacklist
    
    echo "5. Redémarrage de Bind9..."
    systemctl restart bind9
    echo "Mise à jour terminée avec succès !"
else
    echo "ERREUR : Le téléchargement a échoué. Vérifiez la connexion Internet de la Box."
fi
