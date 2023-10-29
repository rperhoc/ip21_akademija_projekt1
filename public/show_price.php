<?php

require_once __DIR__ . '/../setup.php';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET': 
            try {
                $selected_crypto = $model->assignParameter($_GET['crypto']);
                $selected_fiat = $model->assignParameter($_GET['fiat']);
            } catch (Exception $e) {
                echo $error->render(['message' => $e->getMessage()]);
            }
        try {
            echo $show_price->render([
                'crypto_data' => $model->getSortedCurrencies('crypto', $user_id),
                'crypto_favourites' => $model->getFavourites('crypto', $user_id),
                'fiat_data' => $model->getSortedCurrencies('fiat', $user_id),
                'fiat_favourites' => $model->getFavourites('fiat', $user_id),
                'exchange_rate' => round($model->getExchangeRate($selected_crypto, $selected_fiat), 2),
                'crypto' => $selected_crypto,
                'fiat' => $selected_fiat,
                'date' => date('d.m.Y'),
                'time' => date('H:i:s'),
                'get_parameters' => $model->assignParameters($_GET),
                'is_crypto_favourite' => $model->isCurrencyFavourite($selected_crypto, $user_id),
                'is_fiat_favourite' => $model->isCurrencyFavourite($selected_fiat, $user_id),
                'logged_in_as' => $_SESSION['logged_in_as'] ?? null
            ]);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            echo $error->render(['message' => $error_message]);
        }
        break;
    case 'POST':
        try {
            $post_parameters = $model->assignParameters($_POST);
            header('Location: ' . $model->getRedirectUrl($post_parameters));
        } catch (Exception $e) {
            echo $error_page->render(['message' => $e->getMessage()]);
        }
        if ( isset($post_parameters['favourite_add_crypto']) ) {
            $crypto_currency = $model->getCurrencyFromName($post_parameters['crypto'], 'crypto');
            $model->insertIntoFavourites($crypto_currency, 'crypto');
        } elseif ( isset($post_parameters['favourite_remove_crypto']) ) {
            $model->removeFromFavourites($post_parameters['favourite_remove_crypto']);
        } elseif ( isset($post_parameters['favourite_add_fiat']) ) {
            $fiat_currency = $model->getCurrencyFromName($post_parameters['fiat'], 'fiat');
            $model->insertIntoFavourites($fiat_currency, 'fiat');
        } elseif ( isset($post_parameters['favourite_remove_fiat']) ) {
            $model->removeFromFavourites($post_parameters['favourite_remove_fiat']);
        }    
        break;
    default:
        echo $error->render(['message' => "Oops! Something went wrong"]); 
        break; 
}
