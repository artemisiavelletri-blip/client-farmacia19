                                            
<?php

// Pagamento OneClik - Primo pagamento - Avvio pagamento

// Alias e chiave segreta 
$ALIAS = env('XPAY_ALIAS'); // Sostituire con il valore fornito da Nexi
$CHIAVESEGRETA = env('XPAY_SECRET'); // Sostituire con il valore fornito da Nexi

$requestUrl = "https://int-ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet";
//$merchantServerUrl = "http://" . $_SERVER['HTTP_HOST'] . "/cards/";
$merchantServerUrl = "https://unscented-reversion-trout.ngrok-free.dev/cards/";

$codTrans = "PS" . date('YmdHis');
$divisa = "EUR";
$importo = 1;

// Calcolo MAC
$mac = sha1('codTrans=' . $codTrans . 'divisa=' . $divisa . 'importo=' . $importo . $CHIAVESEGRETA);

$numContratto = "NC_TEST_" . date('YmdHis');
$tipoRichiesta = 'PP';

// Parametri obbligatori
$obbligatori = array(
    'alias' => $ALIAS,
    'importo' => $importo,
    'divisa' => $divisa,
    'codTrans' => $codTrans,
    'url' => $merchantServerUrl . "esito.php",
    'url_back' => $merchantServerUrl . "annullo.php",
    'mac' => $mac,
    'num_contratto' => "NC_TEST_20260525120359",
    'tipo_servizio' => 'paga_oc3d',
    'tipo_richiesta' => $tipoRichiesta,
    );

// Parametri facoltativi
$facoltativi = array(
);

$requestParams = array_merge($obbligatori, $facoltativi);

?>

<html>
    <head></head>
    <body>
        <form method='POST' action='<?php echo $requestUrl ?>'>
            <?php foreach ($requestParams as $name => $value) { ?>
                <input type='hidden' name='<?php echo $name; ?>' value='<?php echo htmlentities($value); ?>' />
            <?php } ?>
            
            <input type='submit' value='VAI ALLA PAGINA DI CASSA' />
        </form>
    </body>
</html>
                    
                