# magento2xx-login-as-customer
minimalistic Login As Customer module for older Magento 2.2.x
where login as customer feature not available. 
<br>
<br>
## Setup
1. Create folders in app/code:
```
cd app/code && mkdir Shch && cd Shch && mkdir Lasc
```
<br>
2. Copy module to app/code/Shch/Lasc
3. Run CLI commands:
```
bin/magento cache:clean && bin/magento setup:upgrade && bin/magento setup:di:compile
```
4. After successful installation open admin panel, navigate to system > config > advanced > developer > login as customer and set store to login to. (store id 2 by default). Then navigate to customers grid, select customer and click 'login as customer' button at the top. It should redirect you to logged in customer/account/index page on store front.
