# JDataTable

A library used to initialize a basic data table. Includes the main components of a data table such as: sorting, per page, pagination, table. Supports additional configuration of search and filtering features.

## Table of contents

[Tech Stack](#tech-stack)

[Initialization](#initialization)
- [1. Create a table element in HTML](#1-create-a-table-element-in-html)
- [2. Initialize a TableData class in javascript](#2-initialize-a-tabledata-class-in-javascript)
- [3. Render table](#3-render-table)

[Configuration](#configuration)
- [1. Structure of config variable](#1-structure-of-config-variable)
- [2. Default configuration](#2-default-configuration)
- [3. Detailed configuration](#3-detailed-configuration)

[Filter](#filter)

[Search](#search)

[Edit cell content directly](#edit-cell-content-directly)
- [1. Functions to use](#1-functions-to-use)
- [2. Example](#2-example)

[Sample data structure](#sample-data-structure)

[Demo](#demo-jdatatable)
## Tech Stack

**Language:** HTML, JavaScript.

**Library:** jQuery, Remix Icon.

**Framework:** Bootstrap.

## Initialization

#### 1. Create a table element in HTML

Example:

```
  <table class="table table-striped table-bordered table-hover" id="data_table"></table>
```

#### 2. Initialize a `TableData` class in javascript

Example:

```
  var tableData = new TableData("#data_table", config);
```

#### 3. Render table

Example:

```
  tableData.render();
```

## Configuration

#### 1. Structure of _config_ variable

```js
var config = {
  // Declare configuration for table headers
  // 'render' function is used to customize the header
  heads: [],
  // Use data if config.ajax not exist
  data: [],
  // Used to customize rows according to column names
  rowRender: (colName, colValue, row) => {},
  // Ajax function to automatically get initial data and re-render times
  // If this parameter is not present, the library will use the default parameter "data" passed directly
  ajax: (params) => {
    return new Promise((resolve, reject) => {});
  },
  // Information used to call the API for the row and cell update function
  apiUpdate: {},
  // Includes options for number of rows to display, pagination and row count details
  footer: {},
  // Customize sorting toggle or select which columns are allowed to be sorted
  sort: {},
  // Columns are sorted by default when initialized
  order: {},
  // Filter based on columns declared in config.heads
  filter: {},
  // Current page
  page: 1,
  // Number of lines on the page
  per_page: 15,
};
```

#### 2. Default configuration

```js
config = {
  order: null,
  filter: null,
  heads: [],
  page: 1,
  per_page: 15,
  data: {},
  rowRender: (colName, colValue, row) => {
    return colValue;
  },
  footer: {
    pagination: false,
    detail: false,
  },
  sort: {
    enable: false,
    listColumn: [],
  },
};
```

_Note: The user-declared configuration will be merged with the default configuration_

#### 3. Detailed configuration

| Properties | Type  | Description                                                                                                                                                                                                                         |
| ---------- | ----- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| data       | Array    | The array contains data to render when the user wants to pass data directly to the table. By default, the library will receive this `data` variable as rendering data if the user does not declare the config.ajax attribute. |
| order      | Object   | Columns are sorted by default when initialized. The syntax includes 'order*by*' + column name to sort. Example: `{order_by_id: 'desc'}`                                                                                       |
| filter     | Object   | Filter based on columns declared in config.heads. Example: `{email: '@gmail.com', name: 'Jonh'}`                                                                                                                              |
| page       | Number   | Current page. Default is 1.                                                                                                                                                                                                   |
| per_page   | Number   | Number of lines on the page. Default is 15.                                                                                                                                                                                   |
| heads      | Array | An array of objects representing the columns of the table. Each object has two fields: `name` (the column name) and `value` (the display title of the column). It can have a `render` field to customize the display of the header. |
|            |       |                                                                                                                                                                                                                                     |

Example:

```js
[
  {
    name: "id",
    value: "ID",
  },
  {
    name: "email",
    value: "Email",
  },
  {
    name: "code",
    value: "Code",
    render: (heads) => {
      var html = `
                    <h1>${heads.value}</h1>    
              `;
      return html;
    },
  },
];
```

| Properties | Type     | Description                                                                                                                                                                                                                   |
| ---------- | -------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| rowRender  | Function | A function used to customize the display of each cell in a row. Takes three parameters: `colName` (the column name), `colValue` (the value of the cell), and `row` (the data of the row). Returns an HTML string to display.  |

Example:

```js
(colName, colValue, row) => {
  switch (colName) {
    case "id":
      return `
          <div class="text-center">
            ${row.id}
          </div>
        `;
    case "email":
      return tableData.cellEdit(colName, colValue);
    default:
      return colValue;
  }
};
```

| Properties | Type     | Description                                                                                                                                                                                |
| ---------- | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| ajax       | Function | A function used to call an API and fetch initial data for the table. Takes one argument `params` (query parameters). Returns a promise with data resolved or rejects if there is an error. |
|            |          |                                                                                                                                                                                            |

Example:

```js
(params) => {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: "https://your.domain.net/api/v1/.../get",
      type: "GET",
      data: params, // Autofill based on 'order' and 'filter' attributes
      headers: {
        Authorization: "Bearer ...", // Include the token in the request header
      },
      success: function (response) {
        resolve(response);
      },
      error: function (error) {
        console.log(error);
        reject(error);
      },
    });
  });
};
```

| Properties | Type   | Description                                                                                                                                                                                                |
| ---------- | ------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| apiUpdate  | Object | An object containing information to call the API to update row and cell data. Includes fields: `url` (API endpoint), `type` (API method), and `headers` (request headers, including authentication token). |
|            |        |                                                                                                                                                                                                            |

Example:

```js
{
  url: "https://your.domain.net/api/v1/.../update",
  type: "POST",
  headers: {
    Authorization: "Bearer ...", // Include the token in the request header
  },
}
```

| Properties | Type   | Description                                                                                                                                                                                                             |
| ---------- | ------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| footer     | Object | An object containing configurations for the table footer. It has two fields: `pagination` (true/false to enable/disable pagination) and `detail` (true/false to show/hide detailed information about the record count). |
|            |        |                                                                                                                                                                                                                         |

Example:

```js
{
  pagination: true,
  detail: true
}
```

| Properties | Type   | Description                                                                                                                                                                                                   |
| ---------- | ------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| sort       | Object | An object containing configurations for the table sorting feature. It has two fields: `enable` (true/false to enable/disable sorting feature) and `listColumn` (an array of column names that can be sorted). |
|            |        |                                                                                                                                                                                                               |

Example:

```js
{
  enable: true,
  listColumn: ["id", "email", "code", "created_at"]
}
```

## Filter

### To use the filter function, users need to create a filter form and a function to retrieve values ​​from the form.

Example:

HTML

```html
<!-- Filter -->
<div class="card mb-3">
  <div class="card-body">
    <h5 class="card-title">Filter</h5>
    <form id="filterForm">
      <div class="form-group">
        <label for="email">Email:</label>
        <input type="text" class="form-control" id="email" name="email" />
      </div>
      <div class="form-group">
        <label for="code">Code:</label>
        <input type="text" class="form-control" id="code" name="code" />
      </div>
      <button
        type="button"
        class="btn btn-primary mt-2"
        onclick="getFormValues()"
      >
        Filter
      </button>
    </form>
  </div>
</div>
<!-- Filter -->
```

JS

```js
// Get values from form html and append to config -> re-render with new data
const getFormValues = () => {
  const formData = {};
  const form = document.getElementById("filterForm");

  for (let i = 0; i < form.elements.length; i++) {
    const element = form.elements[i];

    if (!element.name || element.tagName === "BUTTON") {
      continue;
    }

    formData[element.name] = element.value;
  }

  config.filter = formData;
  tableData.updateConfig(config);
};
```

## Search

### Users need to design an input to enter the keyword to search, an event to listen for changes and filter results.

Example:

HTML

```html
<!-- Search -->
<div class="form-group">
  <label for="search_input">Search:</label>
  <input id="search_input" type="text" class="form-control" />
</div>
<!-- Search -->
```

JS

```js
// Search for keywords by line (tr tag)
$("#search_input").on("keyup", function () {
  var value = $(this).val().toLowerCase();
  $("#bookTabe tbody tr").filter(function () {
    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
  });
});
```

## Edit cell content directly

### 1. Functions to use:

| Function             | Description                                                                                                                                      | Parameters                                                                                             |
| -------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------ |
| tableData.cellEdit   | Used to initialize a cell that can be edited directly. Used in `config.rowRender`                                                                | tableData.cellEdit(colName, colValue). `colName` (the column name), `colValue` (the value of the cell) |
| tableData.actionEdit | Used to initialize an edit button action to perform editing operations. Used in `config.rowRender` and usually declared in the `__actions` column. | tableData.actionEdit(row). `row` (the data of the row)             |

### 2. Example

* `rowRender` in `config` variable
```js
rowRender: (colName, colValue, row) => {
  switch (colName) {
    case "email":
      return tableData.cellEdit(colName, colValue);
    case "code":
      return tableData.cellEdit(colName, colValue);
    case "__actions":
      return tableData.actionEdit(row);
    default:
      return colValue;
  }
};
```
* Result

![OpenAI Logo](https://i.ibb.co/S36CYxj/Screenshot-2024-02-21-150628.png)

![OpenAI Logo](https://i.ibb.co/9TTrHSj/Screenshot-2024-02-21-150703.png)

## Sample data structure

```js
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "discount_type_id": 2,
            "user_id": 1,
            "code": "rAZihpg",
            "email": "test112@yopmail.com",
            "value": "30.00",
            "active_date": "2024-01-10T17:00:00.000000Z",
            "expiry": "2024-01-18T17:00:00.000000Z",
            "date_used": "2024-01-18T01:14:56.000000Z",
            "status": 0,
            "created_at": "2024-01-18T01:13:50.000000Z",
            "updated_at": "2024-02-21T01:20:49.000000Z",
            "discount_type": "percent",
            "discount_unit": "%"
        },
        {
            "id": 2,
            "discount_type_id": 2,
            "user_id": 0,
            "code": "miWM4c",
            "email": "test.1@yopmail.com",
            "value": "50.00",
            "active_date": "2024-01-17T17:00:00.000000Z",
            "expiry": "2024-01-18T17:00:00.000000Z",
            "date_used": null,
            "status": 0,
            "created_at": "2024-01-18T02:47:01.000000Z",
            "updated_at": "2024-02-20T08:01:41.000000Z",
            "discount_type": "percent",
            "discount_unit": "%"
        },
        {
            "id": 3,
            "discount_type_id": 2,
            "user_id": 0,
            "code": "QD384l",
            "email": "null",
            "value": "50.00",
            "active_date": "2024-01-17T17:00:00.000000Z",
            "expiry": "2024-01-19T17:00:00.000000Z",
            "date_used": null,
            "status": 0,
            "created_at": "2024-01-18T02:54:10.000000Z",
            "updated_at": "2024-02-20T07:20:43.000000Z",
            "discount_type": "percent",
            "discount_unit": "%"
        },
        {
            "id": 4,
            "discount_type_id": 1,
            "user_id": 1,
            "code": "3xaatd",
            "email": "test112@yopmail.com",
            "value": "30.00",
            "active_date": "2024-01-16T17:00:00.000000Z",
            "expiry": "2024-01-26T17:00:00.000000Z",
            "date_used": "2024-01-18T02:55:42.000000Z",
            "status": 0,
            "created_at": "2024-01-18T02:54:55.000000Z",
            "updated_at": "2024-01-18T02:55:42.000000Z",
            "discount_type": "price",
            "discount_unit": "$"
        },
        {
            "id": 5,
            "discount_type_id": 1,
            "user_id": 0,
            "code": "Ij9t99",
            "email": null,
            "value": "12.00",
            "active_date": "2024-01-01T17:00:00.000000Z",
            "expiry": "2024-01-04T17:00:00.000000Z",
            "date_used": null,
            "status": 0,
            "created_at": "2024-01-18T03:03:06.000000Z",
            "updated_at": "2024-01-18T03:03:15.000000Z",
            "discount_type": "price",
            "discount_unit": "$"
        },
        {
            "id": 6,
            "discount_type_id": 2,
            "user_id": 13,
            "code": "JrK0uL",
            "email": "test114@yopmail.com",
            "value": "20.00",
            "active_date": "2024-01-18T17:00:00.000000Z",
            "expiry": "2024-01-26T17:00:00.000000Z",
            "date_used": "2024-01-19T01:17:19.000000Z",
            "status": 0,
            "created_at": "2024-01-19T01:16:14.000000Z",
            "updated_at": "2024-01-19T01:17:19.000000Z",
            "discount_type": "percent",
            "discount_unit": "%"
        },
        {
            "id": 7,
            "discount_type_id": 1,
            "user_id": 13,
            "code": "S7kKsZ",
            "email": "test114@yopmail.com",
            "value": "500.00",
            "active_date": "2024-01-11T17:00:00.000000Z",
            "expiry": "2024-01-25T17:00:00.000000Z",
            "date_used": "2024-01-19T02:43:01.000000Z",
            "status": 0,
            "created_at": "2024-01-19T02:42:04.000000Z",
            "updated_at": "2024-01-19T02:43:01.000000Z",
            "discount_type": "price",
            "discount_unit": "$"
        },
        {
            "id": 8,
            "discount_type_id": 2,
            "user_id": 0,
            "code": "Nudf64",
            "email": null,
            "value": "50.00",
            "active_date": "2024-01-17T17:00:00.000000Z",
            "expiry": "2024-01-26T17:00:00.000000Z",
            "date_used": null,
            "status": 0,
            "created_at": "2024-01-19T07:47:38.000000Z",
            "updated_at": "2024-01-19T07:48:54.000000Z",
            "discount_type": "percent",
            "discount_unit": "%"
        },
        {
            "id": 9,
            "discount_type_id": 2,
            "user_id": 1,
            "code": "sFxeGg",
            "email": "test112@yopmail.com",
            "value": "55.00",
            "active_date": "2024-01-17T17:00:00.000000Z",
            "expiry": "2024-01-26T17:00:00.000000Z",
            "date_used": "2024-01-19T07:53:23.000000Z",
            "status": 0,
            "created_at": "2024-01-19T07:51:10.000000Z",
            "updated_at": "2024-01-19T07:53:23.000000Z",
            "discount_type": "percent",
            "discount_unit": "%"
        },
        {
            "id": 10,
            "discount_type_id": 1,
            "user_id": 9,
            "code": "jHdsek",
            "email": "nguyenphuc071199@gmail.com",
            "value": "100.00",
            "active_date": "2024-01-01T17:00:00.000000Z",
            "expiry": "2024-01-19T17:00:00.000000Z",
            "date_used": "2024-01-19T08:46:34.000000Z",
            "status": 0,
            "created_at": "2024-01-19T08:46:20.000000Z",
            "updated_at": "2024-01-19T08:46:34.000000Z",
            "discount_type": "price",
            "discount_unit": "$"
        },
        {
            "id": 11,
            "discount_type_id": 1,
            "user_id": 1,
            "code": "Jf7O42",
            "email": "test112@yopmail.com",
            "value": "100.00",
            "active_date": "2024-01-18T17:00:00.000000Z",
            "expiry": "2024-01-30T17:00:00.000000Z",
            "date_used": "2024-01-20T01:05:22.000000Z",
            "status": 0,
            "created_at": "2024-01-20T01:04:59.000000Z",
            "updated_at": "2024-02-20T07:25:27.000000Z",
            "discount_type": "price",
            "discount_unit": "$"
        },
        {
            "id": 12,
            "discount_type_id": 1,
            "user_id": 9,
            "code": "C8RU64",
            "email": "nguyenphuc071199@gmail.com",
            "value": "100.00",
            "active_date": "2024-01-01T17:00:00.000000Z",
            "expiry": "2024-01-27T17:00:00.000000Z",
            "date_used": "2024-01-20T03:11:10.000000Z",
            "status": 0,
            "created_at": "2024-01-20T03:10:41.000000Z",
            "updated_at": "2024-01-20T03:11:10.000000Z",
            "discount_type": "price",
            "discount_unit": "$"
        },
        {
            "id": 13,
            "discount_type_id": 2,
            "user_id": 0,
            "code": "nroXcz",
            "email": null,
            "value": "50.00",
            "active_date": "2023-12-31T17:00:00.000000Z",
            "expiry": "2024-01-27T17:00:00.000000Z",
            "date_used": null,
            "status": 0,
            "created_at": "2024-01-20T04:00:16.000000Z",
            "updated_at": "2024-01-20T04:00:58.000000Z",
            "discount_type": "percent",
            "discount_unit": "%"
        },
        {
            "id": 14,
            "discount_type_id": 2,
            "user_id": 9,
            "code": "xecWsB",
            "email": "nguyenphuc071199@gmail.com",
            "value": "69.00",
            "active_date": "2023-12-31T17:00:00.000000Z",
            "expiry": "2024-01-27T17:00:00.000000Z",
            "date_used": "2024-01-20T04:05:41.000000Z",
            "status": 0,
            "created_at": "2024-01-20T04:04:28.000000Z",
            "updated_at": "2024-01-20T04:05:41.000000Z",
            "discount_type": "percent",
            "discount_unit": "%"
        },
        {
            "id": 15,
            "discount_type_id": 2,
            "user_id": 9,
            "code": "vW3zHu",
            "email": "nguyenphuc071199@gmail.com",
            "value": "50.00",
            "active_date": "2023-12-31T17:00:00.000000Z",
            "expiry": "2024-01-30T17:00:00.000000Z",
            "date_used": "2024-01-20T04:11:13.000000Z",
            "status": 0,
            "created_at": "2024-01-20T04:09:10.000000Z",
            "updated_at": "2024-01-20T04:11:13.000000Z",
            "discount_type": "percent",
            "discount_unit": "%"
        }
    ],
    "first_page_url": "https:\/\/payment.nswteam.net\/api\/v1\/admin\/discount\/get?page=1",
    "from": 1,
    "last_page": 3,
    "last_page_url": "https:\/\/payment.nswteam.net\/api\/v1\/admin\/discount\/get?page=3",
    "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "https:\/\/payment.nswteam.net\/api\/v1\/admin\/discount\/get?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": "https:\/\/payment.nswteam.net\/api\/v1\/admin\/discount\/get?page=2",
            "label": "2",
            "active": false
        },
        {
            "url": "https:\/\/payment.nswteam.net\/api\/v1\/admin\/discount\/get?page=3",
            "label": "3",
            "active": false
        },
        {
            "url": "https:\/\/payment.nswteam.net\/api\/v1\/admin\/discount\/get?page=2",
            "label": "Next &raquo;",
            "active": false
        }
    ],
    "next_page_url": "https:\/\/payment.nswteam.net\/api\/v1\/admin\/discount\/get?page=2",
    "path": "https:\/\/payment.nswteam.net\/api\/v1\/admin\/discount\/get",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 32,
    "status": true
}
```

### *Note: The API that retrieves the data must ensure that the data returned is in the correct structure. Additionally, the sorting and filtering functions have been reconfigured. Information on handling APIs will be updated soon!.*

## [Demo JDataTable](https://jsfiddle.net/joseph_le/eLx17fk9/880/)

## License

[MIT](https://choosealicense.com/licenses/mit/)

## Authors
#### Joseph Le