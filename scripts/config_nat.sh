#!/bin/bash

WAN="eth0"
LAN="eth1"

echo "[NAT] Activation du routage IP"
echo 1 > /proc/sys/net/ipv4/ip_forward

echo "[NAT] Nettoyage des règles existantes"
iptables -F
iptables -t nat -F
iptables -X

echo "[NAT] Autorisation du forwarding"
iptables -A FORWARD -i $LAN -o $WAN -j ACCEPT
iptables -A FORWARD -i $WAN -o $LAN -m state --state RELATED,ESTABLISHED -j ACCEPT

echo "[NAT] Ajout MASQUERADE"
iptables -t nat -A POSTROUTING -o $WAN -j MASQUERADE

echo "[NAT] Règles actives :"
iptables -t nat -L -v
