#!/bin/bash
# Usage: ./config_mail.sh [add|status] [username]

ACTION=$1
USER=$2

if [ "$ACTION" == "add" ]; then
    echo "📧 Création du compte mail pour $USER..."
    # Création d'un utilisateur système (requis pour Postfix local)
    sudo useradd -m -s /bin/false "$USER"
    echo "$USER:password123" | sudo chpasswd
    echo "✅ Compte $USER@illipbox.lan créé."

elif [ "$ACTION" == "status" ]; then
    echo "🔍 Vérification du serveur Postfix..."
    sudo systemctl status postfix --no-pager | head -n 5
fi

# Redémarrage pour appliquer les changements
sudo systemctl restart postfix
