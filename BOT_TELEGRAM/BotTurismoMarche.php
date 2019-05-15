<?php

/*************************/
/* Comportamento del BOT */
/*************************/

require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/curl-lib.php');

// Array dove salvo lo stato, come indice utilizzo id della chat
$stato = [];
$last_update_filename = dirname(__FILE__) . '/last-update-id.txt';
while(1)
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
									  "&limit=1");
	// Recupero i dati riguardanti l'utente
	if(isset($dati->result[0])) 
	{
		$update_id = $dati->result[0]->update_id;
		$chat_id = $dati->result[0]->message->chat->id;
		$nome = $dati->result[0]->message->from->first_name;
		$testo = $dati->result[0]->message->text;

		// Controllo se l'utente aveva già scritto in precedenza
		if(false)
		{
			// Invia messaggio precedente
		}
		else 
		{
			// Se l'utente non aveva mai usato il bot invio un messaggio di
			// benvenuto
			$bnv = "Ciao ".$nome." :)";
			$bnv .= "\nBenvenuto in TurismoMarche!";
			$bnv .= "\nEcco la lista dei comandi:";
			$bnv .= "\n/comune -> Visualizza i POI di un comune specifico";
			$bnv .= "\n/nome -> Visualizza info sul POI cercato"; 
			http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
									  "&text=".urlencode($bnv)."");
		}
		// Memorizziamo il nuovo ID nel file
		file_put_contents($last_update_filename, $update_id);
	
	
		if ((isset($testo)) && 
			((!isset($stato[$chat_id])) ||
			($stato[(string)$chat_id] == 0)))
		{
			switch($testo)
			{
				// Comandi del BOT

				// Benvenuto nel Bot
				case "/start":
					// Invio messaggio di benvenuto
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
										      "&text=".urlencode($bnv)."");
				break;
			
				// Ricerca per comune
				case "/comune":
					// Invio del messaggio dove richiedo il nome del comune
					$msg = "Digita il comune del quale vuoi visualizzare i POI\n";
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
											  "&text=".urlencode($msg)."");
					// Cambio di stato
					$stato[(string)$chat_id] = 1;	
				
				break;
			
				// Ricerca per nome
				case "/nome":
					// Invio del messaggio dove richiedo il nome del POI
					$msg = "Digita il POI del quale vuoi visualizzare le info\n";
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
				 					          "&text=".urlencode($msg));
					// Cambio di stato
					$stato[(string)$chat_id] = 2;
				break;

				// Messaggio ricevuto non valido
				default:
					// Invio del messaggio di errore
					$msg = "Comando non valido!";
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
										      "&text=".urlencode($msg)."");
			}
		}
		else if (isset($testo) &&
				 ($stato[(string)$chat_id] == 1))
		{
			// Richiedo i dati usando l'API 
			$comunePOI = http_request(TURISMO_API."comune/".rawurlencode($testo));
			// Controllo che ci siano POI nel comune richiesto
			$numeroPOI = count(get_object_vars($comunePOI));
			if ($numeroPOI != 0)
			{
				// Invio messaggio con numero POI
				$msg = " *** Nel comune di ".$testo." ci sono ".
					   $numeroPOI." POI. ***";
				http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
									      "&text=".urlencode($msg)."");
			
				// Invio un messaggio per POI con le informazioni base
				foreach($comunePOI as $POI)
				{		
					$infoPOI =  "\nNome: ".$POI->Denominazione;
					$infoPOI .= "\nDescrizione:".$POI->DescTipoIt;
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
											  "&text=".urlencode($infoPOI)."");
				}
				// Invio messaggio per maggiori informazioni
				$msg = "*** ler informazioni più dettagliate su un POI utilizza".
				       " la ricerca per nome. ***";
				http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
									      "&text=".urlencode($msg)."");
			}
			else
			{
				// Non ci sono POI
				$msg = " Il comune di ".$testo." non è presente nel Database";
				http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
									      "&text=".urlencode($msg)."");
			}
			// Ritorno allo stato 0
			$stato[(string)$chat_id] = 0;
		}
		else if (isset($testo) &&
				 ($stato[(string)$chat_id] == 2))
		{
			// Richiedo i dati usando l'API 
			$nomePOI = http_request(TURISMO_API."POI/".rawurlencode($testo));
			if (isset($nomePOI))
			{	
				foreach ($nomePOI as $POI)
				{
					
					// Invio un messaggio per POI con le informazioni
					http_request(TELEGRAM_API."sendPhoto?".
									  "chat_id=".$chat_id.
									  "&photo=".urlencode($POI->patImmagine)."");
					$infoPOI  =  "\n\nNome: "           .$POI->Denominazione;
					$infoPOI .=  "\n\nDescTipoIt: "     .$POI->DescTipoIt;
					$infoPOI .=  "\n\nOrario Apertura: ".$POI->OrarioApertura;
					$infoPOI .=  "\n\nTelefono: "       .$POI->Telefono;
					$infoPOI .=  "\n\nEmail: "          .$POI->Email;
					$infoPOI .=  "\n\nSito Web: "       .$POI->SitoWeb;
					http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
					   				          "&text=".urlencode($infoPOI)."");
					http_request(TELEGRAM_API."sendLocation?".
					  						  "chat_id=".$chat_id.
											  "&longitude=".$POI->Longitudine.
											  "&latitude=".$POI->Latitudine."");
				}
			}
			else
			{
				// Il poi non esiste
				$msg = " Il POI: ".$testo." non è presente nel Database";
				http_request(TELEGRAM_API."sendmessage?chat_id=".$chat_id.
										  "&text=".urlencode($msg)."");
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