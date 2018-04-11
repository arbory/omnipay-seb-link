<?
// eLink datu parakstīšanas un pārbaudes piemērs.
// 2011.05.30, AS "SEB banka"

header("Content-Type: text/html; charset=utf-8");

// iegūtie mainīgie (parametri)
$params['IB_SND_ID']= 'RUKITIS';
$params['IB_SERVICE']= '0002';
$params['IB_VERSION']= '001';
$params['IB_AMOUNT']= '12.34';
$params['IB_CURR']= 'LVL';
$params['IB_NAME']= 'SIA Rūķītis';
$params['IB_PAYMENT_ID'] = 'RUKIS00000000265';
$params['IB_PAYMENT_DESC']= 'Maksa par rūķīšu pakalpojumiem.';
$params['IB_CRC']= 'YyTRcia1iI6YWkDy6/CEw22sZfezCzTEXREFRIHnhJODurYWyJiEsiMYwaY4T5OsIK4vDKQSR56kHAz0Q0MZz90lmrbR24WqznSCoTGpn/PIv66MlrZ4FZCxKEuJFIaqoJRykAqNiZK97SdCOuwURhbNO8x7TlytgRsBdbSJNUY=';
$params['IB_FEEDBACK']= 'https://www.rūķītis.lv/bankas.php?seb=RUKIS00000000265';
$params['IB_LANG']= 'LAT';

// Sagatavo virkni pārbaudei. Jāņem vērā, ka virknei ir jābūt UTF-8 kodējumā!
$virkne =  sprintf('%03s',strlen(utf8_decode($params['IB_SND_ID']))).$params['IB_SND_ID'];
$virkne .= sprintf('%03s',strlen(utf8_decode($params['IB_SERVICE']))).$params['IB_SERVICE'];
$virkne .= sprintf('%03s',strlen(utf8_decode($params['IB_VERSION']))).$params['IB_VERSION'];
$virkne .= sprintf('%03s',strlen(utf8_decode($params['IB_AMOUNT']))).$params['IB_AMOUNT'];
$virkne .= sprintf('%03s',strlen(utf8_decode($params['IB_CURR']))).$params['IB_CURR'];
$virkne .= sprintf('%03s',strlen(utf8_decode($params['IB_NAME']))).$params['IB_NAME'];
$virkne .= sprintf('%03s',strlen(utf8_decode($params['IB_PAYMENT_ID']))).$params['IB_PAYMENT_ID'];
$virkne .= sprintf('%03s',strlen(utf8_decode($params['IB_PAYMENT_DESC']))).$params['IB_PAYMENT_DESC'];

echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head><body><pre>";
echo "Virkne:\n", $virkne, "\n\n";
echo "Esošais paraksts:\n", $params['IB_CRC'], "\n";

// Pārbauda esošo parakstu
if ($pubcert = file_get_contents("pub.cer") and $pubkeyid = openssl_pkey_get_public($pubcert)) {
  $ok = openssl_verify($virkne, base64_decode($params['IB_CRC']), $pubkeyid);
  if ($ok == 1) {
    echo "Esošais paraksts ir derīgs.\n\n";
  } elseif ($ok == 0) {
    echo "Esošais paraksts nav derīgs!\n\n";
  } else {
    echo "Kļuda pārbaudot parakstu!\n\n";
  }
  openssl_free_key($pubkeyid);
} else {
  print "Kļūda atverot sertifikātu!\n\n";
}

// Paraksta datus
if ($privcert = file_get_contents("priv.key") and $privkeyid = openssl_pkey_get_private($privcert)) {
  if (openssl_sign($virkne,$signed,$privkeyid)) {
    $signed = base64_encode($signed);
    echo "Paraksts:\n", $signed;
  }
  openssl_free_key($privkeyid);
} else {
  print "Kļūda atverot privāto atslēgu!\n";
}

echo "</pre></body></html>";

?>
