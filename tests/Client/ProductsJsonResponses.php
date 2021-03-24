<?php

namespace Otto\Market\Test\Client;

final class ProductsJsonResponses
{
    const GET_MARKETPLACESTATUS_ERROR = <<<EOD
    {
    "sku": "123363682",
    "status": "REJECTED",
    "errors": [
        {
            "code": "600004 - VARIATIONTHEME_INVALID",
            "title": "Die Variante konnte in diesem Produkt nicht angelegt werden, da sich die variantenbildenden Merkmale von anderen Varianten in diesem Produkt nicht unterscheiden. Bitte prüfen Sie die Daten dieser Variante."
        }
    ],
    "links": [
        {
            "rel": "variation",
            "href": "/v1/products/123363682"
        }
    ],
    "lastModified": "2020-12-23T20:36:27.297Z"
    }
    EOD;

    const GET_ACTIVE_STATUS = <<<EOD
    {
        "sku": "123363682",
        "active": true,
        "lastModified": "2020-12-08T10:45:49.525Z"
    }
    EOD;

    const POST_ACTIVE_STATUS = <<<EOD
    {
        "state": "pending",
        "message": "The process is currently in progress",
        "progress": 0,
        "total": 2,
        "pingAfter": "2021-03-15T10:26:15.148+0000",
        "links": [
            {
                "rel": "self",
                "href": "/v1/products/update-tasks/e2484daf-440f-4b1a-a6d1-f6a370a3d333"
            },
            {
                "rel": "failed",
                "href": "/v1/products/update-tasks/e2484daf-440f-4b1a-a6d1-f6a370a3d333/failed"
            },
            {
                "rel": "succeeded",
                "href": "/v1/products/update-tasks/e2484daf-440f-4b1a-a6d1-f6a370a3d333/succeeded"
            }
        ]
    }
    EOD;

    const GET_CATEGORIES = <<<EOD
        {
            "categoryGroups": [
              {
                "additionalRequirements": [
                  {
                    "condition": {
                      "jsonPath": "string",
                      "name": "string",
                      "value": "string"
                    },
                    "description": "Die Produktkategorie fällt unter die Preisangabenverordnung, weshalb ein Grundpreis angegeben werden muss.",
                    "featureRelevance": [
                      "LEGAL"
                    ],
                    "jsonPath": "$.productVariations[*].pricing.normPriceInfo",
                    "name": "normPriceInfo",
                    "reference": "https://public-docs.live.api.otto.market/Products/v1/products-interface.html"
                  }
                ],
                "attributes": [
                  {
                    "allowedValues": [ "natur bunt grau ..." ],
                    "attributeGroup": "Maße & Gewicht",
                    "description": "some textual description",
                    "exampleValues": [ "Edelstahl Aluminium Stahl" ],
                    "featureRelevance": [ "FILTER" ],
                    "multiValue": true,
                    "name": "Durchmesser",
                    "recommendedValues": [ "modern klassisch Landhaus Vintage" ],
                    "reference": "http://wikipedia.de/something",
                    "relatedMediaAssets": [
                      "string"
                    ],
                    "relevance": "HIGH",
                    "type": "FLOAT",
                    "unit": "cm",
                    "unitDisplayName": "Zentimeter"
                  }
                ],
                "categories": [ "Farbe" ],
                "categoryGroup": "string",
                "lastModified": "2019-06-18T06:12:36.123+02:00",
                "title": "string",
                "variationThemes": [
                  "string"
                ]
              }
            ],
            "links": [
              {
                "href": "/v1/products?page=0&limit=100",
                "rel": "self"
              }
            ]
        }
        EOD;

    const GET_PRODUCT_PROCESS_PROGRESS_CHANGED = <<<EOD
    {
      "links": [
        {
          "href": "/v1/products",
          "rel": "self"
        }
      ],
      "message": "string",
      "pingAfter": "2021-03-15T10:40:00.000+01:00",
      "progress": 0,
      "state": "pending",
      "total": 0
    }
    EOD;
    
    const GET_PRODUCT_PROCESS_PROGRESS_UNCHANGED = <<<EOD
    {
      "links": [
        {
          "href": "/v1/products/update-tasks/11111111-0000-4444-9999-bbbbbbbbbbbbb/failed",
          "rel": "self"
        }
      ],
      "message": "string",
      "pingAfter": "9999-03-15T10:40:00.000+01:00",
      "progress": 0,
      "state": "pending",
      "total": 0
    }
    EOD;

