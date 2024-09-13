
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <ArduinoJson.h>
#include <Arduino_JSON.h>


const char* ssid = "AndroidAP";
const char* password = "@136313631363@";




using namespace std;

void setup() {
 
 Serial.begin(115200);
 WiFi.begin(ssid, password);
 Serial.println("connecting");
 
 while (WiFi.status() != WL_CONNECTED)
 {
   delay(500);
   Serial.print(".");
 }
   Serial.println("");
   Serial.print("connecting to the local wifi, IP ADDRESS :");
   Serial.println(WiFi.localIP());
 

}

void loop()
 {
    // Call methods
    get_params();
    report_params(); 
    packet_test_connection();
    
 }



// RECEIVE VALUES FROM SERVER SIDE 
void get_params()
{ 

    // The URL of your site.
	const char* serverName = "http://rabipoor.ir/iot/nodemcu_led/mcu_feeds.php";
    // Define connection properties
    WiFiClient client;
    HTTPClient http;
    
    http.begin(client, serverName);
    int httpCode = http.GET();
    if(httpCode == 200){

      String response = http.getString();
      JSONVar gotObject = JSON.parse(response);
      if (JSON.typeof(gotObject) == "undefined")
        {
        Serial.println("failed! something went wrong");
        return;
        }
      Serial.print("JSON object = ");
      Serial.println(gotObject);
      // Parse received data from server and initial the mcu pinout
      JSONVar gpioKeys = gotObject.keys();
      for(int i = 0; i < gpioKeys.length(); i++)
      {
        JSONVar gpioValue = gotObject[gpioKeys[i]];
        Serial.print("GPIO: ");
        Serial.print(gpioKeys[i]);
        Serial.print(" - SET to: ");
        Serial.println(gpioValue);
        pinMode(atoi(gpioKeys[i]), OUTPUT);
        digitalWrite(atoi(gpioKeys[i]), atoi(gpioValue));
      }
    }else{
        Serial.print("no successfull connection");
        Serial.print(httpCode);
        http.end();
        delay(500);
      }

}



// SEND VALUES FROM MCU TO SERVER SIDE
void report_params()
{
	
	  // The URL of your site.
	  const char* serverNameTwo = "http://rabipoor.ir/iot/nodemcu_led/mcu_report.php";
      // Define connection properties
      WiFiClient client;   
      HTTPClient http2;   
      http2.begin(client, serverNameTwo);
      int httpCode2 = http2.GET();
      Serial.print("http2 :");
      Serial.println(httpCode2);

      // Pin status
      String gpios [] = {String(digitalRead(16)), String(digitalRead(5)), String(digitalRead(4)), String(digitalRead(0))};
       
      // Calculate the sensor entry
      float vRef = 3.3;
      float resulotion = vRef/1023;
      float temperature = analogRead(A0);
      temperature = (temperature * resulotion)*100;
      
      Serial.print("temperature :");
      Serial.print(temperature);
      String sensor = String(temperature);

      // Initial the report msg for server
      http2.addHeader("Content-Type", "application/x-www-form-urlencoded");
      String contentData = "sensor_state="+sensor+"&gpio16="+gpios[0]+"&gpio5="+gpios[1]+"&gpio4="+gpios[2]+"&gpio0="+gpios[3];
      // Check the connection state
      int httpResponseCode = http2.POST(contentData);
      if(httpResponseCode > 0)
      {
        Serial.print("http response code :");
        Serial.println(httpResponseCode);
        Serial.println(contentData);
      }else{
        Serial.print(" http response error");
        Serial.println(httpResponseCode);
      }
        http2.end();
        delay(500);
        
  }

  // RECEIVE & SEND A PACKET TO SERVER
  void packet_test_connection()
  {
    // Function to dealing with android
    // first receive a packet from android and send the same packet
    // packet will change randomly from android side

    // Define url
    const char* PACKET_URL = "http://rabipoor.ir/iot/nodemcu_led/mcu_packet.php";
    WiFiClient client;
    HTTPClient packetHttp;
    packetHttp.begin(client, PACKET_URL);
    int packetResponseCode = packetHttp.GET();
    Serial.print("packetResponseCode :");
    Serial.println(packetResponseCode);
    
    // Receive packet and parse objet to a string
    String response = packetHttp.getString();
    JSONVar object = JSON.parse(response);
    String result = object["android_packet"];
    Serial.print("result :");
    Serial.println(result);
    // Send the same packet as answer to server side 
    packetHttp.addHeader("Content-Type", "application/x-www-form-urlencoded");
    String contentData = "mcu_packet="+result;
    int httpAnswerCode = packetHttp.POST(contentData);
    if(httpAnswerCode > 0)
    {
      Serial.print("answercode : ");
      Serial.println(httpAnswerCode);
      Serial.println(contentData);
    }else{
      Serial.print(" httpAnswerCode error");
      Serial.println(httpAnswerCode);
    }
    packetHttp.end();
  }
  
    

