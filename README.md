 Interface Web d'Administration Réseau Automatisée

##  Description
**CeriBox-NetAdmin** est une solution complète d'administration système et réseau permettant de piloter, configurer et surveiller des services réseaux critiques (DNS et DHCP) sur un serveur Linux Ubuntu à travers une interface web d'administration intuitive et sécurisée. 

Ce projet couple la puissance du **scripting système (Bash)** pour l'automatisation bas niveau à une **architecture logicielle Web (PHP/MySQL)** pour l'interface utilisateur. Il met en évidence des compétences en **Administration Réseau**, **Sécurisation d'Infrastructures**, et **Développement Web Full-stack**.

---

##  Fonctionnalités
- **Automatisation DNS (Bind9) :** Génération automatique et gestion dynamique des fichiers de zone DNS, des enregistrements et de la résolution de noms via l'interface web.
- **Gestion DHCP (ISC DHCP Server) :** Configuration simplifiée des plages d'adresses IP dynamiques, des baux réseau et des assignations statiques.
- **Interface d'Administration PHP/MySQL :** Panneau de contrôle complet permettant aux administrateurs de piloter les services sans manipuler directement les fichiers de configuration textuels du serveur.
- **Sécurité Applicative :** Implémentation d'un système de sessions web sécurisées avec contrôle d'accès strict (Rôles Utilisateur / Administrateur).
- **Espace Communautaire :** Intégration d'un forum d'entraide et de support pour les utilisateurs de l'infrastructure.
- **Orchestration Bash :** Scripts système en arrière-plan assurant la réécriture sécurisée des fichiers de configuration système et le redémarrage des services réseau (systemctl).

---

##  Technologies & Outils utilisés
- **Système & Services Réseau :** Linux Ubuntu Server, Bind9 (DNS), ISC DHCP Server.
- **Scripting & Automatisation :** Bash Core.
- **Développement Web Backend :** PHP, MySQL (Gestion des utilisateurs, logs, sessions).
- **Interface Frontend :** HTML5, CSS3, JavaScript (Conception axée sur l'expérience utilisateur UI/UX).

---

##  Architecture Logicielle (Principe de fonctionnement)
1. **L'utilisateur** effectue une action sur l'interface Web (ex: ajouter une plage DHCP ou un enregistrement DNS).
2. **Le serveur Web (PHP)** valide la requête, met à jour la base de données MySQL et appelle un script Bash dédié avec les paramètres requis.
3. **Le script Bash** (exécuté avec les privilèges nécessaires ou via sudoers sécurisé) modifie proprement les fichiers de configuration de `bind9` ou `dhcpd`.
4. **Le service réseau** est rechargé automatiquement, rendant la modification immédiatement opérationnelle sur l'infrastructure.

---

## Sécurité & Bonnes Pratiques
* **Isolation des privilèges :** Les scripts Bash d'administration système sont restreints et audités pour éviter toute élévation de privilèges non autorisée depuis la couche web.
* **Persistance des Sessions :** Protection contre les failles d'authentification et hijacking grâce à une gestion robuste des tokens de session PHP.

---

##  Licence
Ce projet a été réalisé dans un cadre universitaire et personnel. Libre de modification et de déploiement pour des infrastructures de test ou des maquettes de réseaux virtualisés.
