#!/bin/bash
# Script de création de compte mail local
if [ "$1" == "add" ]; then
    useradd -m -s /usr/sbin/nologin "$2"
    echo "$2:password123" | chpasswd
    echo "Utilisateur $2 créé avec succès."
fi
