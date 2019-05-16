const PORT = process.env.PORT || 3000

// Definizione delle librerie.
const express = require("./node_modules/express")
const app = express()
const body = require("./node_modules/body-parser");
app.use(body.urlencoded({ extended: false }));

// Autentificazione con i privilegi di admin
const admin = require("./node_modules/firebase-admin");

// Inizializza l'app con un account di servizio, concedendogli i privilegi di
// amministratore

admin.initializeApp({
  credential: admin.credential.cert({
    projectId: process.env.FIREBASE_PROJECT_ID,
    private_key: process.env.FIREBASE_PRIVATE_KEY.replace(/\\n/g, '\n'), 
    client_email: process.env.FIREBASE_CLIENT_EMAIL,
  }),
  databaseURL: "https://turismomarche-5a733.firebaseio.com"
});

/*
// Recupero la chiave dell'account di servizio dal file JSON
const serviceAccount = require("./turismomarche-5a733-firebase-adminsdk-kip1e-e758f4f121.json");

admin.initializeApp({
  credential: admin.credential.cert(serviceAccount),
  databaseURL: "https://turismomarche-5a733.firebaseio.com"
});
*/

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

// POST: aggiunge un nuovo punto di interesse 
app.post("/POI/aggiungi",(req, res)=>{
  var poiDaAggiungere =
  {
    civico:         req.body.civico == undefined ?
                    "" : req.body.civico,
    codIStatComune: req.body.codIStatComune == undefined ?
                    "" : req.body.codIStatComune,
    denominazione:  req.body.denominazione == undefined ? 
                    "" : req.body.denominazione,
    descTipoIt:     req.body.descTipoIt == undefined ? 
                    "" : req.body.descTipoIt,
    didaImmagineIt: req.body.didaImmagineIt == undefined ? 
                    "" : req.body.didaImmagineIt,
    email:          req.body.email == undefined ? 
                    "" : req.body.email,
    indirizzo:      req.body.indirizzo == undefined ? 
                    "" : req.body.indirizzo,
    latitudine:     req.body.latitudine == undefined ? 
                    "" : req.body.latitudine,
    longitudine:    req.body.longitudine == undefined ? 
                    "" : req.body.longitudine,
    orarioApertura: req.body.orarioApertura == undefined ? 
                    "" : req.body.orarioApertura,
    sitoWeb:        req.body.sitoWeb == undefined ? 
                    "" :  req.body.sitoWeb,
    telefono:       req.body.telefono == undefined ? 
                    "" :  req.body.telefono,
    comune:         req.body.comune == undefined ? 
                    "" : req.body.comune,
    patImmagine:    req.body.patImmagine == undefined ? 
                    "" : req.body.patImmagine,
    idPOI:          req.body.idPOI 
  }

  if (poiDaAggiungere.idPOI == null || 
      poiDaAggiungere.idPOI == undefined)
  {
    console.log("ID POI non valido")
    res.sendStatus(400)
  }
  else
  {
	db.ref("/POI")
    .orderByChild("IdPOI")
	.equalTo(poiDaAggiungere.idPOI)
	.once("value", snap => {
      var id = snap.val()
	  if (id == undefined)
	  {
        var nuovoPOI = db.ref("/POI")
                       .push()
                       .set({ 
                         Civico:         poiDaAggiungere.civico,
                         CodIStatComune: poiDaAggiungere.codIStatComune,
                         Denominazione:  poiDaAggiungere.denominazione,
                         DescTipoIt:     poiDaAggiungere.descTipoIt,
                         DidaImmagineIt: poiDaAggiungere.didaImmagineIt,
                         Email:          poiDaAggiungere.email,
                         IdPOI:          poiDaAggiungere.idPOI,
                         Indirizzo:      poiDaAggiungere.indirizzo,
                         Latitudine:     poiDaAggiungere.latitudine,
                         Longitudine:    poiDaAggiungere.longitudine,
                         OrarioApertura: poiDaAggiungere.orarioApertura,
                         SitoWeb:        poiDaAggiungere.sitoWeb,
	                 Telefono:       poiDaAggiungere.telefono,
                         comune:         poiDaAggiungere.comune,
                         patImmagine:    poiDaAggiungere.patImmagine})
	                     console.log("*** Nuovo POI aggiunto al DB ***" +
	  	  		                     "\nIDPOI:" + poiDaAggiungere.idPOI + 
                                     "\nNome:"+ poiDaAggiungere.denominazione)
	                     res.sendStatus(200)
      }
	  else
	  {
	    console.log("ID POI gia' assegnato")
                        res.sendStatus(409)
      }
    })
  }
})

// localhost:PORT
app.listen(PORT, () => {
  console.log("Server in ascolto sulla porta: " + PORT)
})
