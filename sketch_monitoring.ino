#include <DHT.h>
#include <SPI.h>
#include <Ethernet.h>

#define DHTPIN  2
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };

IPAddress arduinoIP(10, 1, 30, 200);
IPAddress gateway  (10, 1, 30, 254);
IPAddress dnsServer(10, 1, 30, 254);
IPAddress subnet   (255, 255, 255, 0);
IPAddress serveur(10, 1, 30, 46);

EthernetClient client;

void setup() {
    Serial.begin(9600);
    dht.begin();
    delay(2000);

    Serial.println("Demarrage DHT11 + Ethernet...");

    Ethernet.begin(mac, arduinoIP, dnsServer, gateway, subnet);
    delay(1500);

    Serial.print("IP Arduino : ");
    Serial.println(Ethernet.localIP());
    Serial.println("Arduino pret !");
}

void loop() {
    Ethernet.maintain();

    float temp = dht.readTemperature();
    float hum  = dht.readHumidity();

    if (isnan(temp) || isnan(hum)) {
        Serial.println("Erreur DHT11 !");
        delay(5000);
        return;
    }

    Serial.print("Temp: "); Serial.print(temp); Serial.print("C  |  Hum: "); Serial.print(hum); Serial.println("%");

    if (client.connect(serveur, 80)) {
        String json = "{\"temperature\":" + String(temp,1) + ",\"humidite\":" + String(hum,1) + ",\"id_capteur\":\"sensor1\"}";

        client.println("POST /monitoring/insert.php HTTP/1.1");
        client.println("Host: 10.1.30.150");
        client.println("Content-Type: application/json");
        client.println("X-API-Key: CLE_SECRETE_123");
        client.print("Content-Length: "); client.println(json.length());
        client.println("Connection: close");
        client.println();
        client.println(json);

        delay(1000);

        String reponse = "";
        while (client.available()) {
            reponse += (char)client.read();
        }

        if (reponse.indexOf("\"ok\"") >= 0) {
            Serial.println("Envoye avec succes !");
        } else {
            Serial.println("Erreur serveur !");
            Serial.println(reponse);
        }

        client.stop();

    } else {
        Serial.println("Erreur connexion serveur !");
    }

    delay(10000);
}