    const GET_PRODUCTS = <<<EOD
    {
      "productVariations": [
        {
          "productName": "UBN-11779",
          "sku": "3858389911564",
          "ean": "3858389911564",
          "gtin": "00012345600012",
          "isbn": "978-3-16-148410-0",
          "upc": "042100005264",
          "pzn": "PZN-4908802",
          "mpn": "H2G2-42",
          "moin": "93992000200",
          "offeringStartDate": "2019-10-19T10:00:15.000+02:00",
          "releaseDate": "2019-10-19T10:00:15.000+02:00",
          "maxOrderQuantity": 5,
          "productDescription": {
            "category": "Outdoorjacke",
            "brand": "Adidas",
            "productLine": "501",
            "manufacturer": "3M",
            "productionDate": "2019-10-19T10:00:15.000+02:00",
            "multiPack": true,
            "bundle": false,
            "fscCertified": true,
            "disposal": false,
            "productUrl": "http://myproduct.somewhere.com/productname/",
            "description": "fedssdf",
            "bulletPoints": [
              "My top key information..."
            ],
            "attributes": [
              {
                "name": "Bundweite",
                "values": [
                  "34"
                ],
                "additional": true
              }
            ]
          },
          "mediaAssets": [
            {
              "type": "IMAGE",
              "location": "http://apartners.url/image-location"
            }
          ],
          "delivery": {
            "type": "PARCEL",
            "deliveryTime": 1
          },
          "pricing": {
            "standardPrice": {
              "amount": 19.95,
              "currency": "EUR"
            },
            "vat": "FULL",
            "msrp": {
              "amount": 19.95,
              "currency": "EUR"
            },
            "sale": {
              "salePrice": {
                "amount": 19.95,
                "currency": "EUR"
              },
              "startDate": "2019-10-19T10:00:15.000+02:00",
              "endDate": "2019-10-26T10:00:15.000+02:00"
            },
            "normPriceInfo": {
              "normAmount": 100,
              "normUnit": "g",
              "salesAmount": 500,
              "salesUnit": "g"
            }
          },
          "logistics": {
            "packingUnitCount": 3,
            "packingUnits": [
              {
                "weight": 365,
                "width": 600,
                "height": 200,
                "length": 300
              }
            ]
          }
        },
        {
          "productName": "UBN-11779",
          "sku": "3858389911564",
          "ean": "3858389911564",
          "gtin": "00012345600012",
          "isbn": "978-3-16-148410-0",
          "upc": "042100005264",
          "pzn": "PZN-4908802",
          "mpn": "H2G2-42",
          "moin": "93992000200",
          "offeringStartDate": "2019-10-19T10:00:15.000+02:00",
          "releaseDate": "2019-10-19T10:00:15.000+02:00",
          "maxOrderQuantity": 5,
          "productDescription": {
            "category": "Outdoorjacke",
            "brand": "Adidas",
            "productLine": "501",
            "manufacturer": "3M",
            "productionDate": "2019-10-19T10:00:15.000+02:00",
            "multiPack": true,
            "bundle": false,
            "fscCertified": true,
            "disposal": false,
            "productUrl": "http://myproduct.somewhere.com/productname/",
            "description": "fedssdf",
            "bulletPoints": [
              "My top key information..."
            ],
            "attributes": [
              {
                "name": "Bundweite",
                "values": [
                  "34"
                ],
                "additional": true
              }
            ]
          },
          "mediaAssets": [
            {
              "type": "IMAGE",
              "location": "http://apartners.url/image-location"
            }
          ],
          "delivery": {
            "type": "PARCEL",
            "deliveryTime": 1
          },
          "pricing": {
            "standardPrice": {
              "amount": 19.95,
              "currency": "EUR"
            },
            "vat": "FULL",
            "msrp": {
              "amount": 19.95,
              "currency": "EUR"
            },
            "sale": {
              "salePrice": {
                "amount": 19.95,
                "currency": "EUR"
              },
              "startDate": "2019-10-19T10:00:15.000+02:00",
              "endDate": "2019-10-26T10:00:15.000+02:00"
            },
            "normPriceInfo": {
              "normAmount": 100,
              "normUnit": "g",
              "salesAmount": 500,
              "salesUnit": "g"
            }
          },
          "logistics": {
            "packingUnitCount": 3,
            "packingUnits": [
              {
                "weight": 365,
                "width": 600,
                "height": 200,
                "length": 300
              }
            ]
          }
        }
      ],
      "links": [
        {
          "rel": "self",
          "href": "/v1/products?page=0&limit=100"
        }
      ]
    }
    EOD;

}
