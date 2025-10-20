#include <EEPROM.h>
#include "GravityTDS.h"
#include "WiFi.h"
#include "HTTPClient.h"
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// === Pin Sensor ===
#define PH_SENSOR_PIN 34
#define TURBIDITY_PIN 32
#define TDS_SENSOR_PIN 33

// === Objek & Variabel TDS ===
GravityTDS gravityTds;
float temperature = 25.0;
float tdsValue = 0.0;

// === Variabel pH ===
int buffer_arr[10], temp;
float ph_act;

// Variabel Fuzzy
float nilaiCrisp = 0.0;

// Variabel WiFi
const char* ssid = "Koxstgh";
const char* pass = "12345678";

// Alamat server
const char* host = "10.246.43.168";  // Ganti sesuai IP server Anda

// === Objek LCD (alamat bisa 0x27 atau 0x3F) ===
LiquidCrystal_I2C lcd(0x27, 16, 2);

// === Forward declaration ===
String getFuzzyStatus(float ph, float tds, float turbidity);

void setup() {
  Serial.begin(9600);
  analogReadResolution(12);  // Resolusi ADC 12-bit (0–4095)

  WiFi.begin(ssid, pass);
  Serial.print("Menghubungkan ke WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Terhubung!");
  Serial.print("IP ESP32: ");
  Serial.println(WiFi.localIP());

  EEPROM.begin(512);
  gravityTds.setPin(TDS_SENSOR_PIN);
  gravityTds.setAref(3.3);
  gravityTds.setAdcRange(4096);
  gravityTds.begin();

  Serial.println("Sensor pH, Turbidity & TDS Siap...");
  Serial.print("Nilai kValue saat ini (kalibrasi TDS): ");
  Serial.println(gravityTds.getKvalue(), 4);

  // === Inisialisasi LCD ===
  lcd.begin();      // pakai begin() sesuai library kamu
  lcd.backlight();
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Monitoring Air");
  delay(2000);
}

void loop() {
  // === Pembacaan pH ===
  for (int i = 0; i < 10; i++) {
    buffer_arr[i] = analogRead(PH_SENSOR_PIN);
    delay(30);
  }
  for (int i = 0; i < 9; i++) {
    for (int j = i + 1; j < 10; j++) {
      if (buffer_arr[i] > buffer_arr[j]) {
        temp = buffer_arr[i];
        buffer_arr[i] = buffer_arr[j];
        buffer_arr[j] = temp;
      }
    }
  }
  int avgval = 0;
  for (int i = 2; i < 8; i++) {
    avgval += buffer_arr[i];
  }
  ph_act = -4.81 * ((avgval / 6.0) * 3.3 / 4095.0) + 19.87;

  // === Pembacaan Turbidity ===
  int turbidity_adc = analogRead(TURBIDITY_PIN);
  float turbidity_voltage = turbidity_adc * 3.3 / 4095.0;
  float turbidity_ntu = -140.744 * turbidity_voltage + 196.195;
  if (turbidity_ntu <= 0) turbidity_ntu = 1;

  // === Pembacaan TDS ===
  gravityTds.setTemperature(temperature);
  gravityTds.update();
  tdsValue = gravityTds.getTdsValue();

  // === Fuzzy Mamdani Logic: Status Air ===
  String status = getFuzzyStatus(ph_act, tdsValue, turbidity_ntu);

  Serial.println("===== PEMBACAAN SENSOR =====");
  Serial.print("Nilai pH: ");
  Serial.println(ph_act, 2);
  Serial.print("Tegangan NTU: ");
  Serial.print(turbidity_voltage, 3);
  Serial.println(" V");
  Serial.print("NTU: ");
  Serial.println(turbidity_ntu, 1);
  Serial.print("TDS: ");
  Serial.print(tdsValue, 0);
  Serial.println(" ppm");
  Serial.print("Status Air: ");
  Serial.println(status);
  Serial.print("Nilai Crisp Fuzzy: ");
  Serial.println(nilaiCrisp, 2);
  Serial.println("------------------------------");


  // === Tampilkan ke LCD 16x2 secara bergantian ===
  static int lcdPage = 0;
  static unsigned long lastSwitch = 0;

  if (millis() - lastSwitch > 2000) { // ganti tampilan tiap 2 detik
    lcd.clear();
    if (lcdPage == 0) {
      // Halaman 1: Sensor
      lcd.setCursor(0, 0);
      lcd.print("pH:");
      lcd.print(ph_act, 1);
      lcd.print(" TDS:");
      lcd.print(tdsValue, 0);

      lcd.setCursor(0, 1);
      lcd.print("NTU:");
      lcd.print(turbidity_ntu, 1);
    } else {
      // Halaman 2: Status
      lcd.setCursor(0, 0);
      lcd.print("Status:");
      lcd.print(status);

      lcd.setCursor(0, 1);
      lcd.print("Fuzzy=");
      lcd.print(nilaiCrisp, 1);
    }
    lcdPage = 1 - lcdPage;  // toggle halaman (0→1→0)
    lastSwitch = millis();
  }

  // === Kirim data ke server ===
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = "http://" + String(host) + "/monitoringair/kirimdata.php";
    url += "?ph=" + String(ph_act, 2);
    url += "&tds=" + String(tdsValue, 0);
    url += "&turbidity=" + String(turbidity_ntu, 1);

    http.begin(url);
    int httpCode = http.GET();
    if (httpCode > 0) {
      String response = http.getString();
      Serial.println("Respon Server: " + response);
    } else {
      Serial.print("Gagal kirim data. Kode: ");
      Serial.println(httpCode);
    }
    http.end();
  } else {
    Serial.println("WiFi Terputus. Tidak bisa kirim data.");
  }

  delay(500); // biar loop tetap berjalan cepat, LCD diatur via millis()
}

