<?php

declare(strict_types=1);

namespace Otto\Market\Test\Client;

final class ProductUploadJson
{
    public const PRODUCT_UPLOAD_RESPONSE = <<<EOD
        {
            "state": "pending",
            "message": "The process is currently in progress",
            "progress": 0,
            "total": 1,
            "pingAfter": "2020-05-13T10:40:01.815Z",
            "links": [
                {
                    "rel": "self",
                    "href": "/v2/products/update-tasks/09abe6ca-9c36-45bb-9cd3-a7de7f93284b"
                },
                {
                    "rel": "failed",
                    "href": "/v2/products/update-tasks/09abe6ca-9c36-45bb-9cd3-a7de7f93284b/failed"
                },
                {
                    "rel": "succeeded",
                    "href": "/v2/products/update-tasks/09abe6ca-9c36-45bb-9cd3-a7de7f93284b/succeeded"
                },
                {
                    "rel": "unchanged",
                    "href": "/v2/products/update-tasks/09abe6ca-9c36-45bb-9cd3-a7de7f93284b/unchanged"
                }
            ]
        }
    EOD;


    public const PRODUCT_PAYLOAD_ONE_PRODUCT = <<<EOD
[{"productReference":"Test-2701-01","sku":"Test-2701-01-01","ean":"6970451929875","productDescription":{"category":"Sommerkleid","brand":"someBrand","productLine":"Elegant","fscCertified":false,"disposal":false,"description":"Ein sehr schönes Kleid.","bulletPoints":["Mit Rüschen an Ärmel","In schwarz"],"attributes":[{"name":"Rückenlänge","values":["40"]},{"name":"Set-Info","values":["mit Gürtel"]},{"name":"Besondere Merkmale","values":["mit Rüschen"]},{"name":"Materialzusammensetzung","values":["100% Baumwolle"]},{"name":"Material","values":["Baumwolle"]},{"name":"Materialart","values":["Spitze"]},{"name":"Schnittform Länge","values":["knieumspielend"]},{"name":"Anlässe","values":["Abendmode","Casualmode","Frühlingsmode"]},{"name":"Optik","values":["gemustert","gestreift"]}]},"mediaAssets":[{"type":"IMAGE"}],"delivery":{"type":"PARCEL","deliveryTime":1},"pricing":{"standardPrice":{"amount":199,"currency":"EUR"},"vat":"FULL"},"logistics":{"packingUnitCount":0,"packingUnits":[]}}]
EOD;
}
