#!/bin/bash
SCRIPT_DIR=$(dirname "$0")
PROJECT_DIR=$SCRIPT_DIR/..
GENERATED_DIR=$PROJECT_DIR/generated

pushd "$PROJECT_DIR" || exit
echo Prepare to generate files to "$GENERATED_DIR"

if [ -d "$GENERATED_DIR" ]; then
  echo Removing dir "$GENERATED_DIR"
  rm -r "$GENERATED_DIR"
fi

TMP_DIR=$(mktemp -d)
echo Generated TMP_DIR="$TMP_DIR"
openapi-generator-cli generate -g php -i ./openapi-doc/products-interface.yml --additional-properties='variableNamingConvention=camelCase,invokerPackage=Otto\\Market\\Products,modelPackage=Model,srcBasePath=lib' -o $TMP_DIR/products
openapi-generator-cli generate -g php -i ./openapi-doc/shipment-interface.yml --additional-properties='variableNamingConvention=camelCase,invokerPackage=Otto\\Market\\Shipments,modelPackage=Model,srcBasePath=lib' -o $TMP_DIR/shipments

mkdir -p "$GENERATED_DIR"
cp -R "$TMP_DIR"/products/lib "$GENERATED_DIR"/products
rm -r "$GENERATED_DIR"/products/Api
cp -R "$TMP_DIR"/shipments/lib "$GENERATED_DIR"/shipments
rm -r "$GENERATED_DIR"/shipments/Api

echo Removing temporary directory "$TMP_DIR"
rm -r "$TMP_DIR"