// === Fuzzy Mamdani untuk Kelayakan Air ===
String getFuzzyStatus(float ph, float tds, float turbidity) {
  // pH
  float ph_rendah = 0.0, ph_normal = 0.0, ph_tinggi = 0.0;
  if (ph <= 6.5) ph_rendah = 1.0;
  else if (ph > 6.5 && ph < 7.0) ph_rendah = (7.0 - ph) / 0.5;
  if (ph >= 6.5 && ph <= 8.5) ph_normal = 1.0 - fabs(ph - 7.5) / 1.0;
  if (ph >= 8.0 && ph < 8.5) ph_tinggi = (ph - 8.0) / 0.5;
  else if (ph >= 8.5) ph_tinggi = 1.0;

  // TDS
  float tds_rendah = 0.0, tds_tinggi = 0.0;
  if (tds <= 300) tds_rendah = 1.0;
  else if (tds > 300 && tds <= 500) tds_rendah = (500 - tds) / 200.0;
  if (tds >= 500 && tds <= 1000) tds_tinggi = (tds - 500) / 500.0;
  else if (tds > 1000) tds_tinggi = 1.0;

  // Turbidity
  float turb_jernih = 0.0, turb_keruh = 0.0;
  if (turbidity <= 2.0) turb_jernih = 1.0;
  else if (turbidity > 2.0 && turbidity <= 5.0) turb_jernih = (5.0 - turbidity) / 3.0;
  if (turbidity >= 5.0 && turbidity <= 10.0) turb_keruh = (turbidity - 5.0) / 5.0;
  else if (turbidity > 10.0) turb_keruh = 1.0;

  // Rule base
  float R1 = min(ph_normal, min(tds_rendah, turb_jernih));       // Layak
  float R2 = min(ph_normal, min(tds_rendah, turb_keruh));        // Bersyarat
  float R3 = min(ph_rendah, max(tds_tinggi, turb_keruh));        // Tidak Layak
  float R4 = min(ph_tinggi, max(tds_tinggi, turb_keruh));        // Tidak Layak
  float R5 = min(ph_normal, min(tds_tinggi, turb_jernih));       // Bersyarat

  float sum_rule = R1 + R2 + R3 + R4 + R5 + 0.0001;
  float defuzz = (R1 * 100 + R2 * 60 + R3 * 20 + R4 * 20 + R5 * 60) / sum_rule;

  nilaiCrisp = defuzz;
  if (defuzz >= 80.0) return "Layak";
  else if (defuzz >= 50.0) return "Bersyarat";
  else return "TdkLayak";
}
