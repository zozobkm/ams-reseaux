#!/bin/bash
truncate -s 0 /etc/bind/named.conf.blacklist
mysql -u forumuser -pforum123 -D box -N -e "SELECT mot_cle FROM contenu_bloque" | while read domain; do echo "zone \"$domain\" { type master; file \"/etc/bind/db.blackhole\"; };" >> /etc/bind/named.conf.blacklist; done
systemctl restart bind9
