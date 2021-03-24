<?php

namespace Otto\Market\Test\Client;

final class JsonResponses
{
    const GET_SHIPMENTS = <<<EOD
    {
        "resources": [
            {
                "shipmentId": "101074901541",
                "creationDate": "2020-09-02T07:06:45.748Z",
                "trackingKey": {
                    "carrier": "HERMES",
                    "trackingNumber": "H8976598569852345698"
                }
            },
            {
                "shipmentId": "101074897022",
                "creationDate": "2021-03-02T07:05:57.150Z",
                "trackingKey": {
                    "carrier": "HERMES",
                    "trackingNumber": "H5569853654983256987"
                }
            }
        ]
    }
    EOD;

    const GET_SHIPMENTS_PAGE1 = <<<EOD
    {
        "links": [
            {
                "href": "/v1/shipments?next=101074897022&datefrom=2020-09-01&limit=1",
                "rel": "next"
            }
        ],
        "resources": [
            {
                "shipmentId": "101074901541",
                "creationDate": "2020-09-02T07:06:45.748Z",
                "trackingKey": {
                    "carrier": "HERMES",
                    "trackingNumber": "H8976598569852345698"
                }
            }
        ]
    }
    EOD;

    const GET_SHIPMENTS_PAGE2 = <<<EOD
    {
        "resources": [
            {
                "shipmentId": "101074897022",
                "creationDate": "2021-03-02T07:05:57.150Z",
                "trackingKey": {
                    "carrier": "HERMES",
                    "trackingNumber": "H5569853654983256987"
                }
            }
        ]
    }
    EOD;

    const GET_SHIPMENTS_BAD_REQUEST = <<<EOD
    {
        "errors": [
            {
                "title": "INVALID_REQUEST_PARAMETER",
                "path": "/shipments?datefrom=2020-09-0X"
            }
        ]
    }
    EOD;

    const GET_SHIPMENT_BY_ID = <<<EOD
    {
        "shipmentId": "101074897022",
        "creationDate": "2020-09-02T07:05:57.150Z",
        "trackingKey": {
            "carrier": "HERMES",
            "trackingNumber": "H5569853654983256987"
        },
        "shipDate": "2020-09-02T07:02:29.474Z",
        "shipFromAddress": {
            "city": "Hamburg",
            "zipCode": "22222",
            "countryCode": "DEU"
        },
        "positionItems": [
            {
                "positionItemId": "80ab51d6-5e65-4f66-9fee-8b227861fc83",
                "salesOrderId": "2e57d63f-11f4-4baf-9b56-cee829389a0e",
                "returnTrackingKey": {
                    "carrier": "HERMES",
                    "trackingNumber": "H5569853654983256987"
                }
            },
            {
                "positionItemId": "a49350ee-2783-407c-a841-5a8edc290331",
                "salesOrderId": "2e57d63f-11f4-4baf-9b56-cee829389a0e",
                "returnTrackingKey": {
                    "carrier": "HERMES",
                    "trackingNumber": "H5569853654983256987"
                }
            }
        ]
    }
    EOD;

    const GET_SHIPMENT_BY_CARRIER = <<<EOD
    {
        "shipmentId": "101074897022",
        "creationDate": "2020-09-02T07:05:57.150Z",
        "trackingKey": {
            "carrier": "HERMES",
            "trackingNumber": "H5569853654983256987"
        },
        "shipDate": "2020-09-02T07:02:29.474Z",
        "shipFromAddress": {
            "city": "Hamburg",
            "zipCode": "22222",
            "countryCode": "DEU"
        },
        "positionItems": [
            {
                "positionItemId": "80ab51d6-5e65-4f66-9fee-8b227861fc83",
                "salesOrderId": "2e57d63f-11f4-4baf-9b56-cee829389a0e",
                "returnTrackingKey": {
                    "carrier": "HERMES",
                    "trackingNumber": "H5569853654983256987"
                }
            }
        ]
    }
    EOD;

    const CREATE_SHIPMENT = <<<EOD
    {
        "shipmentId": "101074897022"
    }
    EOD;
}
