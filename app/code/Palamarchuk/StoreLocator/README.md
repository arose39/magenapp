bin/magento storelocator:load --file=app/code/Palamarchuk/StoreLocator/var/storage/1location.csv --quantity=30

bin/magento setup:db-declaration:generate-whitelist --module-name=Palamarchuk_StoreLocator

vendor/bin/phpstan analyze app/code --level=5

composer require geocoder-php/google-maps-provider php-http/guzzle7-adapter php-http/message
