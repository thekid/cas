<?php namespace de\thekid\cas\cmd;

use com\google\authenticator\{Secrets, SecretString};
use de\thekid\cas\Encryption;
use util\cmd\Console;

class NewToken {

  public static function main(array<string> $args): int {
    if (sizeof($args) < 2) {
      Console::writeLine('Usage: xp '.strtr(self::class, '\\', '.').' <username> <key> [<secret>]');
      return 1;
    }

    $encryption= new Encryption($args[1]);
    $random= isset($args[2]) ? new SecretString($args[2]) : Secrets::random();
 
    Console::writeLinef('QR Code  -> otpauth://totp/%s?secret=%s&label=CAS', $args[0], $random->encoded());
    Console::writeLinef('Database -> '.$encryption->encrypt($random->bytes()));
    return 0;
  }
}