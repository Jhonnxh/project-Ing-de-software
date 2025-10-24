<?php
return [
  'secretKey'     => 'YourStrongSecretKey123!',  // change in production
  'issuer'        => 'unah-system',
  'audience'      => 'unah-system-users',
  'accessExpire'  => 900,        // 15 min
  'refreshExpire' => 60 * 60 * 24 * 30, // 30 days
];
