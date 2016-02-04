<?php

require __DIR__.'/vendor/autoload.php';

$ll = new LikeLight\Controller();

echo '<a href="' . $ll->getCallbackUrl() . '">Log in with Facebook!</a>';
