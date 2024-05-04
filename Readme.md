# Checkmate: Protezione del Made in Italy contro la Contraffazione

Il progetto Checkmate è una soluzione innovativa sviluppata per combattere la contraffazione dei prodotti Made in Italy. Attraverso l'utilizzo di tecnologie avanzate, tra cui blockchain, smart packaging, QR code e intelligenza artificiale (IA), Checkmate garantisce l'autenticità dei prodotti e protegge l'integrità del marchio Made in Italy.

# Repository del Progetto

Questo repository contiene diversi componenti chiave del nostro progetto. Di seguito è riportato un breve riepilogo di ciascuno:

## Contenuti del Repository
1. **UI.pdf**: L'interfaccia utente del gestionale per la generazione dei sigilli e il tracciamento dei prodotti.
2. **htdocs/**: Il codice sorgente del gestionale.
3. **blochchain_testnet/**: Contiene un esempio di integrazione su blockchain testnet bitcoin effettuando una transazione su blockchain bitcoin testnet.
4. **assets/**: Contiene dei file che aiutano a capire meglio il funzionamento del sistema.
   - **flusso.txt**: Flusso di processo del sistema.
   - **architecture.png**: Illustrazione dell'architettura logica del progetto.
   - **qr_support.png**: Rappresentazione del supporto fisico fornito da IPZS alle aziende produttrici dove andrà ad essere stampato il QR Code, tra i sistemi di sicurezza identificabili in questa immagine vi sono microtesti e guilloche.
   - **SeQR_code.png**: Esempio di QR Code che sarà stampato sul supporto cartaceo, in questa immagine è possibile apprezzare come la forma di ciascun modulo del QR Code sia dipendente dai moduli adiacenti e pertanto una rappresentazione accurata di tale codice richiede una stampa ad una risoluzione maggiore rispetto a quella richiesta da un QR Code tradizionale.
   - **RFID.png**: Esempio concettuale del sigillo elettronico. Da questa immagine si evince che la parte del circuito relativa al sigillo (la parte a destra del tag RFID) è composta da un pattern geometrico intrigato e fitto, idealmente realizzato su scala micrometrica attraverso tecniche di litografia, al fine di rendere estremamente complesso il ripristino delle condizioni iniziali del sigillo una volta invalidato. 

## Progressi del Progetto
- [x] Studio di fattibilità.
- [x] Design della UI.
- [x] Design dell'architettura.
- [x] Integrazione con Registro delle Imprese per determinazione dei dati delle aziende a partire dalla Partita IVA.
- [x] Implementazione della componente grafica del backoffice.
- [x] Prototipo di integrazione con blockchain testnet bitcoin.

## Cose da Fare
- [ ] Implementare la registrazione dei dati su blockchain, effettuando un'integrazione del codice già presente.
- [ ] Sviluppo dell'app destinata al consumatore finale.

## Come Testare i Software nel Repository
1. **Testare il gestionale in `htdocs/`**
   - Avvia un'istanza di webserver (ad esempio Apache) con modulo PHP abilitato.
   - Carica i file nella web root.
   - Crea un database con lo schema presente in `schema.sql`.
   - Modifica il file `business_logic/database.php` affinché l'app punti al database.
2. **Testare l'esempio di integrazione su blockchain in `blockchain_testnet/`**
   - Configura la variabile `myWallet` in `index.ts`.
   - Esegui `npm install`, `tsc index.ts`, e `node index.js`.
   - Questo script mostra l'integrazione su blockchain effettuando una transazione su blockchain bitcoin testnet.
