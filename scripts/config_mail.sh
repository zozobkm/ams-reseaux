#!/bin/bash
# Usage: sudo ./config_mail.sh add [username] [password]

ACTION=$1
USER_NAME=$2
USER_PASS=$3

if [ "$ACTION" == "add" ]; then
    # Verification si l'utilisateur existe deja
    if id "$USER_NAME" &>/dev/null; then
        echo "Erreur : L'utilisateur $USER_NAME existe deja."
        exit 1
    fi

    # Creation du compte sans acces au shell pour la securite
    sudo useradd -m -s /usr/sbin/nologin "$USER_NAME"
    
    # Definition du mot de passe passe en argument
    echo "$USER_NAME:$USER_PASS" | sudo chpasswd
    
    echo "Compte mail pour $USER_NAME cree avec succes sur le domaine ceri.com."
fi
