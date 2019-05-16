# Piattaforme Digitali per la Gestione del Territorio

## Studente:
 - Fraternali Lorenzo 
 - Matricola 266638
 - l.fraternali1@campus.uniurb.it https://github.com/lfraternali1

## Appello
 Primo appello sessione estiva: 05/06/2019

## Obiettivi
Il progetto TurismoMarche si pone i seguenti obiettivi:
  1. Trovare i punti di interesse turistico della città visitata e accedere a tutte le informazioni di interesse
  2. Permettere di aggiungere nuovi POI

## Componenti
Le componenti del progetto sono
  1. Web API, sviluppata in linguaggio **NODEJS + EXPRESS**
  2. Client Bot Telegram, sviluppato in linguaggio **PHP**

## Descrizione 

I dati sono stati acquisiti dal sito http://goodpa.regione.marche.it e succesivamente salvati sul database di FIREBASE.

**API**

Descrizione WEB API (GET e POST) e [documentazione](https://app.swaggerhub.com/apis/lfraternali1/TurismoMarche/1.0.0): 
 - Metodo GET:
   * L'API visualizza un semplice messaggio di benvenuto
   * L'API restituisce tutti i POI del comune richiesto
   * L'API restituisce informazioni sul POI richiesto
 - Metodo POST:
   * L'API può ricevere i dati di un nuovo POI ed inserirlo nel database

**BOT**

TurismoMarche è il client per la piattaforma, permette quindi di interrogare l'API implementata per accedere alle informazioni sui POI. 
I comandi messi a disposizione sono:
 - */start*: visualizza un semplice messaggio di benvenuto e mostra i comandi.
 - */comune*: restituisce l'elenco e il numero dei POI di un comune
 - */nome*: restituisce le informazioni sul POI richiesto
 
***Esempio di funzionamento:***
 
Digitando il comando \start apparira' un messaggio di benvenuto.

<a><img  height="650" src = "https://github.com/lfraternali1/ProgettoPDGT/blob/master/screen/start.png"/></a>


Dopo aver digitato il comando \comune e inserito il nome del comune viene restituita la lista dei POI con una breve descrizione.

<a><img  height="650" src = "https://github.com/lfraternali1/ProgettoPDGT/blob/master/screen/comune.png"/></a>


Dopo aver digitato il comando \nome e inserito il nome del POI vengono restituite tutte le informazioni.

<a><img  height="650" src = "https://github.com/lfraternali1/ProgettoPDGT/blob/master/screen/nome1.png"/></a>

<a><img  height="650" src = "https://github.com/lfraternali1/ProgettoPDGT/blob/master/screen/nome2.png"/></a>

<a><img  height="650" src = "https://github.com/lfraternali1/ProgettoPDGT/blob/master/screen/nome3.png"/></a>
 
 
## Link

Link per l'api: https://turismomarche.herokuapp.com/

Documentazione: https://app.swaggerhub.com/apis/lfraternali1/TurismoMarche/1.0.0

BOT:https://turismomarchebot.herokuapp.com/
