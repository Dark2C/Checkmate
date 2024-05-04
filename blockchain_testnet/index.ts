//@ts-ignore
import * as bitcoin from 'bitcoinjs-lib';
import { Psbt } from 'bitcoinjs-lib';
import { ECPairInterface, ECPairFactory, ECPairAPI, TinySecp256k1Interface } from 'ecpair';
globalThis.fetch = require('node-fetch');

const myWallet = {
    address: '...',
    privateKey: '...',
    blockcypherToken: '...'
};

// funzione che prende in input la chiave privata del wallet di partenza e un wallet di destinazione e un importo
// e restituisce il codice di transazione (txid)
async function sendTransaction(privateKey: string, toAddress: string): Promise<string> {
    const network = bitcoin.networks.testnet;

    const tinysecp: TinySecp256k1Interface = require('tiny-secp256k1');
    const ECPair: ECPairAPI = ECPairFactory(tinysecp);
    const keyPair: ECPairInterface = ECPair.fromWIF(privateKey, network);

    const pubKey = keyPair.publicKey;
    const address = bitcoin.payments.p2pkh({ pubkey: pubKey, network }).address!;
    const unspent = await fetch(`https://api.blockcypher.com/v1/btc/test3/addrs/${address}?unspentOnly=true`).then(response => response.json());
    const utxos = [...(unspent.txrefs || []), ...(unspent.unconfirmed_txrefs || [])];

    if (utxos.length === 0) {
        throw new Error('No transactions available!');
    }
    const psbt = new Psbt({ network });
    const totalAmount = unspent.final_balance;
    const satoshiToSend = 1;
    const fee = 3999;
    for (let i = 0; i < utxos.length; i++) {
        const utxo = utxos[i];
        // recupero l'utxo dalla API
        const utxoFromApi = await fetch(`https://api.blockcypher.com/v1/btc/test3/txs/${utxo.tx_hash}?includeHex=true`).then(response => response.json());
        // aggiungo l'utxo alla transazione (lo script lo recupero da output.script)
        psbt.addInput({
            hash: utxo.tx_hash,
            index: utxo.tx_output_n,
            nonWitnessUtxo: Buffer.from(utxoFromApi.hex, 'hex')
        });
    }
    if (totalAmount < satoshiToSend + fee) {
        console.log('Insufficient balance');
        console.log('Total amount: ', totalAmount);
        console.log('Satoshi to send: ', satoshiToSend + fee);
        return Promise.reject(new Error('Insufficient balance'));
    }

    const change = totalAmount - satoshiToSend - fee;
    psbt.addOutput({ address: toAddress, value: satoshiToSend });
    psbt.addOutput({ address: address, value: change });

    for (let i = 0; i < utxos.length; i++) {
        try {
            psbt.signInput(i, keyPair);
        } catch (_) { }
    }

    psbt.finalizeAllInputs();
    const txHex = psbt.extractTransaction().toHex();

    const result = await fetch(`https://api.blockcypher.com/v1/btc/test3/txs/push?token=${myWallet.blockcypherToken}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tx: txHex })
    }).then(response => response.json());

    return result.tx.hash;
}

const start = new Date().getTime();
sendTransaction(myWallet.privateKey, myWallet.address).then(txid => console.log(txid)).catch(err => console.log(err)).finally(() => {
    // end time
    const end = new Date().getTime();
    // time elapsed in seconds
    const time = (end - start) / 1000;
    console.log('Time elapsed: ', time, 'seconds');
});
/*
Durata media di una richiesta	1,77s
Scarto quadratico medio	0,32s
Range (a 2 deviazioni standard)	1,13s - 2.41s
*/