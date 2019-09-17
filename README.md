# Gitlab Access Provider For Envato

Built for envato authors who wants to give access to their product's gitlab repository using purchase code.

## Requirements :wrench:
1. PHP on server
2. API to store & validate data. Please check DB structure for storing data.

## DB Strcuture :ledger:
DB requires two tables: 
1. Requests
2. Products

### Request Table
This table will store all request received through form and also for validating if user already used given purchase code for getting access to repo(Only one user can get access to repository using single purchase code).
  
**Columns:**
1. email - required
2. purchase_code - required + Unique
3. username - required
4. product_id - Foreign Key [ref: products]

### Products Table
This will store all your products and display it in form.

**Columns:**
1. name - required + Unique
2. enavato_id - required + Unique
3. gitlab_project_id - required + unique

### Required API endpoints
Your API should have below api endpoints to make api call for reading and adding data. `Update` or `Delete` operation is never performed.

1. Find request using purchase code:
 * URL: `<requests_api_endpoint_defined_in_config>?purchase_code=<code_to_varify>`
 * Response: List of objects
 * Required Methods: GET & POST

2. Fetch Products:
 * URL: `<products_api_endpoint_defined_in_config>`
 * Required Methods: GET


## How to Setup :thinking:
Just update `config.json` file and your are done. `config.json` file is easy to understand and removes headache of going through whole code.



## Who We Are :sunglasses: - [THEMESELECTION](https://themeselection.com)
We provides high quality, modern design, professional and easy-to-use Free Bootstrap Admin Dashboard Template,
HTML Themes, Premium Dashboard Templates and UI Kits to create your applications faster!