<?php

require __DIR__.'/vendor/autoload.php';

$ll = new LikeLight\Controller();

try {
    $ll->handleAuthCallback();
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    $ll->getBoard()->resetAllPins();
    for ($i = 0; $i < 10; $i++) {
        $ll->getBoard()->setPinValue('r', 100);
        sleep(1);
        $ll->getBoard()->fadePinOut('r', 7500);
    }
    return;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    $ll->getBoard()->resetAllPins();
    for ($i = 0; $i < 10; $i++) {
        $ll->getBoard()->setPinValue('r', 100);
        sleep(1);
        $ll->getBoard()->fadePinOut('r', 7500);
    }
    return;
}

$ll->getBoard()->resetAllPins();
