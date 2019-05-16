<?php

/*************************/
/* Comportamento del BOT */
/*************************/
require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/curl-lib.php');

// Array dove salvo lo stato, come indice utilizzo id della chat
$stato = [];
$last_update_filename = dirname(__FILE__) . '/last-update-id.txt';
while(true)
{
    // Carica l'ID dell'ultimo aggiornamento da file
	if(file_exists($last_update_filename))
	{
		$last_update = intval(@file_get_contents($last_update_filename));
	}
	else 
	{
		$last_update = 0;
	}
	$dati = http_request(TELEGRAM_API."getUpdates?" .
						              "offset=".($last_update + 1).
									  "&limit=1",
									  "GET");
	// Recupero i dati riguardanti l'utente
	if(isset($dati->result[0])) 
	{
		$update_id = $dati->result[0]->update_id;
		$chat_id   = $dati->result[0]->message->chat->id;
		$user_id   = $dati->result[0]->message->from->id;
		$nome      = $dati->result[0]->message->from->first_name;
		$testo     = $dati->result[0]->message->text;

		// Controllo nel database se l'utente aveva già scritto al bot
		$infoUtente = http_request(FIREBASE."Utenti/".$user_id.".json",
					               "GET");
		// Se l'utente aveva scritto in precedenza al Bot 
		if(isset ($infoUtente))
		{
			$infoUtente->ultMsg = $testo;
			// Tengo traccia di alcune informazioni dell'utente
			http_request(FIREBASE."Utenti/".$user_id.".json",
								  "PATCH",
								  $infoUtente);
		}
		else 
		{
			// Se l'utente non aveva mai usato il BOT salvo le informazioni 
			// sul database
			$nuovoUtente = [
				'user_id' => $user_id,
				'chat_id' => $chat_id,
				'ultMsg' => $testo
			];
			http_request(FIREBASE."Utenti/".$user_id.".json",
								  "PUT",
								  $nuovoUtente);
			// Modifico il testo cosi da inviare successivamente il messaggio di 
			// benvenuto
			$testo = "/start";
		}
		// Memorizziamo il nuovo ID nel file
		file_put_contents($last_update_filename, $update_id);
	
	
		if ((isset($testo)) && 
			((!isset($stato[$chat_id])) ||
			($stato[(string)$chat_id] == 0)))
		{
			switch($testo)
			{
				// Benvenuto nel Bot
				case "/start":
					$bnv  = "\nCiao ".$nome; 
					$bnv .= "\nBenvenuto in TurismoMarche!";
					$bnv .= "\nEcco la lista dei comandi ( /start ):";
					$bnv .= "\n/comune -> Visualizza i POI di un comune specifico.";
					$bnv .= "\n/nome   -> Visualizza informazioni sul POI cercato.";

					// Invio messaggio di benvenuto
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
										      "&text=".urlencode($bnv),
											  "GET");
				break;
			
				// Ricerca per comune
				case "/comune":
					// Invio del messaggio dove richiedo il nome del comune
					$msg = "Digita il comune del quale vuoi visualizzare i POI.\n";
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
											  "&text=".urlencode($msg),
											  "GET");
					// Cambio di stato
					$stato[(string)$chat_id] = 1;	
				
				break;
			
				// Ricerca per nome
				case "/nome":
					// Invio del messaggio dove richiedo il nome del POI
					$msg = "Digita il POI del quale vuoi visualizzare le info.\n";
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
				 					          "&text=".urlencode($msg),
											  "GET");
					// Cambio di stato
					$stato[(string)$chat_id] = 2;
				break;

				// Messaggio ricevuto non valido
				default:
					// Invio del messaggio di errore
					$msg = "Comando non valido! Visualizza i comandi con /start.";
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
										      "&text=".urlencode($msg),
											  "GET");
			}
		}
		else if (isset($testo) &&
				 ($stato[(string)$chat_id] == 1))
		{
			// Richiedo i dati usando l'API 
			$comunePOI = http_request(TURISMO_API."comune/".rawurlencode($testo),
									  "GET");
			if ($comunePOI)
			{
				// Controllo che ci siano POI nel comune richiesto
				$numeroPOI = count(get_object_vars($comunePOI));
				// Invio messaggio con numero POI
				$msg = " *** Nel comune di ".$testo." ci sono 0 POI. ***";
				http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
									      "&text=".urlencode($msg),
										  "GET");
			
				// Invio un messaggio per POI con le informazioni base
				foreach($comunePOI as $POI)
				{		
					$infoPOI =  "\nNome: ".$POI->Denominazione;
					$infoPOI .= "\nDescrizione:".$POI->DescTipoIt;
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
											  "&text=".urlencode($infoPOI),
											  "GET");
				}
				// Invio messaggio per maggiori informazioni
				$msg = "*** Per informazioni più dettagliate su un POI utilizza".
				       " la ricerca per nome. ***";
				http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
									      "&text=".urlencode($msg),
										  "GET");
			}
			else
			{
				// Non ci sono POI
				$msg = " Il comune di ".$testo." non è presente nel Database.";
				http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
									      "&text=".urlencode($msg),
										  "GET");
			}
			// Ritorno allo stato 0
			$stato[(string)$chat_id] = 0;
		}
		else if (isset($testo) &&
				 ($stato[(string)$chat_id] == 2))
		{
			// Richiedo i dati usando l'API 
			$nomePOI = http_request(TURISMO_API."POI/".rawurlencode($testo),
									"GET");
			if (isset($nomePOI))
			{	
				foreach ($nomePOI as $POI)
				{
					
					// Se il link è disponibile invio una foto del POI
					$r = http_request(TELEGRAM_API."sendPhoto?".
						 				           "chat_id=".$chat_id.
												   "&photo=".urlencode(
                                                             $POI->patImmagine),
									               "GET");
					if (!$r)
					{
						$msg = "L'immagine di ".$testo." non è disponibile. :(\n";
						http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
										          "&text=".urlencode($msg),
												  "GET");
					}
					// Invio di un messaggio con le info rigurdanti il POI
					$infoPOI  =  "\n\nNome: "           .$POI->Denominazione;
					$infoPOI .=  "\n\nDescrizione: "    .$POI->DescTipoIt;
					$infoPOI .=  "\n\nOrario Apertura: ".$POI->OrarioApertura;
					$infoPOI .=  "\n\nTelefono: "       .$POI->Telefono;
					$infoPOI .=  "\n\nEmail: "          .$POI->Email;
					$infoPOI .=  "\n\nSito Web: "       .$POI->SitoWeb;
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
					   				          "&text=".urlencode($infoPOI),
											  "GET");
					// Se possibile invio la posizione del POI
					if ((isset($POI->Longitudine)) &&
						(isset($POI->Latitudine)))
					{
						http_request(TELEGRAM_API."sendLocation?".
												  "chat_id=".$chat_id.
												  "&longitude=".$POI->Longitudine.
												  "&latitude=".$POI->Latitudine,
												  "GET");
					}
					else
					{	
						$msg = "La posizione di ".$testo." non è disponibile. :(\n";
						http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
										          "&text=".urlencode($msg),
												  "GET");
					}
				}
			}
			else
			{
				// Il poi non esiste
				$msg = " Il POI: ".$testo." non è presente nel Database.";
				http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
										  "&text=".urlencode($msg),
										  "GET");
			}
			//Ritorno allo stato 0
			$stato[(string)$chat_id] = 0;

		}
		else
		{
			// Ritorno allo stato 0
			$stato[(string)$chat_id] = 0;
		}
	}
}
?>
