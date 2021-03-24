<?php

declare(strict_types=1);

namespace Otto\Market\Test\Client;

final class ProductUploadJson
{
    public const PRODUCT_UPLOAD_RESPONSE = <<<EOD
        {
          "links": [
            {
              "href": "/v1/products/update-tasks/11111111-0000-4444-9999-bbbbbbbbbbbbb/failed",
              "rel": "succeeded"
            }
          ],
          "message": "string",
          "pingAfter": "2020-05-13T10:40:01.815+02:00",
          "progress": 0,
          "state": "pending",
          "total": 1
        }
    EOD;


    public const PRODUCT_PAYLOAD_ONE_PRODUCT = <<<EOD
[{"productName":"Test-2701-01","sku":"Test-2701-01-01","ean":"6970451929875","productDescription":{"category":"Sommerkleid","brand":"someBrand","productLine":"Elegant","fscCertified":false,"disposal":false,"description":"Ein sehr schönes Kleid.","bulletPoints":["Mit Rüschen an Ärmel","In schwarz"],"attributes":[{"name":"Rückenlänge","values":["40"]},{"name":"Set-Info","values":["mit Gürtel"]},{"name":"Besondere Merkmale","values":["mit Rüschen"]},{"name":"Materialzusammensetzung","values":["100% Baumwolle"]},{"name":"Material","values":["Baumwolle"]},{"name":"Materialart","values":["Spitze"]},{"name":"Schnittform Länge","values":["knieumspielend"]},{"name":"Anlässe","values":["Abendmode","Casualmode","Frühlingsmode"]},{"name":"Optik","values":["gemustert","gestreift"]}]},"mediaAssets":[{"type":"IMAGE"}],"delivery":{"type":"PARCEL","deliveryTime":1},"pricing":{"standardPrice":{"amount":199,"currency":"EUR"},"vat":"FULL"},"logistics":{"packingUnitCount":0,"packingUnits":[]}}]
EOD;
}
