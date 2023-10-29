<?php

require_once __DIR__ . '/../setup.php';

try {
    $crypto_data = $model->getSortedCurrencies('crypto', $user_id);
    $fiat_data = $model->getSortedCurrencies('fiat', $user_id);
    $get_parameters['crypto'] = $model->assignParameter($crypto_data[0]['code']);
    $get_parameters['fiat'] = $model->assignParameter($fiat_data[0]['id']);
    
    echo $index->render([
        'crypto_data' => $crypto_data,
        'crypto_favourites' => $model->getFavourites('crypto', $user_id),
        'fiat_data' => $fiat_data,
        'fiat_favourites' => $model->getFavourites('fiat', $user_id),
        'get_parameters' => $get_parameters,
        'is_crypto_favourite' => $model->isCurrencyFavourite($crypto_data[0]['code'], $user_id),
        'is_fiat_favourite' => $model->isCurrencyFavourite($fiat_data[0]['id'], $user_id),
        'logged_in_as' => $_SESSION['logged_in_as'] ?? null
    ]);
} catch (Exception $e) {
    echo $error->render(['message' => $e->getMessage()]);
}


