<?php

  $dbstring = "(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)(HOST = 10.50.5.48)(PORT = 1630))(ADDRESS=(PROTOCOL=TCP)(HOST = 26.2.63.54)(PORT = 1630))(ADDRESS=(PROTOCOL=TCP)(HOST = 26.2.60.57)(PORT = 1521)))(SOURCE_ROUTE=yes)(CONNECT_DATA=(SERVICE_NAME = U12S)(SERVER=DEDICATED)))";

  $conn = OCILogon('comma6', 'comma6', $dbstring);

  $decuser = "giochi.newslottest@pec.aams.it";
  $decpass = "G_ntpai_104";

  $cypher = "DES-EDE3-ECB";
  $secret = "AUTENTICATEST";

  $select = "select t.username, t.passw".
            " from comma6.sca_pec_user t".
            " where t.data_agg = (select max(tt.data_agg) from comma6.sca_pec_user tt)";

  //Analizzo la query
  $quid = oci_parse(  $conn, $select );

  //Eseguo il comando SQL
  $esito =  oci_execute( $quid, OCI_DEFAULT );

  //Estraggo tutte le tuple e le pongo nell'array $rows
  $TotRighe=oci_fetch_all( $quid, $rows, 0, -1, OCI_RETURN_NULLS+OCI_FETCHSTATEMENT_BY_ROW );

  $ivlen = openssl_cipher_iv_length($cypher);
  $iv    = openssl_random_pseudo_bytes($ivlen);

  $encuser = openssl_encrypt($decuser, $cypher, $secret, OPENSSL_RAW_DATA);
  $encpass = openssl_encrypt($decpass, $cypher, $secret, OPENSSL_RAW_DATA);

  $myuser = $encuser;
  $mypass = $encpass;

  // $myuser = $rows[0]['USERNAME'];
  // $mypass = $rows[0]['PASSW'];

  $user = openssl_decrypt($myuser, $cypher, $secret, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
  $pass = openssl_decrypt($mypass, $cypher, $secret, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);


/*
  $td = mcrypt_module_open('tripledes', '', 'ecb', '');
  $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
  // Initialize encryption module for decryption
  mcrypt_generic_init($td, $key, $iv);

  // Decrypt encrypted string
  $user     = trim(mdecrypt_generic($td, $rows[0]['USERNAME']));
  $passw    = trim(mdecrypt_generic($td, $rows[0]['PASSW']));
  
  // Terminate decryption handle and close module
  mcrypt_generic_deinit($td);
  mcrypt_module_close($td);
*/

  oci_free_statement( $quid );     
  print "DecUser: ".$decuser."\nDecPass: ".$decpass."\n";
  print "EncUser: ".$encuser."\nEncPass: ".$encpass."\n";      
  print "RemUser: ".$rows[0]['USERNAME']."\nRemPass: ".$rows[0]['PASSW']."\n";         
  print "MyUser: ".$user."\nMyPass: ".$pass."\n";
