<?php

set_time_limit(0);

require __DIR__.'/vendor/autoload.php';

$ll  = (new LikeLight\Controller())->init();

try {
    if (!$ll->hasValidToken()) {
        echo "You have not yet logged in to get your access token.  Please use the login page.\n";
        $ll->getBoard()->resetAllPins()->setPinValue('r', 100);
        exit;
    }
    if (!$ll->getItems()->count()) {
        $ll->getInitialItems();
    } elseif (!empty($items->since)) {
        $ll->getNewItems();
    }
    if (($newLikes = $ll->getItems()->getNewLikeCounts())) {
        printf("There are %d new like%s\n", $newLikes, $newLikes == 1 ? '' : 's');
        $ll->getBoard()->resetAllPins()->setAllPinsValue(100);
        sleep(300); // 5 minutes
        $ll->getBoard()->fadeAllPinsOut(50000);
    }
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo "There was a problem with the response from Facebook\n";
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
    $ll->getBoard()->resetAllPins();
    for ($i = 0; $i < 10; $i++) {
        $ll->getBoard()->setPinValue('r', 100);
        sleep(1);
        $ll->getBoard()->fadePinOut('r', 7500);
    }
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo "There was a problem with the Facebook SDK\n";
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
    $ll->getBoard()->resetAllPins();
    for ($i = 0; $i < 10; $i++) {
        $ll->getBoard()->setPinValue('r', 100);
        sleep(1);
        $ll->getBoard()->fadePinOut('r', 7500);
    }
}

