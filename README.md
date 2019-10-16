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
 
```js
// Get Response if exists
// Status Code: 200
[{
    "id": 13,
    "email": "jdsolanki0001@gmail.com",
    "purchase_code": "4a18ab18-5e3e-4580-a4b8-7f477d91652c",
    "username": "jdsolanki0001",
    "product_id":
    {
        "id": 5,
        "name": "Vusax - Vuejs + HTML Admin Dashboard Template",
        "envato_id": 23328599,
        "gitlab_project_id": 8641662,
        "created_at": "2019-09-12T06:39:18.103Z",
        "updated_at": "2019-09-12T06:39:18.103Z"
    },
    "created_at": "2019-09-16T14:22:02.981Z",
    "updated_at": "2019-10-12T09:17:42.089Z"
}]

// If doesn't exist
// Status Code: 200
[]
```

2. Fetch Products:
 * URL: `<products_api_endpoint_defined_in_config>`
 * Required Methods: GET
```js
// Status Code: 200
[{
    "id": 1,
    "name": "Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template",
    "envato_id": 23328599,
    "gitlab_project_id": 8641662,
    "created_at": "2019-09-12T06:39:18.103Z",
    "updated_at": "2019-09-12T06:39:18.103Z"
},
...]
```

3. Fetch Product By Envato ID:
 * URL: `<products_api_endpoint_defined_in_config>?envato_id=<envato_id_of_item>`
 * Required Methods: GET
```js
// Status Code: 200
// First item will be considered if multiple items are received
[{
    "id": 1,
    "name": "Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template",
    "envato_id": 23328599,
    "gitlab_project_id": 8641662,
    "created_at": "2019-09-12T06:39:18.103Z",
    "updated_at": "2019-09-12T06:39:18.103Z"
}]
```


## How to Setup :thinking:
Just update `config.json` file and your are done. `config.json` file is easy to understand and removes headache of going through whole code.



## Who We Are :sunglasses: - [THEMESELECTION](https://themeselection.com)
We provides high quality, modern design, professional and easy-to-use Free Bootstrap Admin Dashboard Template,
HTML Themes, Premium Dashboard Templates and UI Kits to create your applications faster!