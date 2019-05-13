const PORT = process.env.PORT || 3000

// Definizione delle librerie.
const express = require("./node_modules/express")
const app = express()

const body = require("./node_modules/body-parser");
app.use(body.urlencoded({ extended: false }));

// Autentificazione con i privilegi di admin
const admin = require("./node_modules/firebase-admin");

// Recupero la chiave dell'account di servizio dal file JSON
const serviceAccount = require("./turismomarche-5a733-firebase-adminsdk-kip1e-e758f4f121.json");

// Inizializza l'app con un account di servizio, concedendogli i privilegi di
// amministratore
admin.initializeApp({
  credential: admin.credential.cert(serviceAccount),
  databaseURL: "https://turismomarche-5a733.firebaseio.com"
});

// Come amministratore, l'app ha accesso per leggere e scrivere tutti i dati,
// indipendentemente dalle regole di sicurezza
const db = admin.database();

// localhost:PORT
app.listen(PORT, () => {
  console.log("Server in ascolto sulla porta: " + PORT)
})
