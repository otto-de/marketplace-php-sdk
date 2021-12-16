# Sample Otto Market PHP Cli Client

This implementation shows a sample client which could be used via any CLI.


The sample client requires [PHP](https://www.php.net/releases/7_4_0.php) v7.4 to run.
The recommended way to install the client is through [Composer](https://getcomposer.org/).

To see how to install the SDK go to the [installation section of the SDK](../README.md).

## Usage

start the client via php in your desired shell
```sh
php ./SampleCli.php -e (sandbox|live) -u YourUserName -p YourPassword product
```

## Help

start the client with --help parameter
```sh
php ./SampleCli.php --help
USAGE:
   OttoClient.php <OPTIONS> <COMMAND> ...

   An awesome CLI for accessing the Otto Market API                            
                                                                               

OPTIONS:
   -h, --help             Display this help screen and exit immediately.       

   -v, --version          print version                                        

   -u <user>, --user      username for authentication                          
   <user>                                                                      

   -p <password>,         password for authentication                          
   --password <password>                                                       

   -e <environment>,      environment: live or sandbox                         
   --environment                                                               
   <environment>                                                               

   --no-colors            Do not use any colors in output. Useful when piping  
                          output to other tools or files.                      

   --loglevel <level>     Minimum level of messages to display. Default is     
                          info. Valid levels are: debug, info, notice, success,
                          warning, error, critical, alert, emergency.          


COMMANDS:
   This tool accepts a command as first parameter as outlined below:           


   product <OPTIONS>

     list all products                                                         
                                                                               

     -s <sku>, --sku <sku> filter for specific sku                             

     -p <productName>,     filter for specific productName                     
     --productName                                                             
     <productName>                                                             

     -c <category>,        filter for specific category                        
     --category <category>                                                     

     -b <brand>, --brand   filter for specific brand                           
     <brand>                                                                   

     -f <format>, --format determine output format, e.g. json                  
     <format>                                                                  


   uploadProduct <postData>

     upload product data to otto market api                                    
                                                                               

     <postData>            to upload product data from json file               

   categories

     list categories                                                           
                                                                               

   category <categoryName>

     inspect category                                                          
                                                                               

     <categoryName>        Name of the category you want to inspect.           

   brands

     list brands                                                               
                                                                               

   marketplace-status <sku>

     get marketplace-status for a given sku                                    
                                                                               

     <sku>                 sku for which you want to get the status            

   active-status <sku>

     get active-status for a given sku                                         
                                                                               

     <sku>                 sku for which you want to get the status            

   upload-active-status <postData>

     upload market-place-status data to otto market api                        
                                                                               

     <postData>            to upload market-place-status data from json file   

   shipments <dateFrom>

     list shipments                                                            
                                                                               

     <dateFrom>            Shipments created from this date onwards for the    
                           given authorized partner will be returned. The date 
                           is considered as ISO 8601 and UTC.                  

   shipmentById <id>

     get a shipment by its ID value                                            
                                                                               

     <id>                  the ID value to search for
```


## Development

Want to contribute? Great!
