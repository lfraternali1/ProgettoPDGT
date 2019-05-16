<?php
/********************************************************/
/* Semplice libreria per le creazione di richieste HTTP */
/********************************************************/

function http_request($url, 
					  $method, 
					  $body = null) 
{
	// Preparo l'handle del cURL
	$handle = curl_init($url);
    if($handle == false) 
	{
        echo ("Ops, cURL non funziona\n");
		return (false);
    }
	curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_USERAGENT, "Bot Telegram TurismoMarche");
	curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
	// Gestione richieste che prevedono l'invio di dati
	if (($method === "POST")  ||
	    ($method === "PUT")   ||
	    ($method === "PATCH"))
	{
		if ($body)
		{
			$json = json_encode($body);
			curl_setopt($handle, CURLOPT_POSTFIELDS, $json);
		}
	}

    // Esecuzione della richiesta
	// $response = contenuto della risposta testuale
    $response = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    if($status != 200)
	{
		echo ("Errore! Codice: ".$status);		
	    return (false);
    }

    // Decodifica della risposta JSON
    return json_decode($response);
}
?>
