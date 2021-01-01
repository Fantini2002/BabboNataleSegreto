# BabboNataleSegreto

Una semplice pagina in PHP per fare un Babbo Natale segreto anche in tempi di Covid-19

## Funzionamento
* **Estrai**
    Lo script seleziona causalmente un nome fra quelli inseriti nella tabella "nomi", è possibile rieseguire il sorteggio solo una volta nel caso in cui sia stato estratto il proprio nome.
    Per evitare sorteggi multipli viene memorizzata sul dispisitivo tramite cookie la persona estratta e nel database l'indirizzo ip del dispositivo. Provando a estrarre nuovamente si visualizzerà il nome della persona sorteggiata precedentemente o un messaggio di errore (in caso di mancanza del cookie)
* **Invia**
    Se è presente il cookie generato in fase di estrazione viene automaticamente selezionato il destinatario altrimenti viene richiesto di inserire all'utente cognome e nome del destinatario. L'invio del messaggio avviene tramite mail utilizzando gli indirizzi presenti nella tabella "mail", una volta che l'invio è stato eseguito con successo viene segnato il destinatario nella tabella "mail" in modo che non possa più ricevere mail attraverso questo script e viene memorizzato l'indirizzo ip del mittente nella tabella "utentiInvio" in modo che l'utente non possa inviare altri messaggi.

## Utilizzo
1. Creare le seguenti tabelle nel database
    * ```
        CREATE TABLE `nomi` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `nome` varchar(50) DEFAULT NULL,
        `estratto` tinyint(1) DEFAULT '0',
        PRIMARY KEY (`id`)
        );
    * ```
        CREATE TABLE `utenti` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `ip` varchar(15) DEFAULT NULL,
        PRIMARY KEY (`id`)
        );
    * ```
        CREATE TABLE `mail` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `nome` varchar(50) NOT NULL,
        `mail` varchar(50) NOT NULL,
        `inviato` tinyint(1) NOT NULL,
        PRIMARY KEY (`id`)
        );
    * ```
        CREATE TABLE `utentiInvio` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ip` varchar(15) NOT NULL,
        PRIMARY KEY (`id`)
        );
2. Inserire i dati nelle tabelle "nomi" e "mail"
3. Clonare i file della repo e impostare
    * Host
    * Username
    * Password
    * DB name
    * Folder

## Miglioramenti
* Utilizzare solo due tabelle:
    * "Partecipanti" dove memorizzare nome, mail, estratto, recapitato
    * "Utenti" dove memorizzare per ogni indirizzo ip estratto, inviato
* Aggiungere una pagina in cui mostrare lo stato delle estrazioni e quello degli invii

## Risorse esterne
* https://github.com/mgalante/jquery.redirect
* <span>Photo by <a href="https://unsplash.com/@emilybernal?utm_source=unsplash&amp;utm_medium=referral&amp;utm_content=creditCopyText">Emily Bernal</a> on <a href="https://unsplash.com/@emilybernal?utm_source=unsplash&amp;utm_medium=referral&amp;utm_content=creditCopyText">Unsplash</a></span> (sfondo)