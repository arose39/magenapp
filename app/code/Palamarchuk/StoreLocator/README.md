bin/magento storelocator:load --file=app/code/Palamarchuk/StoreLocator/var/storage/1location.csv --quantity=30

bin/magento setup:db-declaration:generate-whitelist --module-name=Palamarchuk_StoreLocator

vendor/bin/phpstan analyze app/code --level=5

composer require geocoder-php/google-maps-provider php-http/guzzle7-adapter php-http/message


query {
storeLocations
{
storeLocations {
id
name
store_img
latitude
}
}
}

mutation createStoreLocation {
createStoreLocation(
input: {
name: "String"
description: "String"
address: "Херсонська, 71"
city: "Берислав"
country: "Україна"
zip: "74300"
phone: "+380633123456"
}
) {
storeLocation {
id
name
store_url_key
description
phone
latitude
longitude
}
}
}

mutation createStoreLocation {
updateStoreLocation(
input: {
id: 25
name: "String11111111111111111111"
description: "Strin  1 1 1 1 1  g"
address: "Херсонська, 71"
city: "Берислав"
country: "Україна"
zip: "74300"
phone: "+380633123456"
}
) {
storeLocation {
id
name
store_url_key
description
phone
latitude
longitude
}
}
