# Proxmox Monitoring

Tableau de bord de supervision en temps réel pour une infrastructure Proxmox, avec relevé de température et d'humidité via un capteur DHT11 connecté à un Arduino.

## Fonctionnalités

- Affichage de la température et de l'humidité de la baie (capteur DHT11)
- Supervision du noeud proxmox : CPU, RAM, stockage
- Supervision des vm en cours d'exécution : CPU, RAM
- Interface web avec authentification par session
- Rafraîchissement automatique toutes les 15 secondes

## Architecture

[Arduino + DHT11 + Shield Ethernet]

[insert.php]  ←─ clé API 
        
[Base de données MySQL : proxmox_monitoring]
        
[collector.php]  ←─ API Proxmox 
        
[index.php]  ──►  Tableau de bord 

## Fichiers

Fichier | Rôle
db.php : Connexion PDO à la base de données
collector.php : Collecte les métriques Proxmox via l'API PVE
insert.php : Endpoint HTTP qui reçoit les données du capteur Arduino
index.php : Tableau de bord principal
login.php : Page de connexion
logout.php : Destruction de session et redirection
style.css : style de l'interface
capteur.ino : Code Arduino (DHT11 + Ethernet)


### Serveur

- PHP 
- MySQL 
- Serveur web Apache 

### Arduino

- Arduino avec shield Ethernet 
- Capteur DHT11 sur la broche D2
- Bibliothèques Arduino : DHT sensor library, Ethernet
}
```

Réponse succès : `{"ok":true,"id":"baie_..."}`
