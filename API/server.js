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

//GET: Prova API
app.get('/', function (req, res) {
  res.send("Benvenuto in TurismoMarche!");
});

// GET: restituisce tutti i POI di un comune
app.get("/comune/:comune", (req, res)=>{
  db.ref("/POI")
  .orderByChild("comune")
  .equalTo(req.params.comune)
  .once("value", snap => {
    console.log(snap.val())
    res.send(snap.val())
  })
})

// GET: restituisce il POI cercato
app.get("/POI/:nome", (req, res)=>{
  db.ref("/POI")
  .orderByChild("Denominazione")
  .equalTo(req.params.nome)
  .once("value", snap => {
    console.log(snap.val())
    res.send(snap.val())
  })
})

// localhost:PORT
app.listen(PORT, () => {
  console.log("Server in ascolto sulla porta: " + PORT)
})
